<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Anula todas as compras de revendedor com valor inferior a 10.000 Kz
     * e liberta os vouchers associados de volta ao stock disponível.
     */
    public function up(): void
    {
        $minPurchase = 10000;

        // IDs das compras abaixo do mínimo que ainda não estejam já canceladas
        $purchaseIds = DB::table('reseller_purchases')
            ->where('gross_amount_aoa', '<', $minPurchase)
            ->where('status', '!=', 'cancelled')
            ->pluck('id');

        if ($purchaseIds->isEmpty()) {
            return;
        }

        // Libertar os WifiCodes associados → voltam a ficar disponíveis
        DB::table('wifi_codes')
            ->whereIn('reseller_purchase_id', $purchaseIds)
            ->update([
                'status'                  => 'available',
                'reseller_purchase_id'    => null,
                'reseller_distributed_at' => null,
                'updated_at'              => now(),
            ]);

        // Marcar as compras como canceladas
        DB::table('reseller_purchases')
            ->whereIn('id', $purchaseIds)
            ->update([
                'status'     => 'cancelled',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Irreversível por design — não sabemos quais eram os estados anteriores.
    }
};
