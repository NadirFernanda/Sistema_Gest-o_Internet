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
        'used_at',
    ];

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_USED = 'used';
    public const STATUS_RESERVED = 'reserved';

    public function order()
    {
        return $this->belongsTo(AutovendaOrder::class, 'autovenda_order_id');
    }
}
