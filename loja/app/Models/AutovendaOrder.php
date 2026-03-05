<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representa uma ordem de autovenda (compra rápida de 1 código WiFi).
 *
 * Ciclo típico de estados:
 * - pending: criada a partir do checkout, antes da escolha de pagamento (opcional).
 * - awaiting_payment: cliente escolheu o método de pagamento e foi/será redirecionado para o gateway.
 * - paid: pagamento confirmado pelo gateway / backoffice.
 * - cancelled: cancelada manualmente ou pelo cliente antes do pagamento.
 * - failed: erro irrecuperável no pagamento.
 * - expired: referência ou janela de pagamento expirada.
 */
class AutovendaOrder extends Model
{
    protected $table = 'autovenda_orders';

    protected $fillable = [
        'plan_id',
        'plan_name',
        'plan_speed',
        'plan_duration_minutes',
        'quantity',
        'amount_aoa',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_nif',
        'status',
        'payment_method',
        'payment_reference',
        'payment_gateway',
        'paid_at',
        'wifi_code',
        'delivered_at',
        'delivered_via_email',
        'delivered_via_whatsapp',
        'meta',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'delivered_at' => 'datetime',
        'delivered_via_email' => 'boolean',
        'delivered_via_whatsapp' => 'boolean',
        'meta' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const METHOD_MULTICAIXA = 'multicaixa_express';
    public const METHOD_PAYPAL = 'paypal';

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isAwaitingPayment(): bool
    {
        return $this->status === self::STATUS_AWAITING_PAYMENT;
    }
}
