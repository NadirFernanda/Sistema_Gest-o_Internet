<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * FamilyPlanRequestController — Checkout de planos familiares e empresariais.
 *
 * ════════════════════════════════════════════════════════════════════════════
 * ÂMBITO: PLANOS FAMILIARES & EMPRESARIAIS
 *
 * Este fluxo é completamente diferente do checkout de planos individuais:
 *
 * Planos INDIVIDUAIS (AutovendaOrder):
 *   - Sem dados pessoais
 *   - Código WiFi entregue imediatamente na tela
 *   - Sem integração com SG
 *
 * Planos FAMILIARES/EMPRESARIAIS (FamilyPlanRequest):
 *   - Requer nome, e-mail, telefone, NIF opcional
 *   - Fluxo: formulário → aguardar pagamento → gateway chama webhook → janela adicionada no SG
 *   - Sem intervenção humana (a menos que o SG esteja inacessível durante o callback)
 *   - Os planos são carregados do SG via /sg/plan-templates
 * ════════════════════════════════════════════════════════════════════════════
 *
 * Fluxo:
 *   1. Cliente clica "Comprar" num card familiar/empresarial → GET /solicitar-plano
 *   2. Vê o formulário com os dados do plano (via query string) + campos de identificação
 *   3. Submete → POST /solicitar-plano
 *   4. Registo criado (status: awaiting_payment), gerada referência de pagamento
 *   5. Redirecionado para GET /pagar-plano/{id} com instruções de pagamento
 *   6. Cliente paga via Multicaixa Express / PayPal
 *   7. Gateway chama POST /payment/familia/webhook → syncJanela() no SG → status=activated
 *   8. Cliente recebe e-mail "O seu plano foi activado!"
 *   9. Admin apenas intervém se o webhook falhou (status=pending no painel)
 */
class FamilyPlanRequestController extends Controller
{
    /**
     * Mostra o formulário de checkout para planos familiares/empresariais.
     * Os dados do plano chegam via query string (gerados pelo renderFamilyCard do JS).
     */
    public function show(Request $request)
    {
        // Dados do plano passados pelo JS via query string ao clicar "Comprar"
        $plan = [
            'id'        => $request->query('plan_id', ''),
            'name'      => $request->query('plan_name', 'Plano Familiar/Empresarial'),
            'preco'     => $request->query('plan_preco'),
            'ciclo'     => $request->query('plan_ciclo'),
        ];

        // Se não vier plan_id, o link está mal formado — redireciona para a loja
        if (empty($plan['id'])) {
            return redirect('/')->with('error', 'Plano não identificado. Selecione um plano na página inicial.');
        }

        return view('pages.solicitar-plano', compact('plan'));
    }

