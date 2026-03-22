<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherPlan;
use Illuminate\Http\Request;

/**
 * VoucherPlanAdminController — Gestão dos planos de voucher WiFi para revendedores.
 *
 * Permite ao administrador criar, editar e activar/desactivar planos.
 * Os revendedores vêem apenas os planos activos no seu painel de compras.
 */
class VoucherPlanAdminController extends Controller
{
    public function index()
    {
        $plans = VoucherPlan::orderBy('sort_order')->get();
        return view('admin.voucher_plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug'               => 'required|string|max:50|unique:voucher_plans,slug|regex:/^[a-z0-9_-]+$/',
            'name'               => 'required|string|max:100',
            'validity_label'     => 'required|string|max:50',
            'validity_minutes'   => 'required|integer|min:1',
            'speed_label'        => 'nullable|string|max:50',
            'price_public_aoa'   => 'required|integer|min:0',
            'price_reseller_aoa' => 'required|integer|min:0',
            'sort_order'         => 'required|integer|min:0',
        ]);

        $data['active'] = true;

        VoucherPlan::create($data);

        return redirect()->route('admin.voucher_plans.index')
            ->with('success', "Plano \"{$data['name']}\" criado com sucesso.");
    }

    public function update(Request $request, VoucherPlan $voucherPlan)
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'validity_label'     => 'required|string|max:50',
            'validity_minutes'   => 'required|integer|min:1',
            'speed_label'        => 'nullable|string|max:50',
            'price_public_aoa'   => 'required|integer|min:0',
            'price_reseller_aoa' => 'required|integer|min:0',
            'sort_order'         => 'required|integer|min:0',
        ]);

        $voucherPlan->update($data);

        return redirect()->route('admin.voucher_plans.index')
            ->with('success', "Plano \"{$voucherPlan->name}\" actualizado.");
    }

    public function toggle(VoucherPlan $voucherPlan)
    {
        $voucherPlan->update(['active' => !$voucherPlan->active]);

        $state = $voucherPlan->active ? 'activado' : 'desactivado';
        return redirect()->route('admin.voucher_plans.index')
            ->with('success', "Plano \"{$voucherPlan->name}\" {$state}.");
    }
}
