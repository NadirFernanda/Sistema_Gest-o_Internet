<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailSettingsController extends Controller
{
    public function index()
    {
        $cfg = SystemSetting::mailConfig();
        return view('admin.email-settings.index', compact('cfg'));
    }

    public function update(Request $request)
    {

        $validated = $request->validate([
            'mail_host'       => ['required', 'string', 'max:255'],
            'mail_port'       => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_username'   => ['nullable', 'string', 'max:255'],
            'mail_password'   => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['required', 'in:tls,ssl,none'],
            'mail_from_name'  => ['required', 'string', 'max:100'],
            'mail_from_email' => ['required', 'email', 'max:255'],
        ]);

        // Não guardar password vazia (manter a anterior)
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Configurações de email guardadas.');
    }

    public function testar(Request $request)
    {

        $validated = $request->validate([
            'email_teste' => ['required', 'email'],
        ]);

        $cfg = SystemSetting::mailConfig();

        if (empty($cfg['host'])) {
            return response()->json(['error' => 'Configure primeiro o servidor de email.'], 422);
        }

        try {
            // Aplica configuração guardada na BD em runtime
            config([
                'mail.mailers.smtp.host'       => $cfg['host'],
                'mail.mailers.smtp.port'       => $cfg['port'],
                'mail.mailers.smtp.username'   => $cfg['username'],
                'mail.mailers.smtp.password'   => $cfg['password'],
                'mail.mailers.smtp.encryption' => $cfg['encryption'] === 'none' ? null : $cfg['encryption'],
                'mail.from.address'            => $cfg['from_email'],
                'mail.from.name'               => $cfg['from_name'],
            ]);

            Mail::raw('Este é um email de teste enviado pelo painel SGA — AngolaWiFi.', function ($msg) use ($validated, $cfg) {
                $msg->to($validated['email_teste'])
                    ->subject('Teste de email — AngolaWiFi')
                    ->from($cfg['from_email'], $cfg['from_name']);
            });

            return response()->json(['success' => 'Email enviado com sucesso para ' . $validated['email_teste']]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
