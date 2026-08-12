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

        $movimentos = WhatsappLedger::query()
            ->selectRaw("whatsapp_ledger.*, SUM(CASE WHEN tipo = 'credito' THEN valor ELSE -valor END) OVER (ORDER BY created_at ASC, id ASC) as saldo_apos")
            ->with('registadoPor')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.saldo-whatsapp.index', compact('saldo', 'movimentos'));
    }

    public function carregar(Request $request)
    {
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
}
