<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $fillable = [
        'ref', 'name', 'email', 'phone', 'subject', 'message',
        'category', 'status', 'priority', 'token', 'admin_notes', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public const STATUS_OPEN        = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED    = 'resolved';
    public const STATUS_CLOSED      = 'closed';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const CATEGORIES = [
        'geral'       => 'Geral',
        'pagamento'   => 'Pagamento / Faturação',
        'instalacao'  => 'Instalação / Equipamento',
        'revenda'     => 'Programa de Revenda',
        'tecnico'     => 'Problema Técnico',
        'outro'       => 'Outro',
    ];

    public static function statusLabel(string $status): string
    {
        return match($status) {
            self::STATUS_OPEN        => 'Aberto',
            self::STATUS_IN_PROGRESS => 'Em análise',
            self::STATUS_RESOLVED    => 'Resolvido',
            self::STATUS_CLOSED      => 'Fechado',
            default                  => $status,
        };
    }

    public static function priorityLabel(string $p): string
    {
        return match($p) {
            self::PRIORITY_LOW    => 'Baixa',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH   => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default               => $p,
        };
    }

    public static function generateRef(): string
    {
        $last = self::max('id') ?? 0;
        return 'AWT-' . str_pad($last + 1, 6, '0', STR_PAD_LEFT);
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }
}
