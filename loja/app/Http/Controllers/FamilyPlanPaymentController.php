<?php

namespace App\Http\Controllers;

use App\Models\FamilyPlanRequest;
use App\Services\GpoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

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
        $webhookSecret = config('services.payment.webhook_secret');
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

        $statusRaw = strtolower((string) $request->input('status', ''));
        $amountRaw = $request->input('amount') ?? $request->input('valor');
        $allowedStatuses = ['paid', 'success', 'completed', 'confirmed'];
        $requireFields   = app()->isProduction() || (bool) $webhookSecret;

        if ($requireFields && !$statusRaw) {
            return response()->json(['error' => 'missing_status'], 400);
        }

        if ($statusRaw && !in_array($statusRaw, $allowedStatuses, true)) {
            return response()->json(['error' => 'invalid_status'], 400);
        }

        if ($req->plan_preco !== null) {
            if ($requireFields && $amountRaw === null) {
                return response()->json(['error' => 'missing_amount'], 400);
            }

            if ($amountRaw !== null) {
                $amountValue = is_numeric($amountRaw)
                    ? (int) round((float) $amountRaw)
                    : (int) preg_replace('/\D/', '', (string) $amountRaw);

                if ((int) $amountValue !== (int) $req->plan_preco) {
                    return response()->json(['error' => 'amount_mismatch'], 400);
                }
            }
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
        // Impede activação gratuita de planos fora de local/testing.
        if (!app()->environment('local', 'testing')) {
            abort(404);
        }

        $req = FamilyPlanRequest::findOrFail($id);

        if ($req->status !== FamilyPlanRequest::STATUS_AWAITING_PAYMENT) {
            return redirect()->to(URL::signedRoute('family.payment.show', ['id' => $id]))
                ->with('info', 'Pedido já processado (estado: ' . $req->status . ').');
        }

        $this->activate($req);
        return redirect()->to(URL::signedRoute('family.payment.show', ['id' => $id]));
    }

    // ── GPO ───────────────────────────────────────────────────────────────────

    /**
     * Mostra a página de pagamento GPO (iframe EMIS) para planos familiares/empresariais.
     */
    public function showGpo(int $id, GpoService $gpo)
    {
        $familyRequest = FamilyPlanRequest::findOrFail($id);

        if ($familyRequest->payment_method !== FamilyPlanRequest::METHOD_GPO) {
            return redirect()->to(URL::signedRoute('family.payment.show', ['id' => $id]));
        }

        if ($familyRequest->status === FamilyPlanRequest::STATUS_ACTIVATED) {
            return view('pages.pagar-plano', compact('familyRequest'));
        }

        // Gerar ou reutilizar referência GPO (FAM{id}{4random}, max 15 chars)
        $reference = $familyRequest->gpo_reference;
        if (! $reference) {
            $reference = 'FAM' . $familyRequest->id . strtoupper(Str::random(4));
            $reference = substr(preg_replace('/[^a-zA-Z0-9]/', '', $reference), 0, 15);
            $familyRequest->update(['gpo_reference' => $reference]);
        }

        $callbackUrl = route('webhooks.gpo.family');

        try {
            $iframeUrl = $gpo->createPurchaseToken((float) $familyRequest->plan_preco, $reference, $callbackUrl);
        } catch (\Throwable $e) {
            Log::error('GPO Família: erro ao iniciar pagamento', [
                'request_id' => $familyRequest->id,
                'error'      => $e->getMessage(),
            ]);
            return back()->withErrors(['gateway' => 'Não foi possível iniciar o pagamento. Tente novamente ou escolha outro método.']);
        }

        return view('pages.pagar-plano-gpo', compact('familyRequest', 'iframeUrl', 'reference'));
    }

    /**
     * Polling JSON do estado do pagamento GPO (para o JS da view).
     */
    public function gpoStatus(int $id)
    {
        $familyRequest = FamilyPlanRequest::findOrFail($id);
        $familyRequest->refresh();

        $isPaid   = $familyRequest->status === FamilyPlanRequest::STATUS_ACTIVATED;
        $isFailed = $familyRequest->status === FamilyPlanRequest::STATUS_CANCELLED;

        return response()->json([
            'is_paid'      => $isPaid,
            'status'       => $isPaid ? 'paid' : ($isFailed ? 'failed' : 'pending'),
            'redirect_url' => $isPaid ? route('family.payment.gpo.confirm', $id) : null,
            'cancel_url'   => $isFailed ? route('family.payment.gpo.cancel', $id) : null,
        ]);
    }

    /**
     * Confirmação após polling bem-sucedido — mostra página de sucesso.
     */
    public function gpoConfirm(int $id)
    {
        $familyRequest = FamilyPlanRequest::findOrFail($id);
        return view('pages.pagar-plano', compact('familyRequest'));
    }

    /**
     * Cancelar pagamento GPO — volta ao formulário para escolher outro método.
     */
    public function gpoCancel(int $id)
    {
        $familyRequest = FamilyPlanRequest::findOrFail($id);
        $familyRequest->update(['status' => FamilyPlanRequest::STATUS_CANCELLED]);

        return redirect()->route('family.request.show', [
            'plan_id'    => $familyRequest->plan_id,
            'plan_name'  => $familyRequest->plan_name,
            'plan_preco' => $familyRequest->plan_preco,
            'plan_ciclo' => $familyRequest->plan_ciclo_dias,
        ])->with('error', 'Pagamento não concluído. Pode tentar novamente.');
    }

    /**
     * Callback server-to-server do GPO para planos familiares/empresariais.
     * CSRF-exempt — ver bootstrap/app.php.
     */
    public function gpoCallback(Request $request)
    {
        $data = $request->all();

        Log::info('GPO Família: callback recebido', [
            'ip'   => $request->ip(),
            'body' => $data,
        ]);

        $gpoService = app(\App\Services\GpoService::class);
        $parsed     = $gpoService->parseCallback($data);

        $gpoRef = $parsed['merchant_ref'];
        if (! $gpoRef) {
            return response()->json(['error' => 'missing_reference'], 400);
        }

        $familyRequest = FamilyPlanRequest::where('gpo_reference', $gpoRef)->first();
        if (! $familyRequest) {
            Log::warning('GPO Família: pedido não encontrado', ['ref' => $gpoRef]);
            return response()->json(['error' => 'not_found'], 404);
        }

        // Idempotência
        if ($familyRequest->status === FamilyPlanRequest::STATUS_ACTIVATED) {
            return response()->json(['status' => 'already_processed']);
        }

        if ($parsed['successful']) {
            $familyRequest->update([
                'payment_reference' => $parsed['transaction_id'] ?? $familyRequest->payment_reference,
            ]);
            $this->activate($familyRequest);
        } elseif (in_array($parsed['status'], ['rejected', 'cancelled', 'expired', 'reversed'], true)) {
            $familyRequest->update(['status' => FamilyPlanRequest::STATUS_CANCELLED]);
        }

        return response()->json(['status' => 'processed']);
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
