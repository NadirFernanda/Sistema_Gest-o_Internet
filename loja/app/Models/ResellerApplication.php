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
}
