<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallationAppointment extends Model
{
    protected $table = 'installation_appointments';

    protected $fillable = [
        'name',
        'phone',
        'type',
        'message',
        'status',
        'admin_notes',
    ];

    public const TYPE_FAMILIA    = 'familia';
    public const TYPE_EMPRESA    = 'empresa';
    public const TYPE_INSTITUICAO = 'instituicao';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_DONE      = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_FAMILIA     => 'Família',
            self::TYPE_EMPRESA     => 'Empresa',
            self::TYPE_INSTITUICAO => 'Instituição',
            default                => $type,
        };
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING   => 'Pendente',
            self::STATUS_CONTACTED => 'Contactado',
            self::STATUS_DONE      => 'Concluído',
            self::STATUS_CANCELLED => 'Cancelado',
            default                => $status,
        };
    }
}
