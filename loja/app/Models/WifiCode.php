<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WifiCode extends Model
{
    protected $table = 'wifi_codes';

    protected $fillable = [
        'code',
        'plan_id',
        'status',
        'autovenda_order_id',
        'reseller_purchase_id',
        'reseller_distributed_at',
        'reseller_customer_ref',
        'used_at',
    ];

    protected $casts = [
        'reseller_distributed_at' => 'datetime',
        'used_at'                 => 'datetime',
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_USED      = 'used';
    public const STATUS_RESERVED  = 'reserved';

    public function order()
    {
        return $this->belongsTo(AutovendaOrder::class, 'autovenda_order_id');
    }

    public function resellerPurchase()
    {
        return $this->belongsTo(ResellerPurchase::class, 'reseller_purchase_id');
    }
}
