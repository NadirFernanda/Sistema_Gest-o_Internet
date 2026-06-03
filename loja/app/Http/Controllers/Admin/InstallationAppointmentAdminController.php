<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InstallationAppointmentMail;
use App\Models\InstallationAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        $oldStatus = $appointment->status;
        $appointment->update($validated);

        // Enviar contrato de comodato por e-mail quando a instalação é confirmada (contacted)
        if ($oldStatus !== 'contacted' && $validated['status'] === 'contacted' && $appointment->email) {
            try {
                Mail::to($appointment->email)->send(new InstallationAppointmentMail($appointment));
            } catch (\Throwable $e) {
                Log::warning('InstallationAppointment: erro ao enviar contrato', [
                    'id'    => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $msg = 'Estado actualizado.';
        if ($validated['status'] === 'contacted' && $oldStatus !== 'contacted' && $appointment->email) {
            $msg = 'Estado actualizado — contrato de comodato enviado por e-mail para ' . $appointment->email . '.';
        }

        return back()->with('success', $msg);
    }
}
