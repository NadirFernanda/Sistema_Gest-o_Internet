<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * FamilyPlanPaymentController — Gestão do fluxo de pagamento dos planos familiares/empresariais.
 *
 * Fluxo:
 *   1. GET  /pagar-plano/{id}          → mostra instruções de pagamento (ou sucesso se já activado)
 *   2. POST /payment/familia/webhook   → callback do gateway (CSRF exempt) → activa no SG
 *   3. GET  /payment/familia/simular/{id} → simulação para testes (sem gateway real)
 */
class FamilyPlanPaymentController extends Controller
{
    /**
     * Mostra a página de pagamento.
     * Reutilizada como página de sucesso quando o pagamento já foi processado.
     */
    public function show($id)
    {
        $familyRequest = FamilyPlanRequest::findOrFail($id);
        return view('pages.pagar-plano', compact('familyRequest'));
    }

    /**
     * Webhook do gateway de pagamento (CSRF exempt — ver bootstrap/app.php).
     * Valida a assinatura HMAC se PAYMENT_WEBHOOK_SECRET estiver configurado,
     * evitando que qualquer pessoa forge um webhook e active planos sem pagar.
     */
    public function webhook(Request $request)
    {
        // ── Verificação da assinatura HMAC ─────────────────────────────────
        // Se PAYMENT_WEBHOOK_SECRET estiver definido no .env, exige que a chamada
        // inclua o header correcto; caso contrário rejeita com 401.
        $webhookSecret = env('PAYMENT_WEBHOOK_SECRET');
        if (!$webhookSecret && app()->isProduction()) {
            Log::error('Payment webhook: secret ausente em produção', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'webhook_unconfigured'], 503);
        }
        if ($webhookSecret) {
            $signature = $request->header('X-Webhook-Signature')
                      ?? $request->header('X-Gateway-Signature')
                      ?? '';
            $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (! hash_equals($expected, (string) $signature)) {
                Log::warning('Payment webhook: assinatura HMAC inválida', [
                    'ip' => $request->ip(),
                ]);
                return response()->json(['error' => 'invalid_signature'], 401);
            }
        }

        $reference = $request->input('reference') ?? $request->input('referencia');
        if (!$reference) {
            return response()->json(['error' => 'missing_reference'], 400);
        }

        $req = FamilyPlanRequest::where('payment_reference', $reference)
            ->where('status', FamilyPlanRequest::STATUS_AWAITING_PAYMENT)
            ->first();

        if (!$req) {
            return response()->json(['error' => 'not_found_or_already_processed'], 404);
        }

        $activated = $this->activate($req);
        return response()->json(['success' => $activated, 'status' => $req->status]);
    }

    /**
     * Simula a confirmação de pagamento (para testes sem gateway real).
     * BLOQUEADO em produção: apenas disponível em ambientes local/staging.
     */
    public function simulateSuccess($id)
    {
        // Impede activação gratuita de planos em produção.
        if (app()->isProduction()) {
            abort(404);
        }

        $req = FamilyPlanRequest::findOrFail($id);

        if ($req->status !== FamilyPlanRequest::STATUS_AWAITING_PAYMENT) {
            return redirect()->route('family.payment.show', $id)
                ->with('info', 'Pedido já processado (estado: ' . $req->status . ').');
        }

        $this->activate($req);
        return redirect()->route('family.payment.show', $id);
    }

    /**
     * Activa o plano: sincroniza a janela no SG, actualiza o status e notifica o cliente.
     * Se o SG falhar, muda para STATUS_PENDING para intervenção manual pelo admin.
     */
    private function activate(FamilyPlanRequest $req): bool
    {
        try {
            $proxy  = app(StoreProxyController::class);
            $result = $proxy->syncJanela([
                'nome'            => $req->customer_name,
                'email'           => $req->customer_email,
                'contato'         => $req->customer_phone,
                'nif'             => $req->customer_nif,
                'template_id'     => $req->plan_id,
                'loja_request_id' => $req->id,
            ]);

            if ($result['success']) {
                $sgData = $result['data'] ?? [];
                $notes  = 'SG OK | cliente_id='        . ($sgData['cliente_id']        ?? '?')
                        . ' | plano_id='               . ($sgData['plano_id']          ?? '?')
                        . ' | proxima_renovacao='      . ($sgData['proxima_renovacao'] ?? '?')
                        . ' | action='                 . ($sgData['action']            ?? '?');

                $req->update([
                    'status' => FamilyPlanRequest::STATUS_ACTIVATED,
                    'notes'  => $notes,
                ]);

                FamilyPlanRequestController::notifyActivated($req);
                return true;

            } else {
                // SG respondeu com erro — admin precisa activar manualmente
                $req->update([
                    'status' => FamilyPlanRequest::STATUS_PENDING,
                    'notes'  => 'Pagamento confirmado. SG retornou erro: ' . ($result['error'] ?? 'unknown'),
                ]);
                Log::error('FamilyPlanPayment: SG retornou erro após pagamento confirmado', [
                    'request_id' => $req->id,
                    'error'      => $result['error'] ?? 'unknown',
                ]);
                return false;
            }

        } catch (\Throwable $e) {
            // SG inacessível — admin precisa activar manualmente
            $req->update([
                'status' => FamilyPlanRequest::STATUS_PENDING,
                'notes'  => 'Pagamento confirmado. SG inacessível: ' . $e->getMessage(),
            ]);
            Log::error('FamilyPlanPayment: excepção ao activar pedido', [
                'request_id' => $req->id,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }
}
