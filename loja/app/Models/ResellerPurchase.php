<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerPurchase extends Model
{
    protected $fillable = [
        'reseller_application_id',
        'voucher_plan_id',
        'plan_slug',
        'plan_name',
        'quantity',
        'unit_price_aoa',
        'profit_aoa',
        'status',
        'gross_amount_aoa',
        'discount_percent',
        'net_amount_aoa',
        'codes_count',
        'csv_path',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function application()
    {
        return $this->belongsTo(ResellerApplication::class, 'reseller_application_id');
    }
}
