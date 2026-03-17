<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResellerPurchaseAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ResellerPurchase::with('application')
            ->orderByDesc('id');

        if ($resellerId = $request->get('reseller_id')) {
            $query->where('reseller_application_id', $resellerId);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->whereHas('application', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $purchases = $query->paginate(25)->withQueryString();

        $totalRevenue   = ResellerPurchase::sum('net_amount_aoa');
        $totalCodes     = ResellerPurchase::sum('codes_count');
        $totalResellers = ResellerPurchase::distinct('reseller_application_id')->count('reseller_application_id');

        $ranking = ResellerPurchase::with('application')
            ->select(
                'reseller_application_id',
                DB::raw('SUM(net_amount_aoa)   as total_net'),
                DB::raw('SUM(gross_amount_aoa) as total_gross'),
                DB::raw('SUM(codes_count)      as total_codes'),
                DB::raw('COUNT(*)              as purchases_count')
            )
            ->groupBy('reseller_application_id')
            ->orderByDesc('total_net')
            ->get();

        return view('admin.resellers.purchases', compact(
            'purchases', 'totalRevenue', 'totalCodes', 'totalResellers', 'ranking'
        ));
    }
}
