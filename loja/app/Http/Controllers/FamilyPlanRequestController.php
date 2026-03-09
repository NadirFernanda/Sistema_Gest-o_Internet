<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $phone = preg_replace('/[\s\-\.()]/', '', $request->query('phone', ''));

        if (mb_strlen($phone) < 7) {
            return response()->json(['found' => false]);
        }

        // ── 1. Pesquisa local (pedidos anteriores na loja) ────────────────
        $record = FamilyPlanRequest::where('customer_phone', 'like', '%' . $phone . '%')
            ->whereIn('status', [
                FamilyPlanRequest::STATUS_ACTIVATED,
                FamilyPlanRequest::STATUS_PENDING,
                FamilyPlanRequest::STATUS_AWAITING_PAYMENT,
            ])
            ->orderByDesc('created_at')
            ->first(['customer_name', 'customer_email', 'customer_nif']);

        if ($record) {
            return response()->json([
                'found'  => true,
                'name'   => $record->customer_name,
                'email'  => $record->customer_email ?? '',
                'nif'    => $record->customer_nif ?? '',
                'source' => 'loja',
            ]);
        }

        // ── 2. Fallback: pesquisa no SG (clientes pré-existentes antes da loja) ──
        $sgResult = app(StoreProxyController::class)->lookupClienteSG($phone);

        if (! empty($sgResult['found'])) {
            return response()->json([
                'found'  => true,
                'name'   => $sgResult['name']  ?? '',
                'email'  => $sgResult['email'] ?? '',
                'nif'    => $sgResult['nif']   ?? '',
                'source' => 'sg',
            ]);
        }

        return response()->json(['found' => false]);
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
            'plan_preco'     => 'nullable|integer|min:0',
            'plan_ciclo'     => 'nullable|integer|min:1',
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_nif'   => 'nullable|string|max:50',
            'payment_method' => 'required|in:' . FamilyPlanRequest::METHOD_MULTICAIXA . ',' . FamilyPlanRequest::METHOD_PAYPAL,
        ]);

        $requestRecord = FamilyPlanRequest::create([
            'plan_id'         => $validated['plan_id'],
            'plan_name'       => $validated['plan_name'],
            'plan_preco'      => $validated['plan_preco'] ?? null,
            'plan_ciclo_dias' => $validated['plan_ciclo'] ?? null,
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'] ?? null,
            'customer_phone'  => $validated['customer_phone'],
            'customer_nif'    => $validated['customer_nif'] ?? null,
            'payment_method'  => $validated['payment_method'],
            'status'          => FamilyPlanRequest::STATUS_AWAITING_PAYMENT,
        ]);

        // Generate and persist the payment reference (requires the DB-assigned ID)
        $requestRecord->payment_reference = 'AW-' . str_pad($requestRecord->id, 6, '0', STR_PAD_LEFT);
        $requestRecord->save();

        // Notifica o admin do novo pedido pendente de pagamento
        $this->notifyAdmin($requestRecord);

        // Redireciona para a página de pagamento
        return redirect()->route('family.payment.show', $requestRecord->id);
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

        $paymentLabel = $req->payment_method === FamilyPlanRequest::METHOD_MULTICAIXA
            ? 'Multicaixa Express' : 'PayPal';

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
