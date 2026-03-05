<?php

namespace App\Http\Controllers;

use App\Mail\ResellerApplicationAdminMail;
use App\Mail\ResellerApplicationApplicantMail;
use App\Models\ResellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResellerController extends Controller
{
    public function showForm()
    {
        return view('store.reseller-apply');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'document_number' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:50',
            'installation_location' => 'required|string|max:255',
        ]);

        $subject = 'Quero ser agente revendedor';
        $message = "Saudações prezados,\nVenho pelo intermédio deste manifestar o interesse para ser agente revendedor do serviço AngolaWiFi.";

        $application = ResellerApplication::create([
            'full_name' => $validated['full_name'],
            'document_number' => $validated['document_number'],
            'address' => $validated['address'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'installation_location' => $validated['installation_location'],
            'subject' => $subject,
            'message' => $message,
            'status' => ResellerApplication::STATUS_PENDING,
        ]);

        // E-mail para o administrador
        $adminEmail = config('mail.from.address');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new ResellerApplicationAdminMail($application));
        }

        // E-mail automático para o candidato com informação de que o pedido está em análise
        Mail::to($application->email)->send(new ResellerApplicationApplicantMail($application));

        return redirect()
            ->route('reseller.apply.thankyou')
            ->with('status', 'Pedido enviado com sucesso, iremos analisar e entrar em contacto.');
    }

    public function thankYou()
    {
        return view('store.reseller-apply-thankyou');
    }
}
