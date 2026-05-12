<?php

namespace App\Http\Controllers;

use App\Mail\ResellerApplicationAdminMail;
use App\Mail\ResellerApplicationApplicantMail;
use App\Models\ResellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ResellerController extends Controller
{
    public function showForm()
    {
        return view('store.reseller-apply');
    }

    public function submit(Request $request)
    {
        // --- Defesa 1: Honeypot ---
        // Campo invisível a humanos. Bots preenchem-no automaticamente.
        if ($request->filled('website')) {
            Log::warning('ResellerApply: honeypot ativado', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'ua' => $request->userAgent(),
            ]);
            // Resposta silenciosa para não alertar o bot
            return redirect()->route('reseller.apply.thankyou')
                ->with('status', 'Pedido enviado com sucesso, iremos analisar e entrar em contacto.');
        }

        // --- Defesa 2: Timing check ---
        // Bots submetem em <1s. Humanos demoram pelo menos 3 segundos a preencher.
        $formTime = (int) $request->input('_form_time', 0);
        $elapsed  = time() - $formTime;
        if ($formTime === 0 || $elapsed < 3) {
            Log::warning('ResellerApply: submissão demasiado rápida (possível bot)', [
                'ip'      => $request->ip(),
                'email'   => $request->input('email'),
                'elapsed' => $elapsed,
                'ua'      => $request->userAgent(),
            ]);
            return redirect()->route('reseller.apply')
                ->withErrors(['email' => 'Submissão demasiado rápida. Por favor tente novamente.'])
                ->withInput($request->except(['_form_time', 'website']));
        }

        $validated = $request->validate([
            'full_name'             => 'required|string|min:5|max:255',
            'document_number'       => 'required|string|min:5|max:100',
            'address'               => 'required|string|min:10|max:255',
            'email'                 => 'required|email|max:254',
            'phone'                 => 'required|string|min:9|max:50',
            'installation_location' => 'required|string|min:5|max:255',
            'internet_type'         => 'required|in:own,angolawifi',
        ]);

        // --- Defesa 3: Rate limit por email (independente do IP) ---
        // Impede que bots com IPs rotativos inundem o mesmo email ou criem
        // centenas de candidaturas com emails diferentes em sequência rápida.
        $emailKey = 'reseller_apply_email:' . sha1(strtolower($validated['email']));
        if (RateLimiter::tooManyAttempts($emailKey, 1)) {
            $minutes = (int) ceil(RateLimiter::availableIn($emailKey) / 60);
            Log::warning('ResellerApply: rate limit por email atingido', [
                'ip'    => $request->ip(),
                'email' => $validated['email'],
            ]);
            return redirect()->route('reseller.apply')
                ->withErrors(['email' => "Já foi submetida uma candidatura recente com este e-mail. Aguarde {$minutes} minuto(s) antes de tentar novamente."])
                ->withInput($request->except(['_form_time', 'website']));
        }
        RateLimiter::hit($emailKey, 600); // bloqueia o mesmo email por 10 minutos

        $subject = 'Quero ser agente revendedor';
        $message = "Saudações prezados,\nVenho pelo intermédio deste manifestar o interesse para ser agente revendedor do serviço AngolaWiFi.";

        // Protect existing approved/rejected applications from being overwritten.
        // An attacker who knows an approved reseller's e-mail could submit this form
        // and reset their status to pending, locking them out of the panel.
        $existing = ResellerApplication::where('email', $validated['email'])->first();
        if ($existing && $existing->status !== ResellerApplication::STATUS_PENDING) {
            return redirect()->route('reseller.apply')
                ->withErrors(['email' => 'Já existe uma candidatura processada para este e-mail. Contacte o suporte se precisar de assistência.']);
        }

        $application = ResellerApplication::updateOrCreate(
            ['email' => $validated['email']],
            [
                'full_name'             => $validated['full_name'],
                'document_number'       => $validated['document_number'],
                'address'               => $validated['address'],
                'phone'                 => $validated['phone'],
                'installation_location' => $validated['installation_location'],
                'internet_type'         => $validated['internet_type'],
                'reseller_mode'         => $validated['internet_type'],
                'subject'               => $subject,
                'message'               => $message,
                'status'                => ResellerApplication::STATUS_PENDING,
            ]
        );

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
