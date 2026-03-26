<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanTemplate;
use Illuminate\Support\Facades\Cache;

class PlanTemplateController extends Controller
{
    public function __construct()
    {
        // Only users with the corresponding 'planos.*' permissions may create/edit/delete templates.
        $permissionMiddleware = \Spatie\Permission\Middleware\PermissionMiddleware::class;
        $this->middleware($permissionMiddleware . ':planos.create')->only(['create', 'store']);
        $this->middleware($permissionMiddleware . ':planos.edit')->only(['edit', 'update']);
        $this->middleware($permissionMiddleware . ':planos.delete')->only(['destroy']);
    }
    public function index()
    {
        // Load templates with a count of active clientes (planos.ativo = true)
        $templates = PlanTemplate::withCount(['planos as active_clients_count' => function ($q) {
            $q->where('ativo', true);
        }])->orderBy('name')->paginate(100)->withQueryString();

        return view('plan_templates.index', compact('templates'));
    }

    public function create()
    {
        return view('plan_templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preco' => 'nullable|numeric',
            'ciclo' => 'nullable|integer',
            'estado' => 'nullable|string',
            'tipo' => 'nullable|in:familiar,institucional,empresarial,site',
        ]);

        PlanTemplate::create($data);
        Cache::forget('plan_templates:list_json');
        Cache::forget('plan_templates_catalog:all');
        return redirect()->route('plan-templates.index')->with('success', 'Plano criado com sucesso');
    }

    public function edit(PlanTemplate $plan_template)
    {
        return view('plan_templates.edit', ['template' => $plan_template]);
    }

    public function update(Request $request, PlanTemplate $plan_template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'preco' => 'nullable|numeric',
            'ciclo' => 'nullable|integer',
            'estado' => 'nullable|string',
            'tipo' => 'nullable|in:familiar,institucional,empresarial,site',
        ]);

        $plan_template->update($data);
        Cache::forget('plan_templates:list_json');
        Cache::forget('plan_templates_catalog:all');
        return redirect()->route('plan-templates.index')->with('success', 'Plano atualizado com sucesso');
    }

    public function destroy(PlanTemplate $plan_template)
    {
        $plan_template->delete();
        Cache::forget('plan_templates:list_json');
        Cache::forget('plan_templates_catalog:all');
        return redirect()->route('plan-templates.index')->with('success', 'Plano removido com sucesso');
    }

    // JSON endpoint for frontend prefilling
    public function json(PlanTemplate $plan_template)
    {
        return response()->json($plan_template);
    }

    // list all templates as JSON (lightweight)
    public function listJson()
    {
        $list = Cache::remember('plan_templates:list_json', 300, function () {
            return PlanTemplate::withCount(['planos as template_active_clients_count' => function ($q) {
                $q->where('ativo', true);
            }])->orderBy('name')->get(['id','name','preco','ciclo','estado']);
        });

        // ensure numeric counts are present (0 when none)
        $list->transform(function ($t) {
            $arr = $t->toArray();
            if (! isset($arr['template_active_clients_count'])) {
                $arr['template_active_clients_count'] = 0;
            }
            return $arr;
        });

        return response()->json($list);
    }

    /**
     * Debug endpoint: return per-template counts (total planos and active clientes).
     * Only available when APP_DEBUG is true to avoid exposing in production.
     */
    public function debugCounts()
    {
        if (! config('app.debug')) {
            abort(404);
        }

        $templates = PlanTemplate::orderBy('name')->get(['id','name','ciclo']);
        $result = $templates->map(function ($t) {
            $total = $t->planos()->count();
            $active = $t->planos()->where('ativo', true)->count();
            return [
                'id' => $t->id,
                'name' => $t->name,
                'ciclo' => $t->ciclo,
                'total_planos' => $total,
                'active_clients_count' => $active,
            ];
        });

        return response()->json($result);
    }
}
