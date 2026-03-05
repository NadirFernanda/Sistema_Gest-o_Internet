<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use Illuminate\Http\Request;

class AutovendaOrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = AutovendaOrder::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('wifi_code', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = AutovendaOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
        ]);
    }
}
