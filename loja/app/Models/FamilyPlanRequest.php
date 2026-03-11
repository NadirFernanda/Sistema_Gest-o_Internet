<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * FamilyPlanRequest — Pedido de adesão a um plano familiar ou empresarial.
 *
 * ════════════════════════════════════════════════════════════════════════
 * ÂMBITO: PLANOS FAMILIARES & EMPRESARIAIS (carregados do SG via API)
 *
 * Diferente de AutovendaOrder (planos individuais):
 *  - Requer identificação completa do cliente (nome, e-mail, telefone).
 *  - A activação é coordenada com o Sistema de Gestão (SG).
 *  - Não entrega código WiFi imediato — o admin activa no SG.
 * ════════════════════════════════════════════════════════════════════════
 */
class FamilyPlanRequest extends Model
{
    protected $table = 'family_plan_requests';

    protected $fillable = [
        'plan_id',
        'plan_name',
        'plan_preco',
        'plan_ciclo_dias',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_nif',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
    ];

    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_ACTIVATED = 'activated';
    public const STATUS_CANCELLED = 'cancelled';

    public const METHOD_MULTICAIXA = 'multicaixa_express';
    public const METHOD_PAYPAL     = 'paypal';
}
