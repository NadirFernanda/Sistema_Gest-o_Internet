<?php

namespace App\Services;

use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function confirmPaymentAndDeliver(AutovendaOrder $order, ?string $paymentReference = null): AutovendaOrder
    {
        if ($order->isPaid()) {
            return $order;
        }

        $now = Carbon::now();

        if ($paymentReference) {
            $order->payment_reference = $paymentReference;
        }

        // Envolve a atribuição do código WiFi e o guardado da ordem numa transacção
        // com bloqueio pessimista (lockForUpdate) para impedir que dois pedidos
        // simultâneos retirem o mesmo código do stock (race condition).
        DB::transaction(function () use (&$order, $now) {
            // Marca como pago
            $order->status = AutovendaOrder::STATUS_PAID;
            $order->paid_at = $now;

            // Retira um código WiFi disponível do stock da loja, para o plano correcto.
            // O stock é gerido localmente na tabela wifi_codes — não é consultado o SG.
            // Cada plano (diario, semanal, mensal) tem o seu próprio stock de códigos.
            if (empty($order->wifi_code)) {
                $wifiCode = \App\Models\WifiCode::where('status', \App\Models\WifiCode::STATUS_AVAILABLE)
                    ->where('plan_id', $order->plan_id)  // seleccionar apenas códigos do plano correcto
                    ->lockForUpdate()   // bloqueia a linha; impede outro pedido de a seleccionar
                    ->first();
                if ($wifiCode) {
                    $wifiCode->status = \App\Models\WifiCode::STATUS_USED;
                    $wifiCode->autovenda_order_id = $order->id;
                    $wifiCode->used_at = $now;
                    $wifiCode->save();
                    $order->wifi_code = $wifiCode->code;
                } else {
                    Log::error('Sem códigos WiFi disponíveis para o plano "' . $order->plan_id . '" — ordem ' . $order->id);
                    throw new \Exception('Sem códigos WiFi disponíveis para o plano "' . $order->plan_id . '". Tente mais tarde ou contacte o suporte.');
                }
            }

            $order->delivered_at = $now;

            // Marca a entrega apenas pelos canais que realmente existem para esta ordem.
            // Para planos individuais, e-mail e WhatsApp são opcionais — não são recolhidos
            // por omissão. Por isso, só marcamos como entregue se o canal tiver dados.
            $order->delivered_via_email     = !empty($order->customer_email);
            $order->delivered_via_whatsapp  = !empty($order->customer_phone);

            $order->save();
        }); // fim da transacção — lock libertado antes do envio do e-mail

        // Envia e-mail com o código apenas se o cliente tiver providenciado e-mail.
        // Mantido FORA da transacção para não segurar o lock durante uma operação de rede.
        // Para planos individuais rápidos, o e-mail não é obrigatório.
        if (!empty($order->customer_email)) {
            try {
                Mail::to($order->customer_email)->send(new AutovendaWifiCodeMail($order));
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar e-mail de código WiFi para ordem ' . $order->id . ': ' . $e->getMessage());
            }
        }

        Log::info('Ordem de autovenda ' . $order->id . ' paga e código WiFi entregue.', [
            'order_id'  => $order->id,
            'wifi_code' => $order->wifi_code,
        ]);

        return $order;
    }
}

