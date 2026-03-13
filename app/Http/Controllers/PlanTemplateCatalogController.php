<?php

namespace App\Http\Controllers;

use App\Models\PlanTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanTemplateCatalogController extends Controller
{
    /**
     * Public endpoint: returns active plan templates for the loja catalog.
     * No authentication required — this is a read-only public catalog.
     *
     * Each template includes `is_popular: true` for the most-sold template
     * in each category (based on the count of active/pending plans in planos table).
     */
    public function index(Request $request)
    {
        $query = PlanTemplate::query()
            ->where(function ($q) {
                // Aceita as variantes ortográficas pt-BR ('Ativo') e pt-PT ('Activo'),
                // maiúsculas e minúsculas, string vazia, e NULL.
                $q->whereRaw("LOWER(estado) IN ('ativo','activo','active','')")
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

        // Count active/pending plans per template to determine the most popular in each category.
        // We group by template_id and pick the highest count per (tipo/category) bucket.
        $salesCounts = [];
        try {
            $rows = DB::table('planos')
                ->select('template_id', DB::raw('COUNT(*) as cnt'))
                ->whereNotNull('template_id')
                ->groupBy('template_id')
                ->get();
            foreach ($rows as $row) {
                $salesCounts[$row->template_id] = (int) $row->cnt;
            }
        } catch (\Throwable $e) {
            // planos table may not exist yet in some environments — degrade gracefully
        }

        // Bucket templates by their effective category and find the highest-sold id per bucket.
        $popularByBucket = [];
        foreach ($templates as $t) {
            $bucket  = $t->tipo ?: data_get($t->metadata, 'category', 'familia');
            $bucket  = strtolower(trim($bucket));
            $cnt     = $salesCounts[$t->id] ?? 0;
            if (! isset($popularByBucket[$bucket]) || $cnt > $popularByBucket[$bucket]['cnt']) {
                $popularByBucket[$bucket] = ['id' => $t->id, 'cnt' => $cnt];
            }
        }
        $popularIds = array_column($popularByBucket, 'id');

        return response()->json([
            'data' => $templates->map(function ($t) use ($popularIds) {
                $bucket = strtolower(trim($t->tipo ?: data_get($t->metadata, 'category', 'familia')));
                return [
                    'id'          => $t->id,
                    'name'        => $t->name,
                    'description' => $t->description,
                    'preco'       => $t->preco,
                    'ciclo'       => $t->ciclo,
                    'tipo'        => $t->tipo,
                    'category'    => data_get($t->metadata, 'category', 'familia'),
                    'features'    => array_values(array_filter((array) data_get($t->metadata, 'features', []), 'strlen')),
                    'is_popular'  => in_array($t->id, $popularIds),
                ];
            }),
        ]);
    }
}
