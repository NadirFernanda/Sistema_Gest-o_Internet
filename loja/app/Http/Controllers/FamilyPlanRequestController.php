<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanRequest;
use App\Http\Controllers\StoreProxyController;
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
 *   - Ao submeter o formulário, o plano é activado AUTOMATICAMENTE no SG
 *   - O admin só precisa de verificar se o pagamento foi recebido (e cancelar se não foi)
 *   - Os planos são carregados do SG via /sg/plan-templates
 * ════════════════════════════════════════════════════════════════════════════
 *
 * Fluxo:
 *   1. Cliente clica "Comprar" num card familiar/empresarial → GET /solicitar-plano
 *   2. Vê o formulário com os dados do plano (via query string) + campos de identificação
 *   3. Submete → POST /solicitar-plano
 *   4. Registo criado, janela adicionada AUTOMATICAMENTE no SG via API
 *   5. Cliente recebe confirmação de activação imediata por e-mail
 *   6. Admin recebe notificação e verifica o pagamento (pode cancelar se não recebido)
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
     * Processa o formulário de checkout do plano familiar/empresarial.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id'        => 'required|string|max:100',
            'plan_name'      => 'required|string|max:255',
            'plan_preco'     => 'nullable|integer|min:0',
            'plan_ciclo'     => 'nullable|integer|min:1',
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_nif'   => 'nullable|string|max:50',
            'payment_method' => 'required|in:' . FamilyPlanRequest::METHOD_MULTICAIXA . ',' . FamilyPlanRequest::METHOD_PAYPAL,
        ]);

        // Regista o pedido
        $requestRecord = FamilyPlanRequest::create([
            'plan_id'         => $validated['plan_id'],
            'plan_name'       => $validated['plan_name'],
            'plan_preco'      => $validated['plan_preco'] ?? null,
            'plan_ciclo_dias' => $validated['plan_ciclo'] ?? null,
            'customer_name'   => $validated['customer_name'],
            'customer_email'  => $validated['customer_email'],
            'customer_phone'  => $validated['customer_phone'],
            'customer_nif'    => $validated['customer_nif'] ?? null,
            'payment_method'  => $validated['payment_method'],
            'status'          => FamilyPlanRequest::STATUS_PENDING,
        ]);

        // ── Activação automática no SG ────────────────────────────────────────
        // Tenta adicionar a janela imediatamente. Se o SG não estiver acessível,
        // o pedido fica como "pending" e o admin pode confirmar manualmente.
        $sgActivated = false;
        $sgNotes     = null;
        try {
            $proxy  = app(StoreProxyController::class);
            $result = $proxy->syncJanela([
                'nome'            => $requestRecord->customer_name,
                'email'           => $requestRecord->customer_email,
                'contato'         => $requestRecord->customer_phone,
                'nif'             => $requestRecord->customer_nif,
                'template_id'     => $requestRecord->plan_id,
                'loja_request_id' => $requestRecord->id,
            ]);

            if ($result['success']) {
                $sgData    = $result['data'] ?? [];
                $sgNotes   = 'SG: cliente_id=' . ($sgData['cliente_id'] ?? '?')
                           . ' | plano_id='   . ($sgData['plano_id']   ?? '?')
                           . ' | proxima_renovacao=' . ($sgData['proxima_renovacao'] ?? '?')
                           . ' | action=' . ($sgData['action'] ?? '?');
                $requestRecord->update([
                    'status' => FamilyPlanRequest::STATUS_ACTIVATED,
                    'notes'  => $sgNotes,
                ]);
                $sgActivated = true;
            } else {
                Log::warning('FamilyPlanRequest: falha na activação automática', [
                    'request_id' => $requestRecord->id,
                    'error'      => $result['error'],
                ]);
                $requestRecord->update(['notes' => 'SG unreachable: ' . ($result['error'] ?? 'unknown')]);
            }
        } catch (\Throwable $e) {
            Log::error('FamilyPlanRequest: excepção na activação automática', [
                'request_id' => $requestRecord->id,
                'error'      => $e->getMessage(),
            ]);
        }

        // Notifica o admin (para verificação do pagamento)
        $this->notifyAdmin($requestRecord, $sgActivated);

        // Envia confirmação ao cliente
        $this->confirmToClient($requestRecord, $sgActivated);

        return view('pages.solicitar-plano-confirmacao', [
            'familyRequest' => $requestRecord,
            'sgActivated'   => $sgActivated,
        ]);
    }

    // ── Notificações ─────────────────────────────────────────────────────────

    protected function notifyAdmin(FamilyPlanRequest $req, bool $sgActivated): void
    {
        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')));
        if (!$adminEmail) return;

        $paymentLabel = $req->payment_method === FamilyPlanRequest::METHOD_MULTICAIXA
            ? 'Multicaixa Express' : 'PayPal';

        $statusLine = $sgActivated
            ? "✅ Janela ACTIVADA automaticamente no SG."
            : "⚠️ Activação no SG FALHOU — verifique e active manualmente.";

        $body = "Pedido de plano familiar/empresarial — #{$req->id}\n\n"
            . "{$statusLine}\n\n"
            . "Plano: {$req->plan_name} (ID: {$req->plan_id})\n"
            . ($req->plan_preco ? "Preço: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
            . ($req->plan_ciclo_dias ? "Duração: {$req->plan_ciclo_dias} dias\n" : '')
            . "\nDados do cliente:\n"
            . "  Nome: {$req->customer_name}\n"
            . "  E-mail: {$req->customer_email}\n"
            . "  Telefone: {$req->customer_phone}\n"
            . ($req->customer_nif ? "  NIF: {$req->customer_nif}\n" : '')
            . "\nMétodo de pagamento: {$paymentLabel}\n"
            . "\nVerifique se o pagamento foi recebido. Se NÃO foi recebido, cancele o pedido no painel de admin.\n";

        try {
            Mail::raw($body, function ($message) use ($adminEmail, $req, $sgActivated) {
                $subject = $sgActivated
                    ? "[AngolaWiFi] ✅ Pedido #{$req->id} activado — {$req->plan_name}"
                    : "[AngolaWiFi] ⚠️ Pedido #{$req->id} FALHOU activação — {$req->plan_name}";
                $message->to($adminEmail)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar notificação ao admin para pedido #{$req->id}: " . $e->getMessage());
        }
    }

    protected function confirmToClient(FamilyPlanRequest $req, bool $sgActivated): void
    {
        if (empty($req->customer_email)) return;

        $paymentLabel = $req->payment_method === FamilyPlanRequest::METHOD_MULTICAIXA
            ? 'Multicaixa Express' : 'PayPal';

        if ($sgActivated) {
            $body = "Olá {$req->customer_name},\n\n"
                . "O seu plano AngolaWiFi foi activado com sucesso! ✅\n\n"
                . "Plano: {$req->plan_name}\n"
                . ($req->plan_preco ? "Valor: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
                . "Método de pagamento: {$paymentLabel}\n"
                . "Ref. do pedido: #{$req->id}\n\n"
                . "O seu acesso já está disponível.\n"
                . "Por favor efectue o pagamento o mais brevemente possível para evitar interrupções.\n\n"
                . "Para qualquer dúvida, contacte-nos pelo WhatsApp ou e-mail de suporte.\n\n"
                . "Obrigado por escolher a AngolaWiFi!\n";
        } else {
            $body = "Olá {$req->customer_name},\n\n"
                . "Recebemos o seu pedido de adesão ao plano AngolaWiFi.\n\n"
                . "Plano solicitado: {$req->plan_name}\n"
                . ($req->plan_preco ? "Valor: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
                . "Método de pagamento: {$paymentLabel}\n"
                . "Ref. do pedido: #{$req->id}\n\n"
                . "A nossa equipa irá contactá-lo(a) em breve para activar o acesso.\n\n"
                . "Para qualquer dúvida, contacte-nos pelo WhatsApp ou e-mail de suporte.\n\n"
                . "Obrigado por escolher a AngolaWiFi!\n";
        }

        try {
            Mail::raw($body, function ($message) use ($req, $sgActivated) {
                $subject = $sgActivated
                    ? "AngolaWiFi — O seu plano foi activado! #{$req->id}"
                    : "AngolaWiFi — Recebemos o seu pedido #{$req->id}";
                $message->to($req->customer_email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar confirmação ao cliente para pedido #{$req->id}: " . $e->getMessage());
        }
    }
}
