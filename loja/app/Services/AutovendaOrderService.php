<?php

namespace App\Services;

use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * AutovendaOrderService — Serviço de entrega de ordens de AUTOVENDA (planos individuais).
 *
 * ÂMBITO: Este serviço gere EXCLUSIVAMENTE as ordens de planos individuais rápidos
 * (Dia, Semana, Mês). Os planos familiares e empresariais são geridos pelo Sistema de
 * Gestão (SG) e NÃO passam por aqui.
 *
 * Fluxo esperado:
 *   1. StorefrontController cria a AutovendaOrder (status: awaiting_payment).
 *   2. Cliente é redirecionado ao gateway de pagamento.
 *   3. Gateway faz callback → PaymentCallbackController chama confirmPaymentAndDeliver().
 *   4. Este serviço marca a ordem como "paid", retira um código WiFi do stock e entrega.
 *
 * Em modo protótipo, o passo 2-3 é simulado e confirmPaymentAndDeliver() é chamado
 * directamente após a criação da ordem, mas a lógica de negócio é idêntica.
 */
class AutovendaOrderService
{
    private const MAX_DELIVERY_ATTEMPTS = 5;

    public function confirmPaymentAndDeliver(AutovendaOrder $order, ?string $paymentReference = null): AutovendaOrder
    {
        if ($order->isPaid()) {
            return $order;
        }

        $orderId = $order->getKey();

        // Envolve a atribuição do código WiFi e o guardado da ordem numa transacção
        // com bloqueio pessimista da ordem e do stock para impedir entregas duplicadas
        // quando o gateway repetir callbacks simultâneos.
        $stockExausted = false;
        $alreadyPaid = false;

        DB::transaction(function () use ($orderId, $paymentReference, &$order, &$stockExausted, &$alreadyPaid) {
            $order = AutovendaOrder::whereKey($orderId)->lockForUpdate()->firstOrFail();
            if ($order->isPaid()) {
                $alreadyPaid = true;
                return;
            }

            $now = Carbon::now();

            if ($paymentReference) {
                $order->payment_reference = $paymentReference;
            }

            // Marca como pago — o pagamento foi confirmado pelo gateway
            $order->status = AutovendaOrder::STATUS_PAID;
            $order->paid_at = $now;

            // Retira um código WiFi disponível do stock da loja, para o plano correcto.
            // O stock é gerido localmente na tabela wifi_codes — não é consultado o SG.
            // Cada plano (diario, semanal, mensal) tem o seu próprio stock de códigos.
            if (empty($order->wifi_code)) {
                $wifiCode = \App\Models\WifiCode::where('status', \App\Models\WifiCode::STATUS_AVAILABLE)
                    ->where('plan_id', $order->plan_id)
                    ->whereNull('reseller_purchase_id')  // apenas stock de autovenda
                    ->whereNull('autovenda_order_id')
                    ->lockForUpdate()
                    ->first();

                if ($wifiCode) {
                    $wifiCode->status = \App\Models\WifiCode::STATUS_USED;
                    $wifiCode->autovenda_order_id = $order->id;
                    $wifiCode->used_at = $now;
                    $wifiCode->save();
                    $order->wifi_code = $wifiCode->code;
                } else {
                    // Stock esgotado — o pagamento JÁ foi feito; marcamos como pago mas
                    // sem código. O admin deve entregar manualmente ou reembolsar.
                    $stockExausted = true;
                    Log::error('STOCK ESGOTADO — autovenda sem códigos WiFi', [
                        'plan_id'  => $order->plan_id,
                        'order_id' => $order->id,
                        'amount'   => $order->amount_aoa,
                        'customer' => $order->customer_email ?? $order->customer_phone ?? 'anónimo',
                    ]);
                }
            }

            if (! $stockExausted && $order->bonus_plan_slug && empty($order->bonus_wifi_code)) {
                $bonusCode = \App\Models\WifiCode::where('status', \App\Models\WifiCode::STATUS_AVAILABLE)
                    ->where('plan_id', $order->bonus_plan_slug)
                    ->whereNull('reseller_purchase_id')
                    ->whereNull('autovenda_order_id')
                    ->lockForUpdate()
                    ->first();

                if ($bonusCode) {
                    $bonusCode->status = \App\Models\WifiCode::STATUS_USED;
                    $bonusCode->autovenda_order_id = $order->id;
                    $bonusCode->used_at = $now;
                    $bonusCode->save();
                    $order->bonus_wifi_code = $bonusCode->code;
                    $order->bonus_delivery_status = 'delivered';
                } else {
                    $order->bonus_delivery_status = 'stock_unavailable';
                    Log::critical('BÓNUS PROMOCIONAL SEM STOCK — ordem paga sem o voucher gratuito', [
                        'order_id' => $order->id,
                        'campaign_id' => $order->bonus_campaign_id,
                        'bonus_plan_slug' => $order->bonus_plan_slug,
                    ]);
                }
            }

            if (!$stockExausted) {
                $order->delivered_at           = $now;
            }

            $order->save();
        }); // fim da transacção — lock libertado antes do envio do e-mail

        if ($alreadyPaid) {
            return $order;
        }

        // Se stock esgotado: ordem fica paga sem código — admin entrega manualmente
        if ($stockExausted) {
            Log::critical('AUTOVENDA — STOCK ESGOTADO: ordem ' . $order->id . ' paga sem código. Intervenção manual urgente.', [
                'plan_id' => $order->plan_id,
                'amount'  => $order->amount_aoa,
                'contact' => $order->customer_email ?? $order->customer_phone ?? 'anónimo',
            ]);
            return $order;
        }

        // A reconciliação manual pode envolver uma transacção exterior.
        // Só iniciar chamadas de rede quando a confirmação estiver persistida.
        if (DB::transactionLevel() > 0) {
            DB::afterCommit(fn () => $this->retryDelivery($order));

            return $order;
        }

        return $this->retryDelivery($order);
    }

