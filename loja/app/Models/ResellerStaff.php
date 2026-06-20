<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResellerStaff extends Model
{
    protected $table = 'reseller_staff';

    protected $fillable = [
        'reseller_application_id',
        'full_name',
        'phone',
        'email',
        'pin_hash',
        'status',
        'notes',
    ];

    protected $hidden = ['pin_hash'];

    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';

    const MAX_PER_RESELLER = 10;

    public function application()
    {
        return $this->belongsTo(ResellerApplication::class, 'reseller_application_id');
    }

    public function soldCodes()
    {
        return $this->hasMany(WifiCode::class, 'reseller_staff_id')
                    ->whereNotNull('reseller_distributed_at');
    }

    public function checkPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function totalSoldCount(): int
    {
        return WifiCode::where('reseller_staff_id', $this->id)
            ->whereNotNull('reseller_distributed_at')
            ->count();
    }

    public function totalSalesAoa(): int
    {
        return (int) DB::table('wifi_codes')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->where('wifi_codes.reseller_staff_id', $this->id)
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->sum('voucher_plans.price_public_aoa');
    }

    public function monthlySoldCount(): int
    {
        return WifiCode::where('reseller_staff_id', $this->id)
            ->whereNotNull('reseller_distributed_at')
            ->whereYear('reseller_distributed_at', now()->year)
            ->whereMonth('reseller_distributed_at', now()->month)
            ->count();
    }
}
