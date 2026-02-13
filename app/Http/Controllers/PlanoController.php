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
            // prevent duplicate active plan name for the same client
            try {
                $exists = Plano::where('cliente_id', $validated['cliente_id'])
                    ->where('nome', $validated['nome'])
                    ->where('ativo', true)
                    ->exists();
            } catch (\Exception $e) {
                // if the 'ativo' column doesn't exist or another DB issue, fallback to safer check
                $exists = Plano::where('cliente_id', $validated['cliente_id'])
                    ->where('nome', $validated['nome'])
                    ->exists();
            }
            if ($exists) {
                \Log::warning('PlanoController@store - Plano duplicado detectado', ['cliente_id' => $validated['cliente_id'], 'nome' => $validated['nome']]);
                return response()->json(['error' => 'Já existe um plano ativo com esse nome para este cliente.'], 409);
            }

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

    /**
     * Web view for planos list. Returns the Blade view with clients for the inline form.
     */
    public function webIndex(Request $request)
    {
        $clientes = \App\Models\Cliente::orderBy('nome')->get();
        return view('planos', compact('clientes'));
    }

    /**
     * Show the standalone create page for planos.
     */
    public function createWeb()
    {
        $clientes = \App\Models\Cliente::orderBy('nome')->get();
        return view('planos.create', compact('clientes'));
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

    /**
     * Web form store: accepts a normal web POST (CSRF) and redirects back to planos list.
     */
    public function storeWeb(Request $request)
    {
        \Log::info('PlanoController@storeWeb - Request recebido', ['request' => $request->all()]);
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
            // prevent duplicate when submitting from web form
            try {
                $exists = Plano::where('cliente_id', $validated['cliente_id'])
                    ->where('nome', $validated['nome'])
                    ->where('ativo', true)
                    ->exists();
            } catch (\Exception $e) {
                $exists = Plano::where('cliente_id', $validated['cliente_id'])
                    ->where('nome', $validated['nome'])
                    ->exists();
            }
            if ($exists) {
                \Log::warning('PlanoController@storeWeb - Plano duplicado detectado', ['cliente_id' => $validated['cliente_id'], 'nome' => $validated['nome']]);
                return back()->with('error', 'Já existe um plano ativo com esse nome para este cliente.')->withInput();
            }

            $plano = Plano::create($validated);
            \Log::info('PlanoController@storeWeb - Plano criado', ['plano' => $plano]);
            return redirect()->route('planos.index')->with('success', 'Plano cadastrado com sucesso.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('PlanoController@storeWeb - Erro ao cadastrar plano', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erro ao cadastrar plano: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Web view: show single plano in a simple page
     */
    public function webShow($id)
    {
        $plano = Plano::with('cliente')->findOrFail($id);
        return view('planos.show', compact('plano'));
    }

    /**
     * Web view: edit form for plano
     */
    public function editWeb($id)
    {
        $plano = Plano::findOrFail($id);
        $clientes = \App\Models\Cliente::orderBy('nome')->get();
        return view('planos.edit', compact('plano','clientes'));
    }

    /**
     * Web destroy (handle form DELETE)
     */
    public function destroyWeb($id)
    {
        $plano = Plano::findOrFail($id);
        $plano->delete();
        return redirect()->route('planos.index')->with('success', 'Plano removido.');
    }
}
