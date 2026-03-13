<?php

namespace App\Http\Controllers;

use App\Models\InstallationAppointment;
use Illuminate\Http\Request;

class InstallationAppointmentController extends Controller
{
    public function show()
    {
        return view('pages.agendar-instalacao');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:120'],
            'phone'   => ['required', 'string', 'min:7', 'max:30'],
            'type'    => ['required', 'in:familia,empresa,instituicao'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        InstallationAppointment::create($validated);

        return redirect()->route('appointment.show')
            ->with('success', 'O seu pedido foi registado! A nossa equipa entrará em contacto brevemente.');
    }
}
