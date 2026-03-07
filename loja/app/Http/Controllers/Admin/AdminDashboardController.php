<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Models\EquipmentOrder;
use App\Models\Product;
use App\Models\ResellerApplication;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalOrders = AutovendaOrder::count();
        $paidOrders = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->count();
        $awaitingPayment = AutovendaOrder::where('status', AutovendaOrder::STATUS_AWAITING_PAYMENT)->count();
        $totalRevenueAoa = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)->sum('amount_aoa');

        $recentOrders = AutovendaOrder::orderByDesc('id')->limit(8)->get();

        $pendingResellers = ResellerApplication::where('status', ResellerApplication::STATUS_PENDING)->count();
        $totalResellers = ResellerApplication::count();

        // Equipment stats
        $totalProducts      = Product::count();
        $totalEquipOrders   = EquipmentOrder::count();
        $newEquipOrders     = EquipmentOrder::where('status', EquipmentOrder::STATUS_PENDING)->count();
        $totalEquipRevenue  = EquipmentOrder::whereIn('status', [
            EquipmentOrder::STATUS_CONFIRMED,
            EquipmentOrder::STATUS_SHIPPED,
            EquipmentOrder::STATUS_DELIVERED,
        ])->sum('total_aoa');

        return view('admin.dashboard', [
            'totalOrders' => $totalOrders,
            'paidOrders' => $paidOrders,
            'awaitingPayment' => $awaitingPayment,
            'totalRevenueAoa' => $totalRevenueAoa,
            'recentOrders' => $recentOrders,
            'pendingResellers' => $pendingResellers,
            'totalResellers' => $totalResellers,
            'totalProducts' => $totalProducts,
            'totalEquipOrders' => $totalEquipOrders,
            'newEquipOrders' => $newEquipOrders,
            'totalEquipRevenue' => $totalEquipRevenue,
        ]);
    }

    public function reports()
    {
        $byStatus = AutovendaOrder::selectRaw('status, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $latestDays = AutovendaOrder::selectRaw('DATE(created_at) as day, COUNT(*) as total, SUM(amount_aoa) as total_amount')
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(14)
            ->get();

        return view('admin.reports', [
            'byStatus' => $byStatus,
            'latestDays' => $latestDays,
        ]);
    }
}
