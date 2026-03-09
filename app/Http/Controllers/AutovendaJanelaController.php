<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Cliente;
use App\Models\Plano;
use App\Models\PlanTemplate;

/**
 * AutovendaJanelaController
 * ═════════════════════════
 * Endpoint consumed by the loja when an admin confirms a family/business plan request.
 * Creates or finds the client in the SG, then extends (or initialises) their plan window.
 *
 * POST /api/janela-autovenda
 *   nome          string  required
 *   email         string  required email
 *   contato       string  required
 *   nif           string  optional  (stored as bi with tipo=NIF when provided)
 *   template_id   int     required  must exist in plan_templates
 *   loja_request_id int   optional  for cross-system audit tracing
 */
class AutovendaJanelaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'             => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'contato'          => 'required|string|max:20',
            'nif'              => 'nullable|string|max:64',
            'template_id'      => 'required|integer|exists:plan_templates,id',
            'loja_request_id'  => 'nullable|integer',
        ]);

        $template = PlanTemplate::findOrFail($validated['template_id']);

        // ── 1. Find or create the client ──────────────────────────────────
        $cliente = Cliente::where('email', $validated['email'])->first();

        if (! $cliente) {
            $bi = $validated['nif'] ?? 'LOJA:' . strtoupper(substr(md5($validated['email']), 0, 8));
            $cliente = Cliente::create([
                'bi'      => $bi,
                'nome'    => $validated['nome'],
                'email'   => $validated['email'],
                'contato' => $validated['contato'],
            ]);
        }

        // ── 2. Find an active plan for this client + template ─────────────
        $plano = Plano::where('cliente_id', $cliente->id)
            ->where('template_id', $template->id)
            ->whereRaw("LOWER(TRIM(COALESCE(estado, ''))) IN ('ativo', 'pendente')")
            ->orderByDesc('data_ativacao')
            ->first();

        $ciclo = intval(preg_replace('/[^0-9]/', '', (string) $template->ciclo));
        if ($ciclo <= 0) $ciclo = 30;

        $hoje = Carbon::today();

        // ── 3a. Extend existing plan (add janela) ─────────────────────────
        if ($plano) {
            try {
                if (! empty($plano->proxima_renovacao)) {
                    $base = Carbon::parse($plano->proxima_renovacao);
                } elseif (! empty($plano->data_ativacao)) {
                    $base = Carbon::parse($plano->data_ativacao)->addDays($ciclo - 1);
                } else {
                    $base = $hoje;
                }
            } catch (\Exception $e) {
                $base = $hoje;
            }

            // Pagamento tardio: janela começa de hoje
            if ($hoje->gt($base)) {
                $base = $hoje;
            }

            $novaRenovacao = $base->copy()->addDays($ciclo)->toDateString();
            $plano->proxima_renovacao = $novaRenovacao;
            $plano->estado = 'Ativo';
            $plano->save();

            \DB::table('compensacoes')->insert([
                'plano_id'         => $plano->id,
                'user_id'          => null,
                'dias_compensados' => $ciclo,
                'anterior'         => $base->toDateString(),
                'novo'             => $novaRenovacao,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return response()->json([
                'success'          => true,
                'action'           => 'janela_extended',
                'cliente_id'       => $cliente->id,
                'plano_id'         => $plano->id,
                'proxima_renovacao'=> $novaRenovacao,
            ]);
        }

        // ── 3b. Create new plan (first time) ─────────────────────────────
        $dataAtivacao   = $hoje->toDateString();
        $proximaRenovacao = $hoje->copy()->addDays($ciclo)->toDateString();

        $plano = Plano::create([
            'nome'              => $template->name,
            'descricao'         => $template->description ?? '',
            'preco'             => (string) number_format($template->preco ?? 0, 2, '.', ''),
            'ciclo'             => $template->ciclo,
            'template_id'       => $template->id,
            'cliente_id'        => $cliente->id,
            'estado'            => 'Ativo',
            'data_ativacao'     => $dataAtivacao,
            'proxima_renovacao' => $proximaRenovacao,
        ]);

        \Log::info('AutovendaJanela: plano criado via loja', [
            'loja_request_id' => $validated['loja_request_id'] ?? null,
            'cliente_id'      => $cliente->id,
            'plano_id'        => $plano->id,
        ]);

        return response()->json([
            'success'           => true,
            'action'            => 'plano_created',
            'cliente_id'        => $cliente->id,
            'plano_id'          => $plano->id,
            'proxima_renovacao' => $proximaRenovacao,
        ], 201);
    }
}
