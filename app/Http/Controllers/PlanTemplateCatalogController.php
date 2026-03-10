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
        $query = PlanTemplate::query()
            ->where(function ($q) {
                $q->whereIn('estado', ['ativo', 'active', 'Ativo', 'Active', ''])
                  ->orWhereNull('estado');
            })
            ->orderBy('preco');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $templates = $query->get(['id', 'name', 'description', 'preco', 'ciclo', 'estado', 'tipo', 'metadata']);

        return response()->json([
            'data' => $templates->map(function ($t) {
                return [
                    'id'          => $t->id,
                    'name'        => $t->name,
                    'description' => $t->description,
                    'preco'       => $t->preco,
                    'ciclo'       => $t->ciclo,
                    'tipo'        => $t->tipo,
                    'category'    => data_get($t->metadata, 'category', 'familia'),
                    'features'    => array_values(array_filter((array) data_get($t->metadata, 'features', []), 'strlen')),
                ];
            }),
        ]);
    }
}
