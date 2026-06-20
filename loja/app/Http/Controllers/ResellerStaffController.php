<?php

namespace App\Http\Controllers;

use App\Models\ResellerApplication;
use App\Models\ResellerStaff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResellerStaffController extends Controller
{
    private function getApplication(Request $request): ?ResellerApplication
    {
        $id = $request->session()->get('reseller_id');
        if (!$id) return null;
        $app = ResellerApplication::find($id);
        return ($app && $app->status === 'approved') ? $app : null;
    }

    public function index(Request $request)
    {
        $application = $this->getApplication($request);
        if (!$application) return redirect()->route('reseller.panel');

        $staff = ResellerStaff::where('reseller_application_id', $application->id)
            ->orderByDesc('created_at')
            ->get();

        // Enrich with per-staff stats (one query)
        $staffIds = $staff->pluck('id');
        $salesStats = \Illuminate\Support\Facades\DB::table('wifi_codes')
            ->join('voucher_plans', 'wifi_codes.plan_id', '=', 'voucher_plans.slug')
            ->whereIn('wifi_codes.reseller_staff_id', $staffIds)
            ->whereNotNull('wifi_codes.reseller_distributed_at')
            ->selectRaw('wifi_codes.reseller_staff_id, COUNT(*) as sold_count, COALESCE(SUM(voucher_plans.price_public_aoa),0) as sales_aoa')
            ->groupBy('wifi_codes.reseller_staff_id')
            ->get()
            ->keyBy('reseller_staff_id');

        $monthlySales = \Illuminate\Support\Facades\DB::table('wifi_codes')
            ->whereIn('reseller_staff_id', $staffIds)
            ->whereNotNull('reseller_distributed_at')
            ->whereYear('reseller_distributed_at', now()->year)
            ->whereMonth('reseller_distributed_at', now()->month)
            ->selectRaw('reseller_staff_id, COUNT(*) as sold_this_month')
            ->groupBy('reseller_staff_id')
            ->get()
            ->keyBy('reseller_staff_id');

        return view('reseller.staff', compact('application', 'staff', 'salesStats', 'monthlySales'));
    }

    public function store(Request $request)
    {
        $application = $this->getApplication($request);
        if (!$application) return redirect()->route('reseller.panel');

        $count = ResellerStaff::where('reseller_application_id', $application->id)->count();
        if ($count >= ResellerStaff::MAX_PER_RESELLER) {
            return back()->with('error', 'Limite máximo de ' . ResellerStaff::MAX_PER_RESELLER . ' membros de equipa atingido.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'required|string|max:30',
            'email'     => 'nullable|email|max:255',
            'pin'       => ['required', 'string', 'min:4', 'max:6', 'regex:/^\d+$/'],
            'notes'     => 'nullable|string|max:500',
        ], [
            'pin.regex' => 'O PIN deve conter apenas dígitos (4 a 6 números).',
            'pin.min'   => 'O PIN deve ter pelo menos 4 dígitos.',
        ]);

        ResellerStaff::create([
            'reseller_application_id' => $application->id,
            'full_name' => $validated['full_name'],
            'phone'     => preg_replace('/\s+/', '', $validated['phone']),
            'email'     => $validated['email'] ?? null,
            'pin_hash'  => Hash::make($validated['pin']),
            'status'    => ResellerStaff::STATUS_ACTIVE,
            'notes'     => $validated['notes'] ?? null,
        ]);

        return back()->with('success', $validated['full_name'] . ' adicionado(a) à equipa com sucesso.');
    }

    public function toggle(Request $request, ResellerStaff $staff)
    {
        $application = $this->getApplication($request);
        if (!$application || $staff->reseller_application_id !== $application->id) abort(403);

        $newStatus = $staff->status === ResellerStaff::STATUS_ACTIVE
            ? ResellerStaff::STATUS_SUSPENDED
            : ResellerStaff::STATUS_ACTIVE;

        $staff->update(['status' => $newStatus]);

        $label = $newStatus === ResellerStaff::STATUS_ACTIVE ? 'activado(a)' : 'suspenso(a)';
        return back()->with('success', $staff->full_name . ' foi ' . $label . '.');
    }

    public function resetPin(Request $request, ResellerStaff $staff)
    {
        $application = $this->getApplication($request);
        if (!$application || $staff->reseller_application_id !== $application->id) abort(403);

        $validated = $request->validate([
            'pin' => ['required', 'string', 'min:4', 'max:6', 'regex:/^\d+$/'],
        ], [
            'pin.regex' => 'O PIN deve conter apenas dígitos.',
        ]);

        $staff->update(['pin_hash' => Hash::make($validated['pin'])]);
        return back()->with('success', 'PIN de ' . $staff->full_name . ' actualizado com sucesso.');
    }

    public function destroy(Request $request, ResellerStaff $staff)
    {
        $application = $this->getApplication($request);
        if (!$application || $staff->reseller_application_id !== $application->id) abort(403);

        $name = $staff->full_name;
        $staff->delete();
        return back()->with('success', $name . ' foi removido(a) da equipa.');
    }
}
