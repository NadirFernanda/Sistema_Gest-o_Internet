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
     * Já foi enviado no ciclo actual (desde $since).
     * Garante que cada estágio de vencimento dispara uma vez por ciclo de renovação,
     * não uma vez na vida do plano — clientes recorrentes voltam a receber os alertas.
     */
    public static function jaEnviadoAlgumaVez(int $planId, string $type, ?\Carbon\Carbon $since = null): bool
    {
        $query = static::where('plan_id', $planId)->where('type', $type);
        if ($since) {
            $query->where('sent_date', '>=', $since->toDateString());
        }
        return $query->exists();
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