    /** Envia apenas por canais ainda pendentes, com reserva atómica da tentativa. */
    public function retryDelivery(AutovendaOrder $order): AutovendaOrder
    {
        $claimed = DB::transaction(function () use ($order): ?AutovendaOrder {
            $locked = AutovendaOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $needsEmail = ! empty($locked->customer_email) && ! $locked->delivered_via_email;
            $needsWhatsapp = ! empty($locked->customer_phone) && ! $locked->delivered_via_whatsapp;

            if (! $locked->isPaid() || ! $locked->wifi_code || (! $needsEmail && ! $needsWhatsapp)
                || $locked->delivery_retry_count >= self::MAX_DELIVERY_ATTEMPTS
                || $locked->delivery_next_attempt_at?->isFuture()) {
                return null;
            }

            $attempt = $locked->delivery_retry_count + 1;
            $delays = [1 => 5, 2 => 15, 3 => 30, 4 => 60, 5 => 120];
            $locked->delivery_retry_count = $attempt;
            $locked->delivery_next_attempt_at = now()->addMinutes($delays[$attempt]);
            $locked->save();

            return $locked;
        });

        if (! $claimed) {
            return $order->fresh();
        }

        $emailSent = false;
        $whatsappSent = false;

        if ($claimed->customer_email && ! $claimed->delivered_via_email) {
            try {
                Mail::to($claimed->customer_email)->send(new AutovendaWifiCodeMail($claimed));
                $emailSent = true;
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar e-mail de código WiFi', ['order_id' => $claimed->id, 'error' => $e->getMessage()]);
            }
        }

        if ($claimed->customer_phone && ! $claimed->delivered_via_whatsapp) {
            try {
                $this->enviarWhatsAppCodigo($claimed);
                $whatsappSent = true;
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar WhatsApp de código WiFi', ['order_id' => $claimed->id, 'error' => $e->getMessage()]);
            }
        }

        $updated = DB::transaction(function () use ($claimed, $emailSent, $whatsappSent): AutovendaOrder {
            $locked = AutovendaOrder::whereKey($claimed->id)->lockForUpdate()->firstOrFail();
            $locked->delivered_via_email = $locked->delivered_via_email || $emailSent;
            $locked->delivered_via_whatsapp = $locked->delivered_via_whatsapp || $whatsappSent;
            if ((! $locked->customer_email || $locked->delivered_via_email)
                && (! $locked->customer_phone || $locked->delivered_via_whatsapp)) {
                $locked->delivery_next_attempt_at = null;
            }
            $locked->save();

            return $locked;
        });

        if ($updated->delivery_retry_count >= self::MAX_DELIVERY_ATTEMPTS
            && $updated->delivery_next_attempt_at !== null) {
            Log::error('Autovenda: tentativas automáticas de envio esgotadas', ['order_id' => $updated->id]);
        }

        return $updated;
    }

