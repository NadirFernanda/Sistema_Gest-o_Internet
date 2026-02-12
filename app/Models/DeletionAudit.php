<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionAudit extends Model
{
    use HasFactory;

    protected $table = 'deletion_audits';

    protected $fillable = [
        'entity_type', 'entity_id', 'user_id', 'reason', 'payload'
    ];

    protected $casts = [
        'payload' => 'array'
    ];
}
