<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerApplication extends Model
{
    protected $fillable = [
        'full_name',
        'document_number',
        'address',
        'email',
        'phone',
        'installation_location',
        'internet_type',
        'subject',
        'message',
        'status',
        'notified_at',
        'meta',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'meta' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /** Revendedor tem infraestrutura de internet própria no local de instalação. */
    public const INTERNET_OWN        = 'own';
    /** Revendedor depende de ligação fornecida pela AngolaWiFi. */
    public const INTERNET_ANGOLAWIFI = 'angolawifi';
}
