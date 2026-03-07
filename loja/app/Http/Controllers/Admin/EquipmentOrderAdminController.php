<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentOrder;
use Illuminate\Http\Request;

class EquipmentOrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = EquipmentOrder::orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders   = $query->paginate(25)->withQueryString();
        $statuses = EquipmentOrder::allStatuses();

        return view('admin.equipment.orders.index', compact('orders', 'statuses'));
    }

    public function show(int $id)
    {
        $order    = EquipmentOrder::findOrFail($id);
        $statuses = EquipmentOrder::allStatuses();

        return view('admin.equipment.orders.show', compact('order', 'statuses'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $order = EquipmentOrder::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:' . implode(',', EquipmentOrder::allStatuses()),
        ]);

        $order->update(['status' => $request->input('status')]);

        return redirect()->route('admin.equipment.orders.show', $order->id)
            ->with('success', 'Estado da encomenda atualizado.');
    }
}
