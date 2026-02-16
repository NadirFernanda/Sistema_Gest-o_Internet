<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Plano;
use App\Models\PlanTemplate;

class PlanoController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('PlanoController@store - Request recebido', ['request' => $request->all()]);
        try {
            // If a template_id is provided, load it and enforce its values server-side.
            $template = null;
            if ($request->filled('template_id')) {
                $templateId = $request->input('template_id');
                if ($templateId) {
                    $template = PlanTemplate::find($templateId);
                } else {
                    return back()->withErrors(['template_id' => 'Template não selecionado'])->withInput();
                }
            }

            if ($template) {
                // Minimal validation when a template is used (security: template values will be applied server-side)
                $validated = $request->validate([
                    'template_id' => 'required|exists:plan_templates,id',
                    'cliente_id' => 'required|exists:clientes,id',
                    'estado' => 'required|string',
                    'data_ativacao' => 'required|date',
                ]);

                // Overwrite/ensure values come from the template
                $validated['nome'] = $template->name;
                $validated['descricao'] = $template->description ?? '';
                $validated['preco'] = (string) number_format($template->preco ?? 0, 2, '.', '');
                $validated['ciclo'] = $template->ciclo;
                $validated['template_id'] = $template->id;
            } else {
                // Full validation when no template is selected
                $validated = $request->validate([
                    'nome' => 'required|string|max:255',
                    'descricao' => 'required|string',
                    'preco' => 'required|numeric|min:0',
                    'ciclo' => 'required|integer|min:1',
                    'cliente_id' => 'required|exists:clientes,id',
                    'estado' => 'required|string',
                    'data_ativacao' => 'required|date',
                ]);
            }

            \Log::info('PlanoController@store - Dados validados', ['validated' => $validated, 'template_used' => $template ? $template->id : null]);
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
            \Log::info('PlanoController@store - Plano criado', ['plano' => $plano, 'template_used' => $template ? $template->id : null, 'created_by' => auth()->id()]);

            // If this is a regular web form submit (not expecting JSON), redirect
            if (! $request->wantsJson()) {
                return redirect()->route('planos.index')->with('success', 'Plano cadastrado com sucesso.');
            }

            \Log::info('PlanoController@store - Resposta enviada (JSON)', ['response' => ['success' => true, 'plano' => $plano]]);
            return response()->json(['success' => true, 'plano' => $plano], 201)
                ->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $request->wantsJson()) {
                return back()->withErrors($e->errors())->withInput();
            }
            return response()->json(['error' => 'Erro de validação', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('PlanoController@store - Erro ao cadastrar plano', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            \Log::info('PlanoController@store - Resposta enviada (erro)', ['response' => ['error' => 'Erro ao cadastrar plano', 'details' => $e->getMessage()]]);
            if (! $request->wantsJson()) {
                return back()->with('error', 'Erro ao cadastrar plano: ' . $e->getMessage())->withInput();
            }
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

        // Attach canonical web URLs to each plano so front-end can use server-generated routes
        $payload = $planos->map(function ($p) {
            $arr = $p->toArray();
            try {
                $arr['web_show'] = route('planos.show', ['plano' => $p->id]);
            } catch (\Exception $e) {
                $arr['web_show'] = '/planos/' . ($p->id ?? '');
            }
            try {
                $arr['web_edit'] = route('planos.edit', ['plano' => $p->id]);
            } catch (\Exception $e) {
                $arr['web_edit'] = '/planos/' . ($p->id ?? '') . '/edit';
            }
            try {
                $arr['web_delete'] = route('planos.destroy', ['plano' => $p->id]);
            } catch (\Exception $e) {
                $arr['web_delete'] = '/planos/' . ($p->id ?? '');
            }
            return $arr;
        });

        return response()->json($payload);
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
        // If this request is a normal web form submit, redirect to the show page
        if (! $request->wantsJson()) {
            return redirect()->route('planos.show', $plano->id)->with('success', 'Plano atualizado com sucesso.');
        }

        return response()->json(['success' => true, 'message' => 'Plano atualizado com sucesso.', 'plano' => $plano]);
    }

    /**
     * Web form store: accepts a normal web POST (CSRF) and redirects back to planos list.
     */
    public function storeWeb(Request $request)
    {
        \Log::info('PlanoController@storeWeb - Request recebido', ['request' => $request->all()]);
        try {
            $template = null;
            if ($request->filled('template_id')) {
                $templateId = $request->input('template_id');
                if ($templateId) {
                    $template = PlanTemplate::find($templateId);
                } else {
                    return back()->withErrors(['template_id' => 'Template não selecionado'])->withInput();
                }
            }

            if ($template) {
                $validated = $request->validate([
                    'template_id' => 'required|exists:plan_templates,id',
                    'cliente_id' => 'required|exists:clientes,id',
                    'estado' => 'required|string',
                    'data_ativacao' => 'required|date',
                ]);

                $validated['nome'] = $template->name;
                $validated['descricao'] = $template->description ?? '';
                $validated['preco'] = (string) number_format($template->preco ?? 0, 2, '.', '');
                $validated['ciclo'] = $template->ciclo;
                $validated['template_id'] = $template->id;
            } else {
                $validated = $request->validate([
                    'nome' => 'required|string|max:255',
                    'descricao' => 'required|string',
                    'preco' => 'required|numeric|min:0',
                    'ciclo' => 'required|integer|min:1',
                    'cliente_id' => 'required|exists:clientes,id',
                    'estado' => 'required|string',
                    'data_ativacao' => 'required|date',
                ]);
            }
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
            \Log::info('PlanoController@storeWeb - Plano criado', ['plano' => $plano, 'template_used' => $template ? $template->id : null, 'created_by' => auth()->id()]);
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
