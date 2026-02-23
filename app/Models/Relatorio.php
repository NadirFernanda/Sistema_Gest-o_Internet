<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relatorio extends Model
{
    use HasFactory;

    protected $fillable = ['period', 'filename', 'generated_at', 'counts', 'note', 'status'];

    protected $casts = [
        'generated_at' => 'datetime',
        'counts' => 'array',
    ];
}
