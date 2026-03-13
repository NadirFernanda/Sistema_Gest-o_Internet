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

        $purchases = $query->paginate(25)->withQueryString();

        $totalRevenue = ResellerPurchase::sum('net_amount_aoa');
        $totalCodes   = ResellerPurchase::sum('codes_count');

        return view('admin.resellers.purchases', compact('purchases', 'totalRevenue', 'totalCodes'));
    }
}