    /**
     * Pesquisa dados de um cliente anterior pelo número de telefone.
     * Usado pelo formulário de checkout para pré-preenchimento via JS.
     * Devolve apenas nome, e-mail e NIF — nunca histórico de pedidos.
     *
     * GET /checkout/lookup?phone=9XXXXXXXX
     */
    public function lookup(Request $request)
    {
        Log::info('lookup: chamado', ['phone' => $request->input('phone', '?'), 'ip' => $request->ip()]);
        // Always return JSON — wrapping everything ensures no HTML error leaks to the JS caller.
        try {
            // Strip non-digit characters and require a full phone number (9+ digits)
            // to prevent partial-number fishing that could leak customer PII.
            $phone = preg_replace('/\D/', '', $request->input('phone', ''));

            if (mb_strlen($phone) < 9) {
                return response()->json(['found' => false]);
            }

            // ── 1. Pesquisa local (pedidos anteriores na loja) ────────────────
            // LIKE search strips stored formatting (spaces/dashes) via SQL REPLACE
            // so "923 883 971" and "923883971" are treated as the same number.
            $phonePattern = ['%' . $phone . '%'];
            $phoneRaw     = "REPLACE(REPLACE(customer_phone, ' ', ''), '-', '') LIKE ?";

            $record = FamilyPlanRequest::whereRaw($phoneRaw, $phonePattern)
                ->whereIn('status', [
                    FamilyPlanRequest::STATUS_ACTIVATED,
                    FamilyPlanRequest::STATUS_PENDING,
                    FamilyPlanRequest::STATUS_AWAITING_PAYMENT,
                ])
                ->orderByDesc('created_at')
                ->first(['customer_name', 'customer_email', 'customer_nif']);

            // Current plan from the most recently activated local order
            $localCurrentPlanId = FamilyPlanRequest::whereRaw($phoneRaw, $phonePattern)
                ->where('status', FamilyPlanRequest::STATUS_ACTIVATED)
                ->orderByDesc('created_at')
                ->value('plan_id');

            if ($record) {
                return response()->json([
                    'found'           => true,
                    'name'            => $record->customer_name,
                    'email'           => $record->customer_email ?? '',
                    'source'          => 'loja',
                    'current_plan_id' => $localCurrentPlanId,
                ]);
            }

            // ── 2. Fallback: pesquisa no SG (clientes pré-existentes antes da loja) ──
            $sgResult = app(StoreProxyController::class)->lookupClienteSG($phone);

            if (! empty($sgResult['found'])) {
                return response()->json([
                    'found'             => true,
                    'name'              => $sgResult['name']  ?? '',
                    'email'             => $sgResult['email'] ?? '',
                    'source'            => 'sg',
                    'current_plan_id'   => $sgResult['current_plan_id']  ?? null,
                    'current_plan_name' => $sgResult['current_plan_name'] ?? null,
                ]);
            }

            return response()->json(['found' => false]);

        } catch (\Throwable $e) {
            Log::warning('FamilyPlanRequest lookup error: ' . $e->getMessage(), [
                'phone' => substr(preg_replace('/\D/', '', $request->query('phone', '')), 0, 4) . '***',
            ]);
            return response()->json(['found' => false, 'error' => true], 200);
        }
    }

