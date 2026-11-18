<?php

namespace App\Console\Commands;

use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class RetryAutovendaDelivery extends Command
{
    protected $signature = 'autovenda:retry-delivery';

    protected $description = 'Reenvia vouchers de encomendas pagas por canais cuja entrega falhou';

    public function handle(AutovendaOrderService $delivery): int
    {
        $processed = 0;

        AutovendaOrder::query()
            ->where('status', AutovendaOrder::STATUS_PAID)
            ->whereNotNull('wifi_code')
            ->where('delivery_retry_count', '<', 5)
            ->where(function (Builder $query) {
                $query->whereNull('delivery_next_attempt_at')
                    ->orWhere('delivery_next_attempt_at', '<=', now());
            })
            ->where(function (Builder $query) {
                $query->where(function (Builder $email) {
                    $email->whereNotNull('customer_email')
                        ->where('customer_email', '!=', '')
                        ->where('delivered_via_email', false);
                })->orWhere(function (Builder $whatsapp) {
                    $whatsapp->whereNotNull('customer_phone')
                        ->where('customer_phone', '!=', '')
                        ->where('delivered_via_whatsapp', false);
                });
            })
            ->chunkById(100, function ($orders) use ($delivery, &$processed) {
                foreach ($orders as $order) {
                    $delivery->retryDelivery($order);
                    $processed++;
                }
            });

        $this->info("Encomendas examinadas para reenvio: {$processed}");

        return self::SUCCESS;
    }
}
