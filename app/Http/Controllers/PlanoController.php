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
            // Require a template_id for creating planos. Template values are enforced server-side.
            $validated = $request->validate([
                'template_id' => 'required|exists:plan_templates,id',
                'cliente_id' => 'required|exists:clientes,id',
                'estado' => 'required|string',
                'data_ativacao' => 'required|date',
            ]);

            $template = PlanTemplate::find($validated['template_id']);
            if (! $template) {
                return back()->withErrors(['template_id' => 'Template não encontrado'])->withInput();
            }

            // Populate plan fields from template (server-side authoritative)
            $validated['nome'] = $template->name;
            $validated['descricao'] = $template->description ?? '';
            $validated['preco'] = (string) number_format($template->preco ?? 0, 2, '.', '');
            $validated['ciclo'] = $template->ciclo;
            $validated['template_id'] = $template->id;

            \Log::info('PlanoController@store - Dados validados', ['validated' => $validated, 'template_used' => $template ? $template->id : null]);
            // Allow multiple contracts of the same plan for a client.
            // Previously the code prevented creating a plano with the same name
            // for a client when an active one existed. For cases where a client
            // may contract the same plan more than once (e.g. separate services
            // or renewals), we no longer block creation here.

            $plano = Plano::create($validated);
            \Log::info('PlanoController@store - Plano criado', ['plano' => $plano, 'template_used' => $template ? $template->id : null, 'created_by' => auth()->id()]);

            // If this is a regular web form submit (not expecting JSON), redirect
            if (! $request->wantsJson()) {
                return redirect()->route('planos.index')->with('success', 'Plano cadastrado com sucesso.');
            }

            \Log::info('PlanoController@store - Resposta enviada (JSON)', ['response' => ['success' => true, 'message' => 'Plano cadastrado com sucesso.', 'plano' => $plano]]);
            return response()->json(['success' => true, 'message' => 'Plano cadastrado com sucesso.', 'plano' => $plano], 201)
                ->header('Content-Type', 'application/json');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $request->wantsJson()) {
                return back()->withErrors($e->errors())->withInput();
            }
            return response()->json(['success' => false, 'message' => 'Erro de validação', 'errors' => $e->errors()], 422);
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
            return response()->json(['success' => false, 'message' => 'Erro ao cadastrar plano', 'error' => $e->getMessage()], 500);
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

        // Attach canonical web URLs to each plano and permission flags so front-end
        // can safely decide which actions to render per-plan.
        $user = auth()->user();
        $payload = $planos->map(function ($p) use ($user) {
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

            // Permission flags (default to false on any exception)
            try {
                $arr['can_edit'] = $user ? (bool) $user->can('update', $p) : false;
                $arr['can_delete'] = $user ? (bool) $user->can('delete', $p) : false;
            } catch (\Exception $e) {
                $arr['can_edit'] = false;
                $arr['can_delete'] = false;
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
            return response()->json(['success' => false, 'message' => 'Plano não encontrado'], 404);
        }
        $plano->delete();
        return response()->json(['success' => true, 'message' => 'Plano removido com sucesso.']);
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
            // Require a template_id for web form creation. Populate plan fields from template.
            $validated = $request->validate([
                'template_id' => 'required|exists:plan_templates,id',
                'cliente_id' => 'required|exists:clientes,id',
                'estado' => 'required|string',
                'data_ativacao' => 'required|date',
            ]);

            $template = PlanTemplate::find($validated['template_id']);
            if (! $template) {
                return back()->withErrors(['template_id' => 'Template não encontrado'])->withInput();
            }

            $validated['nome'] = $template->name;
            $validated['descricao'] = $template->description ?? '';
            $validated['preco'] = (string) number_format($template->preco ?? 0, 2, '.', '');
            $validated['ciclo'] = $template->ciclo;
            $validated['template_id'] = $template->id;
            // Allow multiple contracts of the same plan via web form as well.
            // Removing the duplicate-blocking logic to permit multiple active
            // entries when required by business logic.

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