    /**
     * Processa o formulário de checkout do plano familiar/empresarial.
     * APENAS regista o pedido e redireciona para a página de pagamento.
     * A activação no SG ocorre DEPOIS do pagamento, via webhook do gateway.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id'        => 'required|string|max:100',
            'plan_name'      => 'required|string|max:255',
            'plan_preco'     => 'nullable|numeric|min:0',
            'plan_ciclo'     => 'nullable|integer|min:1',
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_nif'   => 'nullable|string|max:50',
            'payment_method' => 'required|in:' . FamilyPlanRequest::METHOD_GPO,
        ]);

        // ── Verifica o plano junto ao SG para evitar adulteração de preço ────────
        // Os campos plan_name e plan_preco vêm de campos ocultos no formulário e
        // podem ser manipulados pelo cliente. Buscamos os valores reais do SG e
        // rejeitamos se o plano não for encontrado (SG acessível) ou sinalizamos
        // para revisão manual (SG inacessível).
        $sgVerifiedName   = $validated['plan_name'];
        $sgVerifiedPreco  = $validated['plan_preco'] ?? null;
        $sgVerifiedCiclo  = $validated['plan_ciclo'] ?? null;
        $priceVerified    = false;
        $sgAvailable      = false;

        try {
            $sg  = rtrim(config('services.sg.url', env('SG_URL', 'http://127.0.0.1:8000')), '/');
            $res = (new Client(['timeout' => 5]))->get($sg . '/api/plan-templates', ['http_errors' => false]);

            if ($res->getStatusCode() === 200) {
                $sgAvailable = true;
                $templates = json_decode((string) $res->getBody(), true)['data'] ?? [];
                $template  = collect($templates)->firstWhere('id', $validated['plan_id']);

                if ($template) {
                    // Usa os valores autoritativos do SG, ignorando os do cliente
                    $sgVerifiedName   = $template['nome']  ?? $sgVerifiedName;
                    $sgVerifiedPreco  = isset($template['preco'])  ? (int) $template['preco']  : $sgVerifiedPreco;
                    $sgVerifiedCiclo  = isset($template['ciclo'])  ? (int) $template['ciclo']  : $sgVerifiedCiclo;
                    $priceVerified    = true;
                } else {
                    // SG acessível mas plano não encontrado → possível adulteração
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['plan_id' => 'O plano seleccionado já não está disponível. Por favor seleccione outro plano.']);
                }
            }
        } catch (\Throwable $e) {
            // SG inacessível — bloquear para evitar adulteração de preço
            Log::warning('FamilyPlanRequest: não foi possível verificar o template do plano (SG inacessível)', [
                'plan_id' => $validated['plan_id'],
            ]);
        }

        if (!$sgAvailable || !$priceVerified) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['plan_id' => 'O Sistema de Gestão está indisponível. Tente novamente dentro de alguns minutos.']);
        }

        // ── Verifica se o cliente já existe (loja local ou SG) ──────────────────
        // Planos familiares/empresariais são exclusivos para clientes já instalados.
        // Novos clientes devem contactar para agendar a instalação física primeiro.
        $phone = preg_replace('/\D/', '', $validated['customer_phone']);
        $customerExists = FamilyPlanRequest::whereRaw(
                "REPLACE(REPLACE(customer_phone, ' ', ''), '-', '') LIKE ?",
                ['%' . $phone . '%']
            )
            ->whereIn('status', [
                FamilyPlanRequest::STATUS_ACTIVATED,
                FamilyPlanRequest::STATUS_PENDING,
                FamilyPlanRequest::STATUS_AWAITING_PAYMENT,
            ])
            ->exists();

        if (! $customerExists) {
            try {
                $sgResult = app(StoreProxyController::class)->lookupClienteSG($phone);
                if (! empty($sgResult['found'])) {
                    $customerExists = true;
                }
            } catch (\Throwable $e) {
                Log::warning('FamilyPlanRequest: não foi possível verificar existência do cliente no SG', [
                    'phone' => substr($phone, 0, 4) . '***',
                    'error' => $e->getMessage(),
                ]);
                // SG inacessível — bloqueia para evitar adesões de não-clientes
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['customer_phone' => 'Não foi possível verificar o seu registo de momento. Tente novamente mais tarde.']);
            }
        }

        if (! $customerExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_phone' => 'O seu número não está registado no sistema. Os planos familiares e empresariais são exclusivos para clientes já instalados. Contacte-nos para agendar a sua instalação.']);
        }

        // ── Verifica se o plano coincide com o plano actual do cliente ──────────
        // Impede fraude: cliente com plano de 10MBPS a pagar plano de 6MBPS.
        $currentPlanId = null;

        // 1. Verificar registos locais (mais recente activado)
        $localActivatedPlanId = FamilyPlanRequest::whereRaw(
                "REPLACE(REPLACE(customer_phone, ' ', ''), '-', '') LIKE ?",
                ['%' . $phone . '%']
            )
            ->where('status', FamilyPlanRequest::STATUS_ACTIVATED)
            ->orderByDesc('created_at')
            ->value('plan_id');

        if ($localActivatedPlanId) {
            $currentPlanId = (string) $localActivatedPlanId;
        } elseif ($sgAvailable) {
            // 2. Verificar no SG (cliente pré-existente sem histórico na loja)
            try {
                $sgLookup = app(StoreProxyController::class)->lookupClienteSG($phone);
                if (! empty($sgLookup['current_plan_id'])) {
                    $currentPlanId = (string) $sgLookup['current_plan_id'];
                }
            } catch (\Throwable $e) {
                Log::warning('FamilyPlanRequest store: não foi possível verificar plano actual do cliente', [
                    'phone' => substr($phone, 0, 4) . '***',
                ]);
            }
        }

        if ($currentPlanId !== null && $currentPlanId !== (string) $validated['plan_id']) {
            Log::warning('FamilyPlanRequest: tentativa de pagamento com plano incompatível', [
                'phone'           => substr($phone, 0, 4) . '***',
                'plan_solicitado' => $validated['plan_id'],
                'plan_atual'      => $currentPlanId,
                'ip'              => $request->ip(),
            ]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['plan_id' => 'O plano seleccionado não corresponde ao seu plano actual. Contacte o suporte se pretender alterar o seu plano.']);
        }

        $requestRecord = FamilyPlanRequest::create([
            'plan_id'         => $validated['plan_id'],
            'plan_name'       => $sgVerifiedName,
            'plan_preco'      => $sgVerifiedPreco,
            'plan_ciclo_dias' => $sgVerifiedCiclo,
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'] ?? null,
            'customer_phone'  => $validated['customer_phone'],
            'customer_nif'    => $validated['customer_nif'] ?? null,
            'payment_method'  => $validated['payment_method'],
            'status'          => FamilyPlanRequest::STATUS_AWAITING_PAYMENT,
            'notes'           => null,
        ]);

        $requestRecord->payment_reference = 'AW-' . str_pad($requestRecord->id, 6, '0', STR_PAD_LEFT);
        $requestRecord->save();

        $this->notifyAdmin($requestRecord);

        // GPO: redireciona para a página de pagamento online (iframe EMIS)
        if ($validated['payment_method'] === FamilyPlanRequest::METHOD_GPO) {
            return redirect()->route('family.payment.gpo', $requestRecord->id);
        }

        return redirect()->to(URL::signedRoute('family.payment.show', ['id' => $requestRecord->id]));
    }

    // ── Notificações ─────────────────────────────────────────────────────────

    public static function notifyActivated(FamilyPlanRequest $req): void
    {
        // E-mail ao cliente confirmar activação
        if (empty($req->customer_email)) return;

        $body = "Olá {$req->customer_name},\n\n"
            . "O seu plano AngolaWiFi foi activado! ✅\n\n"
            . "Plano: {$req->plan_name}\n"
            . ($req->plan_preco ? "Valor: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
            . "Ref. do pedido: {$req->payment_reference}\n\n"
            . "O seu acesso está disponível. A janela foi adicionada no sistema.\n\n"
            . "Obrigado por escolher a AngolaWiFi!\n";

        try {
            Mail::raw($body, function ($message) use ($req) {
                $message->to($req->customer_email)
                    ->subject("AngolaWiFi — Plano activado! {$req->payment_reference}");
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar e-mail de activação ao cliente #{$req->id}: " . $e->getMessage());
        }
    }

    protected function notifyAdmin(FamilyPlanRequest $req): void
    {
        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')));
        if (!$adminEmail) return;

        $paymentLabel = 'GPO / EMIS';

        $body = "Novo pedido aguardando pagamento — {$req->payment_reference}\n\n"
            . "Plano: {$req->plan_name}\n"
            . ($req->plan_preco ? "Valor: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
            . "Método: {$paymentLabel}\n\n"
            . "Cliente:\n"
            . "  {$req->customer_name} / {$req->customer_phone} / {$req->customer_email}\n"
            . ($req->customer_nif ? "  NIF: {$req->customer_nif}\n" : '')
            . "\nO plano será activado AUTOMATICAMENTE quando o pagamento for confirmado pelo gateway.\n"
            . "Só intervenha se o cliente reportar um problema.\n";

        try {
            Mail::raw($body, function ($message) use ($adminEmail, $req) {
                $message->to($adminEmail)
                    ->subject("[AngolaWiFi] Novo pedido {$req->payment_reference} — aguardando pagamento");
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar notificação ao admin para pedido #{$req->id}: " . $e->getMessage());
        }
    }
}
