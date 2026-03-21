<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResellerBonusTransaction extends Model
{
    protected $fillable = [
        'reseller_application_id',
        'amount_aoa',
        'reason',
    ];

    protected $casts = [
        'amount_aoa' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(ResellerApplication::class, 'reseller_application_id');
    }
}
