<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertLog extends Model
{
    protected $fillable = ['plan_id', 'type', 'dias_restantes', 'sent_date'];

    protected $casts = ['sent_date' => 'date'];

    public static function jaEnviado(int $planId, string $type): bool
    {
        return static::where('plan_id', $planId)
            ->where('type', $type)
            ->whereDate('sent_date', today())
            ->exists();
    }

    /**
     * Já foi enviado alguma vez (não só hoje) — usado para os estágios de
     * vencimento, que devem disparar uma única vez na vida da subscrição,
     * independentemente de quantos dias o comando correr sem apanhar o cliente
     * exactamente no dia certo.
     */
    public static function jaEnviadoAlgumaVez(int $planId, string $type): bool
    {
        return static::where('plan_id', $planId)
            ->where('type', $type)
            ->exists();
    }

    public static function registar(int $planId, string $type, int $diasRestantes): void
    {
        static::create([
            'plan_id'        => $planId,
            'type'           => $type,
            'dias_restantes' => $diasRestantes,
            'sent_date'      => today(),
        ]);
    }
}
