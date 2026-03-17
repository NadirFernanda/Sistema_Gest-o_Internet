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
        // compensacoes table has no cliente_id column — query via plan IDs
        $planoIds = $cliente->planos()->pluck('id')->toArray();
        $compensacoes = Compensacao::whereIn('plano_id', $planoIds)
            ->orderBy('created_at', 'desc')
            ->get();
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

        // determine role-based monthly limit (matching seeded role names)
        $limit = 0; // 0 means no ability unless role matches
        if ($user->hasRole('Administrador')) {
            $limit = null; // unlimited for Administrador
        } elseif ($user->hasRole('Gestor')) {
            $limit = 3;
        } elseif ($user->hasRole('Colaborador')) {
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

        // Localiza o plano ativo mais recente do cliente
        $plano = $cliente->planos()
            ->whereRaw("LOWER(TRIM(COALESCE(estado, ''))) = ?", ['ativo'])
            ->where(function($q) {
                $q->where('ativo', true)->orWhereNull('ativo');
            })
            ->orderByDesc('data_ativacao')
            ->first();

        if (! $plano) {
            try {
                $todos = $cliente->planos()->get(['id', 'nome', 'estado', 'ativo', 'data_ativacao'])->map(function ($p) {
                    return $p->toArray();
                });
                \Log::warning('compensarDias (store): nenhum plano ativo encontrado', [
                    'cliente_id' => $cliente->id,
                    'planos' => $todos,
                ]);
            } catch (\Exception $e) {
                \Log::warning('compensarDias (store): falha ao listar planos para debug', [
                    'cliente_id' => $cliente->id,
                    'err' => $e->getMessage(),
                ]);
            }

            return redirect()->back()->with('error', 'Nenhum plano ativo encontrado para este cliente. Verifique estado/flag do plano.');
        }

        $hoje = Carbon::today();

        // Determina a data atual de término/renovação do plano
        try {
            if (!empty($plano->proxima_renovacao)) {
                $currentNext = Carbon::parse($plano->proxima_renovacao);
            } elseif (!empty($plano->data_ativacao) && $plano->ciclo) {
                $cicloInt = intval(filter_var($plano->ciclo, FILTER_SANITIZE_NUMBER_INT));
                if ($cicloInt <= 0) {
                    $cicloInt = (int) $plano->ciclo;
                }
                \Log::debug('compensarDias (store): cicloInt resolved', [
                    'plano_id' => $plano->id,
                    'ciclo_raw' => $plano->ciclo,
                    'ciclo_int' => $cicloInt,
                ]);
                $currentNext = Carbon::parse($plano->data_ativacao)->addDays($cicloInt - 1);
            } else {
                $currentNext = $hoje;
            }
        } catch (\Exception $e) {
            \Log::warning('compensarDias (store): falha ao parsear datas do plano', [
                'plano_id' => $plano->id,
                'err' => $e->getMessage(),
            ]);
            $currentNext = $hoje;
        }

        // Regra de negócio (compensação): a compensação deve ser contabilizada
        // a partir da data em que termina a janela ($currentNext), independentemente
        // da data de pagamento/hoje. Pagamentos seguem lógica distinta (data de pagamento).
        $base = $currentNext;

        $anterior = $currentNext->toDateString();
        $diasComp = (int) $request->input('dias_compensados');
        $novo = $base->copy()->addDays($diasComp)->toDateString();

        $plano->proxima_renovacao = $novo;
        $plano->save();

        // Regista detalhe da compensação na mesma tabela de histórico usada por adicionarJanela
        try {
            Compensacao::create([
                'plano_id'        => $plano->id,
                'user_id'         => $user->id,
                'dias_compensados' => $diasComp,
                'anterior'        => $anterior,
                'novo'            => $novo,
            ]);
        } catch (\Exception $e) {
            \Log::warning('compensarDias (store): falha ao gravar registro de compensacao', [
                'err' => $e->getMessage(),
                'plano_id' => $plano->id,
            ]);
        }

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', "Compensados {$diasComp} dias. Próxima renovação: " . Carbon::parse($novo)->format('d/m/Y'));
    }
}