    private function enviarWhatsAppCodigo(AutovendaOrder $order): void
    {
        if (! config('services.evolution.enabled')) {
            throw new \RuntimeException('Evolution API desactivada');
        }

        $url      = rtrim((string) config('services.evolution.url'), '/');
        $key      = (string) config('services.evolution.key');
        $instance = (string) config('services.evolution.instance');

        if (! $url || ! $key || ! $instance) {
            throw new \RuntimeException('Evolution API não configurada');
        }

        // Normaliza o número: remove não-dígitos; adiciona 244 se for número local de 9 dígitos
        $numero = preg_replace('/\D/', '', $order->customer_phone ?? '');
        if (strlen($numero) === 9 && str_starts_with($numero, '9')) {
            $numero = '244' . $numero;
        }

        if (empty($numero)) {
            throw new \RuntimeException('Número de WhatsApp inválido');
        }

        $nome     = $order->customer_name ? "Prezado(a) *{$order->customer_name}*,\n\n" : '';
        $plano    = $order->plan_name ?? 'Plano Individual';
        $codigo   = $order->wifi_code;
        $ref      = $order->payment_reference ?? "#{$order->id}";

        $mensagem =
            "✅ *Pagamento Confirmado — AngolaWiFi*\n\n" .
            $nome .
            "O seu pagamento foi processado com sucesso.\n\n" .
            "📋 *Detalhes:*\n" .
            "• Plano: *{$plano}*\n" .
            "• Referência: {$ref}\n\n" .
            "📶 *O seu código WiFi é:*\n\n" .
            "```{$codigo}```\n\n" .
            ($order->bonus_wifi_code
                ? "🎁 *Bónus promocional — {$order->bonus_plan_name} ({$order->bonus_plan_validity}):*\n\n```{$order->bonus_wifi_code}```\n\n"
                : ($order->bonus_delivery_status === 'stock_unavailable'
                    ? "⚠️ O bónus promocional {$order->bonus_plan_name} está a ser verificado pela equipa AngolaWiFi, que entrará em contacto para concluir a entrega.\n\n"
                    : '')) .
            "⚠️ *Guarde este código* — a AngolaWiFi não armazena dados pessoais para planos individuais.\n\n" .
            "Em caso de dúvidas: 📞 (+244) 949 364 505\n\n" .
            "*AngolaWiFi – Conectando você sempre!*";

        $response = Http::withHeaders([
            'apikey'       => $key,
            'Content-Type' => 'application/json',
        ])->timeout(15)->post("{$url}/message/sendText/{$instance}", [
            'number' => $numero,
            'text'   => $mensagem,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Evolution API retornou ' . $response->status() . ': ' . substr($response->body(), 0, 300)
            );
        }

        Log::info('WhatsApp autovenda: código WiFi enviado', ['to' => $numero, 'order' => $order->id]);
    }
}
