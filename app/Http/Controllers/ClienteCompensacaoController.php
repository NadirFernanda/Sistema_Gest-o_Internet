<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compensacao;
use App\Models\Cliente;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ClienteCompensacaoController extends Controller
{
    /**
     * Show compensations history for a cliente.
     */
    public function index($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $compensacoes = Compensacao::where('cliente_id', $cliente->id)->orderBy('created_at', 'desc')->get();
        return view('clientes.compensacoes', compact('cliente', 'compensacoes'));
    }

    /**
     * Store a compensation and enforce monthly limits by role.
     */
    public function store(Request $request, $clienteId)
    {
        $request->validate([
            'dias_compensados' => 'required|integer|min:1|max:90',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->back()->with('error', 'Usuário não autenticado.');
        }

        // determine role-based monthly limit
        $limit = 0; // 0 means no ability unless role matches
        if ($user->hasRole('administrador')) {
            $limit = null; // unlimited
        } elseif ($user->hasRole('gerente')) {
            $limit = 3;
        } elseif ($user->hasRole('colaborador')) {
            $limit = 2;
        } else {
            // default: no allowance
            $limit = 0;
        }

        // enforce monthly limit (count by user)
        if (!is_null($limit)) {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $count = Compensacao::where('user_id', $user->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            if ($limit === 0 || $count >= $limit) {
                return redirect()->back()->with('error', 'Limite mensal de compensações atingido.');
            }
        }

        $cliente = Cliente::findOrFail($clienteId);

        $comp = Compensacao::create([
            'cliente_id' => $cliente->id,
            'user_id' => $user->id,
            'dias' => (int) $request->input('dias_compensados'),
            'motivo' => $request->input('motivo'),
        ]);

        // NOTE: if you want the compensation to actually extend a plan expiry date,
        // update the relevant Cliente/Plano field here (business logic dependent).

        return redirect()->route('clientes.show', $cliente->id)->with('success', 'Compensação registada com sucesso.');
    }
}
