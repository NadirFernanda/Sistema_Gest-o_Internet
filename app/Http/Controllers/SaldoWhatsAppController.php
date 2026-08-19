<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SaldoWhatsAppController extends Controller
{
    public function index(Request $request)
    {
        $saldo = WhatsappLedger::saldoAtual();
        $podeCarregar = $this->podeCarregar();

        $movimentos = WhatsappLedger::query()
            ->selectRaw("whatsapp_ledger.*, SUM(CASE WHEN tipo = 'credito' THEN valor ELSE -valor END) OVER (ORDER BY created_at ASC, id ASC) as saldo_apos")
            ->with('registadoPor')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.saldo-whatsapp.index', compact('saldo', 'movimentos', 'podeCarregar'));
    }

    public function carregar(Request $request)
    {
        abort_unless($this->podeCarregar(), 403, 'Só o administrador do sistema pode registar carregamentos de saldo.');

        $validated = $request->validate([
            'valor'     => ['required', 'numeric', 'min:1'],
            'descricao' => ['nullable', 'string', 'max:255'],
        ]);

        WhatsappLedger::carregar(
            (float) $validated['valor'],
            $validated['descricao'] ?: 'Carregamento manual de saldo',
            Auth::id()
        );

        return back()->with('success', 'Saldo carregado com sucesso.');
    }

    /**
     * Estado da ligação WhatsApp (JSON) — consumido pelo polling JS da view.
     * Devolve: { connected: bool, qrcode: string|null, status: string }
     */
    public function estadoWhatsApp()
    {
        $ev = $this->evolutionConfig();
        if (! $ev) {
            return response()->json(['connected' => false, 'qrcode' => null, 'status' => 'não configurado']);
        }

        try {
            // Estado da ligação
            $stateRes = Http::withHeaders(['apikey' => $ev['key']])
                ->timeout(8)
                ->get("{$ev['url']}/instance/connectionState/{$ev['instance']}");

            $state = $stateRes->json('instance.state') ?? $stateRes->json('state') ?? 'unknown';
            $connected = strtolower($state) === 'open';

            $qrcode = null;
            if (! $connected) {
                // Pedir QR code
                $qrRes = Http::withHeaders(['apikey' => $ev['key']])
                    ->timeout(8)
                    ->get("{$ev['url']}/instance/connect/{$ev['instance']}");

                $qrcode = $qrRes->json('base64')
                    ?? $qrRes->json('qrcode.base64')
                    ?? $qrRes->json('data.qrcode.base64')
                    ?? null;

                // Remove prefixo data URI se vier incluído
                if ($qrcode && str_contains($qrcode, ',')) {
                    $qrcode = explode(',', $qrcode, 2)[1];
                }
            }

            return response()->json([
                'connected' => $connected,
                'status'    => $state,
                'qrcode'    => $qrcode,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['connected' => false, 'qrcode' => null, 'status' => 'erro: ' . $e->getMessage()]);
        }
    }

    /**
     * Solicita código de emparelhamento por número de telefone.
     * POST { phone: "244XXXXXXXXX" }
     */
    public function pairingCode(Request $request)
    {
        $ev = $this->evolutionConfig();
        if (! $ev) {
            return response()->json(['error' => 'Evolution API não configurada.'], 503);
        }

        $phone = preg_replace('/\D/', '', $request->input('phone', ''));
        if (strlen($phone) < 9) {
            return response()->json(['error' => 'Número de telefone inválido.'], 422);
        }

        try {
            $res = Http::withHeaders(['apikey' => $ev['key']])
                ->timeout(15)
                ->post("{$ev['url']}/instance/pairingCode/{$ev['instance']}", [
                    'phoneNumber' => $phone,
                ]);

            $code = $res->json('pairingCode') ?? $res->json('code') ?? null;

            if ($code) {
                return response()->json(['code' => $code]);
            }

            return response()->json(['error' => 'Não foi possível obter o código. Resposta: ' . substr($res->body(), 0, 200)], 500);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function evolutionConfig(): ?array
    {
        $url      = rtrim((string) config('services.evolution.url'), '/');
        $key      = (string) config('services.evolution.key');
        $instance = (string) config('services.evolution.instance');

        if (! $url || ! $key || ! $instance) return null;

        return compact('url', 'key', 'instance');
    }

    /**
     * Só os emails listados em SUPER_ADMIN_EMAILS (.env) podem carregar saldo —
     * clientes com role Administrador continuam a ver o extrato, mas não têm
     * como creditar saldo a si próprios. Ver config/services.php.
     */
    private function podeCarregar(): bool
    {
        $email = Auth::user()?->email;

        return $email && in_array($email, config('services.super_admin_emails', []), true);
    }
}
