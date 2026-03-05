<?php

namespace App\Services;

use App\Mail\AutovendaWifiCodeMail;
use App\Models\AutovendaOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // Marca como pago
        $order->status = AutovendaOrder::STATUS_PAID;
        $order->paid_at = $now;

        // Gera (ou atribui) o código WiFi
        if (empty($order->wifi_code)) {
            $order->wifi_code = $this->generateWifiCode();
        }

        // Marca entrega (nesta fase assumimos entrega imediata via e-mail/WhatsApp)
        $order->delivered_at = $now;
        $order->delivered_via_email = true;
        $order->delivered_via_whatsapp = true;

        $order->save();

        // Envia e-mail com o código (simples protótipo) apenas se existir e-mail associado
        if (!empty($order->customer_email)) {
            try {
                Mail::to($order->customer_email)->send(new AutovendaWifiCodeMail($order));
            } catch (\Throwable $e) {
                Log::warning('Falha ao enviar e-mail de código WiFi para ordem '.$order->id.': '.$e->getMessage());
            }
        }

        // Integração real com WhatsApp ficará para fase posterior.
        Log::info('Ordem de autovenda '.$order->id.' marcada como paga e código WiFi entregue.', [
            'order_id' => $order->id,
            'wifi_code' => $order->wifi_code,
        ]);

        return $order;
    }

    protected function generateWifiCode(): string
    {
        // Protótipo simples de geração; em produção, alinhar com o sistema real de stock/códigos.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = 10;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }
}
