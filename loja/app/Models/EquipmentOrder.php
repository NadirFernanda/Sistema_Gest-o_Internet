<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentOrder extends Model
{
    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'items',
        'total_aoa',
        'status',
        'order_type',
        'estimated_delivery_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'items'                   => 'array',
        'total_aoa'               => 'integer',
        'estimated_delivery_date' => 'date',
    ];

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_SHIPPED   = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_IMMEDIATE = 'immediate';
    public const TYPE_BACKORDER = 'backorder';

    public const METHOD_MULTICAIXA = 'multicaixa_express';
    public const METHOD_PAYPAL     = 'paypal';
    public const METHOD_CASH       = 'cash';

    public static function allStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];
    }
}
