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
 *   - O admin recebe notificação e activa o plano no SG
 *   - Os planos são carregados do SG via /sg/plan-templates
 * ════════════════════════════════════════════════════════════════════════════
 *
 * Fluxo:
 *   1. Cliente clica "Comprar" num card familiar/empresarial → GET /solicitar-plano
 *   2. Vê o formulário com os dados do plano (via query string) + campos de identificação
 *   3. Submete → POST /solicitar-plano
 *   4. Registo criado em family_plan_requests (status: pending)
 *   5. Email de notificação enviado ao admin + confirmação ao cliente
 *   6. Cliente vê página de confirmação
 *   7. Admin contacta cliente e activa o plano no SG
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

        // Envia email de notificação ao admin
        $this->notifyAdmin($requestRecord);

        // Envia confirmação ao cliente
        $this->confirmToClient($requestRecord);

        return view('pages.solicitar-plano-confirmacao', [
            'request' => $requestRecord,
        ]);
    }

    // ── Notificações ─────────────────────────────────────────────────────────

    protected function notifyAdmin(FamilyPlanRequest $req): void
    {
        $adminEmail = config('mail.admin_address', env('ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')));
        if (!$adminEmail) return;

        $paymentLabel = $req->payment_method === FamilyPlanRequest::METHOD_MULTICAIXA
            ? 'Multicaixa Express' : 'PayPal';

        $body = "Novo pedido de plano familiar/empresarial — #{$req->id}\n\n"
            . "Plano: {$req->plan_name} (ID: {$req->plan_id})\n"
            . ($req->plan_preco ? "Preço: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
            . ($req->plan_ciclo_dias ? "Duração: {$req->plan_ciclo_dias} dias\n" : '')
            . "\nDados do cliente:\n"
            . "  Nome: {$req->customer_name}\n"
            . "  E-mail: {$req->customer_email}\n"
            . "  Telefone: {$req->customer_phone}\n"
            . ($req->customer_nif ? "  NIF: {$req->customer_nif}\n" : '')
            . "\nMétodo de pagamento preferido: {$paymentLabel}\n"
            . "\nActive o plano no Sistema de Gestão após confirmar o pagamento.\n";

        try {
            Mail::raw($body, function ($message) use ($adminEmail, $req) {
                $message->to($adminEmail)
                    ->subject("[AngolaWiFi] Pedido #{$req->id} — {$req->plan_name}");
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar notificação ao admin para pedido #{$req->id}: " . $e->getMessage());
        }
    }

    protected function confirmToClient(FamilyPlanRequest $req): void
    {
        if (empty($req->customer_email)) return;

        $paymentLabel = $req->payment_method === FamilyPlanRequest::METHOD_MULTICAIXA
            ? 'Multicaixa Express' : 'PayPal';

        $body = "Olá {$req->customer_name},\n\n"
            . "Recebemos o seu pedido de adesão ao plano AngolaWiFi.\n\n"
            . "Plano solicitado: {$req->plan_name}\n"
            . ($req->plan_preco ? "Valor: " . number_format($req->plan_preco, 0, ',', '.') . " AOA\n" : '')
            . "Método de pagamento: {$paymentLabel}\n"
            . "Ref. do pedido: #{$req->id}\n\n"
            . "A nossa equipa irá contactá-lo(a) em breve para confirmar o pagamento e "
            . "activar o acesso.\n\n"
            . "Para qualquer dúvida, contacte-nos pelo WhatsApp ou e-mail de suporte.\n\n"
            . "Obrigado por escolher a AngolaWiFi!\n";

        try {
            Mail::raw($body, function ($message) use ($req) {
                $message->to($req->customer_email)
                    ->subject("AngolaWiFi — Confirmação do pedido #{$req->id}");
            });
        } catch (\Throwable $e) {
            Log::warning("Falha ao enviar confirmação ao cliente para pedido #{$req->id}: " . $e->getMessage());
        }
    }
}
