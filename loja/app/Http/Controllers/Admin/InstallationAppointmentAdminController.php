<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstallationAppointment;
use Illuminate\Http\Request;

class InstallationAppointmentAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = InstallationAppointment::orderByDesc('created_at');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $appointments = $query->paginate(25)->withQueryString();

        $counts = [
            'pending'   => InstallationAppointment::where('status', InstallationAppointment::STATUS_PENDING)->count(),
            'contacted' => InstallationAppointment::where('status', InstallationAppointment::STATUS_CONTACTED)->count(),
            'done'      => InstallationAppointment::where('status', InstallationAppointment::STATUS_DONE)->count(),
            'cancelled' => InstallationAppointment::where('status', InstallationAppointment::STATUS_CANCELLED)->count(),
        ];

        return view('admin.installation_appointments.index', compact('appointments', 'counts', 'status'));
    }

    public function updateStatus(Request $request, InstallationAppointment $appointment)
    {
        $validated = $request->validate([
            'status'      => ['required', 'in:pending,contacted,done,cancelled'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Estado actualizado.');
    }
}
