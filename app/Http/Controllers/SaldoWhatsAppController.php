<?php

namespace App\Http\Controllers;

use App\Models\WhatsappLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
