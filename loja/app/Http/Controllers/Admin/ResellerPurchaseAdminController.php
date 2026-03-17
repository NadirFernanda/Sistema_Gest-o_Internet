<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerPurchase;
use Illuminate\Http\Request;

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

        $totalRevenue = ResellerPurchase::sum('net_amount_aoa');
        $totalCodes   = ResellerPurchase::sum('codes_count');

        return view('admin.resellers.purchases', compact('purchases', 'totalRevenue', 'totalCodes'));
    }
}
