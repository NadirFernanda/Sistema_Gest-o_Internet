<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteStat extends Model
{
    protected $table = 'site_stats';

    protected $fillable = [
        'ordem',
        'valor',
        'legenda',
        'count_to',
        'count_decimals',
        'count_suffix',
    ];

    protected $casts = [
        'count_to'       => 'float',
        'count_decimals' => 'integer',
        'ordem'          => 'integer',
    ];
}
