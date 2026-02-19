<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanTemplate;

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
        }])->orderBy('name')->get();

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
        ]);

        PlanTemplate::create($data);
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
        ]);

        $plan_template->update($data);
        return redirect()->route('plan-templates.index')->with('success', 'Plano atualizado com sucesso');
    }

    public function destroy($plan_template)
    {
        if (!$plan_template || $plan_template === 'null' || $plan_template === null) {
            return redirect()->route('plan-templates.index')->with('error', 'Plano inválido');
        }
        $tpl = PlanTemplate::find($plan_template);
        if (!$tpl) {
            return redirect()->route('plan-templates.index')->with('error', 'Plano não encontrado');
        }
        $tpl->delete();
        return redirect()->route('plan-templates.index')->with('success', 'Plano removido com sucesso');
    }

    // JSON endpoint for frontend prefilling
    public function json($plan_template)
    {
        if (!$plan_template || $plan_template === 'null' || $plan_template === null) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }
        $tpl = PlanTemplate::find($plan_template);
        if (!$tpl) {
            return response()->json(['error' => 'Plano não encontrado'], 404);
        }
        return response()->json($tpl);
    }

    // list all templates as JSON (lightweight)
    public function listJson()
    {
        $list = PlanTemplate::orderBy('name')->get(['id','name','preco','ciclo','estado']);
        return response()->json($list);
    }
}
