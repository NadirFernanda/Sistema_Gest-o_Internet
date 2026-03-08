<?php

namespace App\Http\Controllers;

use App\Models\PlanTemplate;
use Illuminate\Http\Request;

class PlanTemplateCatalogController extends Controller
{
    /**
     * Public endpoint: returns active plan templates for the loja catalog.
     * No authentication required — this is a read-only public catalog.
     */
    public function index(Request $request)
    {
        $templates = PlanTemplate::query()
            ->whereIn('estado', ['ativo', 'active', 'Ativo', 'Active', ''])
            ->orWhereNull('estado')
            ->orderBy('preco')
            ->get(['id', 'name', 'description', 'preco', 'ciclo', 'estado', 'metadata']);

        return response()->json([
            'data' => $templates->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'name'        => $t->name,
                    'description' => $t->description,
                    'preco'       => $t->preco,
                    'ciclo'       => $t->ciclo,
                    'estado'      => $t->estado,
                    'category'    => data_get($t->metadata, 'category', 'familia'),
                ];
            }),
        ]);
    }
}
