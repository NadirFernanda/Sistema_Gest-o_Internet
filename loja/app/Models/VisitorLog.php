<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $table      = 'visitor_logs';
    public    $timestamps = false;
    public    $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['date', 'hour', 'sessions', 'hits'];

    protected $casts = [
        'date'     => 'date',
        'hour'     => 'integer',
        'sessions' => 'integer',
        'hits'     => 'integer',
    ];
}
