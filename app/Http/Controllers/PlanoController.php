<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plano;

class PlanoController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('PlanoController@store - Request recebido', ['request' => $request->all()]);
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'descricao' => 'required|string',
                'preco' => 'required|numeric|min:0',
                'ciclo' => 'required|integer|min:1',
                'cliente_id' => 'required|exists:clientes,id',
                'estado' => 'required|string',
                'data_ativacao' => 'required|date',
            ]);
            \Log::info('PlanoController@store - Dados validados', ['validated' => $validated]);
            $plano = Plano::create($validated);
            \Log::info('PlanoController@store - Plano criado', ['plano' => $plano]);
            \Log::info('PlanoController@store - Resposta enviada', ['response' => ['success' => true, 'plano' => $plano]]);
            return response()->json(['success' => true, 'plano' => $plano], 201)
                ->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Erro de validação', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('PlanoController@store - Erro ao cadastrar plano', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            \Log::info('PlanoController@store - Resposta enviada', ['response' => ['error' => 'Erro ao cadastrar plano', 'details' => $e->getMessage()]]);
            return response()->json(['error' => 'Erro ao cadastrar plano', 'details' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Plano::with('cliente');

        if ($busca = $request->query('busca')) {
            $busca = trim($busca);
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'ILIKE', "%{$busca}%")
                    ->orWhere('descricao', 'ILIKE', "%{$busca}%");
            })->orWhereHas('cliente', function ($q) use ($busca) {
                $q->where('nome', 'ILIKE', "%{$busca}%");
            });
        }

        $planos = $query->get();
        \Log::info('Planos retornados', ['total' => $planos->count()]);
        return response()->json($planos);
    }

    public function show($id)
    {
        $plano = Plano::with('cliente')->find($id);
        if (!$plano) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }
        return response()->json($plano);
    }
    public function destroy($id)
    {
        $plano = Plano::find($id);
        if (!$plano) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }
        $plano->delete();
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|required|string|max:255',
            'descricao' => 'sometimes|required|string',
            'preco' => 'sometimes|required|numeric|min:0',
            'ciclo' => 'sometimes|required|integer|min:1',
            'cliente_id' => 'sometimes|required|exists:clientes,id',
            'estado' => 'sometimes|required|string',
            'data_ativacao' => 'sometimes|required|date',
        ]);
        $plano = Plano::findOrFail($id);
        $plano->update($validated);
        return response()->json(['success' => true, 'plano' => $plano]);
    }
}
