<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;

class ClienteController extends Controller
{
    /**
     * Retorna planos elegíveis para alerta de vencimento (para exibir na lista do frontend)
     * GET /api/alertas?dias=5
     */
    public function listarAlertas(Request $request)
    {
        $dias = $request->input('dias', 5);
        $hoje = now()->startOfDay();
        $planosRaw = \App\Models\Plano::with('cliente')
            ->where('estado', 'Ativo')
            ->get();
        $planos = $planosRaw->filter(function ($plano) use ($dias, $hoje) {
            if (!$plano->data_ativacao || !$plano->ciclo) {
                return false;
            }
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            return $diasRestantes >= 0 && $diasRestantes <= $dias;
        });
        $alertas = $planos->map(function($plano) use ($hoje) {
            $dataTermino = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataTermino, false);
            return [
                'id' => $plano->id,
                'nome' => $plano->cliente ? $plano->cliente->nome : '-',
                'plano' => $plano->nome,
                'contato' => $plano->cliente ? $plano->cliente->contato : '-',
                'diasRestantes' => $diasRestantes,
                'dataTermino' => $dataTermino->toDateString(),
            ];
        })->values();
        return response()->json($alertas);
    }
    /**
     * Dispara alertas de vencimento para uma lista de planos (por e-mail).
     * Espera receber um array de planos (plano_id, diasRestantes) no request.
     */
    public function dispararAlertas(Request $request)
    {
        $dias = $request->input('dias', 5); // padrão 5 dias
        $hoje = now();
        $planosRaw = \App\Models\Plano::with('cliente')
            ->whereNotNull('data_ativacao')
            ->get();
        $planos = $planosRaw->filter(function ($plano) use ($dias, $hoje) {
            if (!$plano->data_ativacao || !$plano->ciclo) {
                \Log::info('Plano ignorado: data_ativacao ou ciclo ausente', ['plano_id' => $plano->id, 'data_ativacao' => $plano->data_ativacao, 'ciclo' => $plano->ciclo]);
                return false;
            }
            $dataVencimento = \Carbon\Carbon::parse($plano->data_ativacao)->addDays($plano->ciclo - 1)->startOfDay();
            $diasRestantes = $hoje->diffInDays($dataVencimento, false);
            if ($plano->estado !== 'Ativo') {
                \Log::info('Plano ignorado: estado não ativo', ['plano_id' => $plano->id, 'estado' => $plano->estado]);
                return false;
            }
            if ($diasRestantes > $dias || $diasRestantes < 0) {
                \Log::info('Plano ignorado: diasRestantes fora do filtro', ['plano_id' => $plano->id, 'diasRestantes' => $diasRestantes, 'filtro' => $dias]);
                return false;
            }
            \Log::info('Plano elegível para alerta', ['plano_id' => $plano->id, 'diasRestantes' => $diasRestantes, 'data_ativacao' => $plano->data_ativacao, 'ciclo' => $plano->ciclo, 'estado' => $plano->estado]);
            return true;
        });

        $enviados = [];
        $erros = [];
        if ($planos->isEmpty()) {
            \Log::info('Nenhum plano elegível para alerta no momento.', ['dias' => $dias]);
        }
        foreach ($planos as $plano) {
            if ($plano->cliente) {
                $cliente = $plano->cliente;
                if (empty($cliente->email) || !filter_var($cliente->email, FILTER_VALIDATE_EMAIL)) {
                    $erros[] = 'Cliente sem e-mail válido: ' . $cliente->nome;
                    \Log::warning('Cliente sem e-mail válido para alerta', ['cliente_id' => $cliente->id, 'nome' => $cliente->nome, 'email' => $cliente->email]);
                    continue;
                }
                try {
                    $cliente->notify(new \App\Notifications\ClienteVencimentoAlert($cliente, $plano, $dias));
                    $cliente->notify(new \App\Notifications\ClienteVencimentoWhatsApp($cliente, $plano, $dias));
                    $enviados[] = $cliente->nome . ' <' . $cliente->email . '>';
                    \Log::info('Alerta enviado com sucesso', ['cliente_id' => $cliente->id, 'nome' => $cliente->nome, 'email' => $cliente->email]);
                } catch (\Exception $e) {
                    $erros[] = 'Erro ao enviar alerta para ' . $cliente->nome . ': ' . $e->getMessage();
                    \Log::error('Erro ao enviar alerta para cliente', ['cliente_id' => $cliente->id, 'nome' => $cliente->nome, 'email' => $cliente->email, 'erro' => $e->getMessage()]);
                }
            } else {
                $erros[] = 'Plano sem cliente associado: ' . $plano->id;
                \Log::warning('Plano sem cliente associado', ['plano_id' => $plano->id]);
            }
        }
        \Log::info('dispararAlertas - Enviados:', ['enviados' => $enviados, 'erros' => $erros]);
        return response()->json([
            'success' => true,
            'enviados' => $enviados,
            'erros' => $erros,
            'total_planos' => $planos->count()
        ]);
    }

    /**
     * Método utilitário para testar envio de alerta de vencimento por e-mail para um cliente.
     * Exemplo de uso no Tinker:
     *   App\Http\Controllers\ClienteController::enviarAlertaEmailTeste(1, 3)
     */
    public static function enviarAlertaEmailTeste($clienteId, $diasRestantes = 3)
    {
        $cliente = \App\Models\Cliente::findOrFail($clienteId);
        $cliente->notify(new \App\Notifications\ClienteVencimentoAlert($cliente, $diasRestantes));
        return 'Alerta de vencimento enviado para ' . $cliente->email;
    }
    public function index()
    {
        $clientes = Cliente::all();
        // Se for chamada via API/AJAX, retorna JSON
        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($clientes);
        }
        // Caso contrário, retorna a view normalmente
        return view('clientes', compact('clientes'));
    }

    public function show($id)
    {
        $cliente = Cliente::with('equipamentos')->findOrFail($id);
        return view('clientes', compact('cliente'));
    }
    public function store(Request $request)
    {
        \Log::info('Entrou no método store do ClienteController', ['request' => $request->all()]);
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'email' => 'required|email|unique:clientes,email',
                'contato' => 'required|string|max:20|unique:clientes,contato',
            ]);

            $cliente = Cliente::create($validated);
            \Log::info('Cliente cadastrado', ['cliente' => $cliente]);

            return response()->json([
                'success' => true,
                'cliente' => $cliente
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors();
            $customMessages = [];
            if ($errors->has('email')) {
                $customMessages['email'] = 'Já existe um cliente cadastrado com este e-mail.';
            }
            if ($errors->has('contato')) {
                $customMessages['contato'] = 'Já existe um cliente cadastrado com este contato.';
            }
            $defaultMessages = $errors->messages();
            $allMessages = array_merge($defaultMessages, $customMessages);
            return response()->json([
                'success' => false,
                'errors' => $allMessages
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|email|unique:clientes,email,' . $id,
            'contato' => 'sometimes|required|string|max:20|unique:clientes,contato,' . $id,
            'bi' => 'sometimes|nullable|string|max:32',
        ]);

        $cliente = Cliente::findOrFail($id);
        $cliente->update($validated);

        return response()->json(['success' => true, 'cliente' => $cliente]);
    }

    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json(['success' => true]);
    }
}
