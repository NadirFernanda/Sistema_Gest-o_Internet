<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Plano;
use App\Models\PlanTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
                'tipo' => 'required|in:familiar,institucional,empresarial,site',
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
            Cache::forget('sg_active_clients_count'); // invalida cache do contador de clientes activos
            Cache::forget('plan_templates_catalog:sales_counts');

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
        try {
            $query = Plano::with(['cliente', 'template']);

            // Filter by template_id when a specific plan type is selected
            if ($templateId = $request->query('template_id')) {
                $query->where('planos.template_id', (int) $templateId);
            }

            // Filter by estado (e.g. Ativo, Suspenso, Cancelado…)
            if ($estado = $request->query('estado')) {
                $query->where('planos.estado', $estado);
            }

            // Filter by tipo (familiar, institucional, empresarial, site)
            if ($tipo = $request->query('tipo')) {
                $query->where('planos.tipo', $tipo);
            }

            if ($busca = $request->query('busca')) {
            $busca = trim($busca);
            // Use LOWER(... ) LIKE ? to provide case-insensitive search that
            // works across different database engines (Postgres, MySQL, SQLite).
            $buscaParam = mb_strtolower($busca);

            $query->where(function ($q) use ($buscaParam) {
                $q->whereRaw('LOWER(planos.nome) LIKE ?', ["%{$buscaParam}%"])
                    ->orWhereRaw('LOWER(planos.descricao) LIKE ?', ["%{$buscaParam}%"])
                    ->orWhereRaw('LOWER(planos.estado) LIKE ?', ["%{$buscaParam}%"]);
            });

            // Determine DB driver early so it can be used for unaccent detection
            try {
                $driver = DB::getDriverName();
            } catch (\Exception $e) {
                $driver = config('database.default');
            }

            // Search related template and cliente fields (and support accent-insensitive search on PG if available)
            $useUnaccent = false;
            if ($driver === 'pgsql' || str_contains($driver, 'pgsql')) {
                try {
                    $has = DB::select("select extname from pg_extension where extname = 'unaccent'");
                    $useUnaccent = !empty($has);
                } catch (\Exception $e) {
                    $useUnaccent = false;
                }
            }

            if ($useUnaccent) {
                $query->orWhereHas('template', function ($q) use ($buscaParam) {
                    $q->whereRaw("unaccent(lower(name)) LIKE unaccent(lower(?))", ["%{$buscaParam}%"]) 
                      ->orWhereRaw("unaccent(lower(COALESCE(description, ''))) LIKE unaccent(lower(?))", ["%{$buscaParam}%"]);
                });

                $query->orWhereHas('cliente', function ($q) use ($buscaParam) {
                    $q->whereRaw("unaccent(lower(nome)) LIKE unaccent(lower(?))", ["%{$buscaParam}%"]) 
                      ->orWhereRaw("unaccent(lower(COALESCE(bi, ''))) LIKE unaccent(lower(?))", ["%{$buscaParam}%"]);
                });
            } else {
                $query->orWhereHas('template', function ($q) use ($buscaParam) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$buscaParam}%"]) 
                      ->orWhereRaw("LOWER(COALESCE(description, '')) LIKE ?", ["%{$buscaParam}%"]);
                });

                $query->orWhereHas('cliente', function ($q) use ($buscaParam) {
                    $q->whereRaw('LOWER(nome) LIKE ?', ["%{$buscaParam}%"]) 
                      ->orWhereRaw("LOWER(COALESCE(bi, '')) LIKE ?", ["%{$buscaParam}%"]);
                });
            }

            // Also try matching numeric fields using LIKE so partial matches
            // (this favors user-friendly searches rather than exact numeric match).
            // Use DB driver-aware CAST because different DBs use different syntax.
            if ($driver === 'pgsql' || str_contains($driver, 'pgsql')) {
                $precoCast = 'CAST(planos.preco AS TEXT)';
                $cicloCast = 'CAST(planos.ciclo AS TEXT)';
            } else {
                // MySQL, MariaDB and others: CAST to CHAR
                $precoCast = 'CAST(planos.preco AS CHAR)';
                $cicloCast = 'CAST(planos.ciclo AS CHAR)';
            }

            $query->orWhereRaw("LOWER({$precoCast}) LIKE ?", ["%{$buscaParam}%"])
                  ->orWhereRaw("LOWER({$cicloCast}) LIKE ?", ["%{$buscaParam}%"]);
        }

          // Ordena por nome do cliente em ordem alfabética para consistência
          // usamos leftJoin para garantir planos sem cliente também apareçam
            // Faz left join com a tabela de clientes e ordena por clientes.nome
            $query->leftJoin('clientes', 'planos.cliente_id', '=', 'clientes.id')
                  ->select('planos.*')
                  ->orderByRaw("LOWER(COALESCE(clientes.nome, ''))");

            $limit = (int) $request->query('limit', 200);
            $limit = max(1, min($limit, 1000));
            $page = (int) $request->query('page', 1);
            $page = max(1, $page);

            if (config('app.debug')) {
                try {
                    \Log::debug('PlanoController@index - SQL query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);
                } catch (\Exception $e) {
                    \Log::warning('PlanoController@index - Falha ao obter SQL para debug', ['error' => $e->getMessage()]);
                }
            }

            $planos = $query->forPage($page, $limit)->get();

        // Attach canonical web URLs to each plano and permission flags so front-end
        // can safely decide which actions to render per-plan.
        $user = auth()->user();
        $templateCounts = PlanTemplate::whereIn('id', $planos->pluck('template_id')->filter()->unique())
            ->withCount(['planos as template_active_clients_count' => function ($q) {
                $q->where('ativo', true);
            }])
            ->get()
            ->keyBy('id');

        $payload = $planos->map(function ($p) use ($user, $templateCounts) {
            $arr = $p->toArray();
            // include template and active clients count for front-end cards
            try {
                if ($p->relationLoaded('template') && $p->template) {
                    $arr['template'] = $p->template->toArray();
                    $countRow = $templateCounts->get($p->template->id);
                    $arr['template_active_clients_count'] = $countRow
                        ? (int) $countRow->template_active_clients_count
                        : 0;
                }
            } catch (\Exception $e) {
                $arr['template_active_clients_count'] = null;
            }
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

            // Compute days remaining (diasRestantes) for front-end cards.
            try {
                $termino = null;
                if (!empty($p->proxima_renovacao)) {
                    $termino = Carbon::parse($p->proxima_renovacao)->startOfDay();
                } elseif (!empty($p->data_ativacao) && !empty($p->ciclo)) {
                    $termino = Carbon::parse($p->data_ativacao)->startOfDay()->addDays((int)$p->ciclo - 1);
                }
                if ($termino) {
                    $arr['diasRestantes'] = Carbon::now()->startOfDay()->diffInDays($termino, false);
                } else {
                    $arr['diasRestantes'] = null;
                }
            } catch (\Exception $e) {
                $arr['diasRestantes'] = null;
            }

            return $arr;
        });

            return response()->json($payload);
        } catch (\Exception $e) {
            \Log::error('PlanoController@index - Erro ao buscar planos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            if (! $request->wantsJson()) {
                return back()->with('error', 'Erro ao buscar planos: ' . $e->getMessage());
            }
            return response()->json(['success' => false, 'message' => 'Erro ao buscar planos', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Web view for planos list. Returns the Blade view with clients for the inline form.
     */
    public function webIndex(Request $request)
    {
        // Load plan templates and compute number of distinct clients per template
        $templates = PlanTemplate::orderBy('name')
            ->withCount(['planos as clients_count' => function ($q) {
                $q->whereNotNull('cliente_id')->select(DB::raw('COUNT(DISTINCT cliente_id)'));
            }])
            ->get();

        return view('planos', compact('templates'));
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
        Cache::forget('sg_active_clients_count');
        Cache::forget('plan_templates_catalog:sales_counts');
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
            'tipo' => 'sometimes|required|in:familiar,institucional,empresarial,site',
        ]);
        $plano = Plano::findOrFail($id);
        $plano->update($validated);
        Cache::forget('sg_active_clients_count'); // invalida cache (ativo pode ter mudado)
        Cache::forget('plan_templates_catalog:sales_counts');
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
                'tipo' => 'required|in:familiar,institucional,empresarial,site',
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
            Cache::forget('sg_active_clients_count');
            Cache::forget('plan_templates_catalog:sales_counts');
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
        $plano = Plano::with(['cliente', 'cliente.planos'])->findOrFail($id);
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
        Cache::forget('sg_active_clients_count');
        Cache::forget('plan_templates_catalog:sales_counts');
        return redirect()->route('planos.index')->with('success', 'Plano removido.');
    }
}
