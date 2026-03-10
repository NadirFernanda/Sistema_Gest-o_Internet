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
 * Endpoints consumed by the loja for client lookup and plan window management.
 *
 * GET  /api/cliente-lookup?phone=...  → find client by phone, return name/email/nif
 * POST /api/janela-autovenda          → find/create client, extend/create plan window
 *
 * POST /api/janela-autovenda
 *   nome            string  required
 *   email           string  optional  (clients from Angola often have none)
 *   contato         string  required
 *   nif             string  optional  (stored as bi with tipo=NIF when provided)
 *   template_id     int     required  must exist in plan_templates
 *   loja_request_id int     optional  for cross-system audit tracing
 *   estado          string  optional  'Ativo' (default) or 'Pendente' (awaiting payment)
 *
 * POST /api/janela-activar/{plano_id}
 *   Marks an existing 'Pendente' plan as 'Ativo'.
 *   loja_request_id int   optional  for audit tracing
 */
class AutovendaJanelaController extends Controller
{
    /**
     * Lookup a client by phone number — used by the loja form to pre-fill fields.
     * Returns name/email/nif from the most recently active client record, if found.
     *
     * GET /api/cliente-lookup?phone=9XXXXXXXX
     */
    public function lookup(Request $request)
    {
        $phone = preg_replace('/[\s\-\.()]/', '', $request->query('phone', ''));

        if (mb_strlen($phone) < 7) {
            return response()->json(['found' => false], 400);
        }

        $cliente = Cliente::where('contato', 'like', '%' . $phone . '%')
            ->orderByDesc('created_at')
            ->first(['nome', 'email', 'bi']);

        if (! $cliente) {
            return response()->json(['found' => false]);
        }

        // Only return bi if it looks like a real NIF (not a LOJA: hash placeholder)
        $nif = null;
        if (! empty($cliente->bi) && ! str_starts_with($cliente->bi, 'LOJA:')) {
            $nif = $cliente->bi;
        }

        return response()->json([
            'found' => true,
            'name'  => $cliente->nome,
            'email' => $cliente->email ?? '',
            'nif'   => $nif ?? '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'             => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'contato'          => 'required|string|max:20',
            'nif'              => 'nullable|string|max:64',
            'template_id'      => 'required|integer|exists:plan_templates,id',
            'loja_request_id'  => 'nullable|integer',
            'estado'           => 'nullable|string|in:Ativo,Pendente',
        ]);

        // Allow the caller to create the plan as 'Pendente' (awaiting payment confirmation).
        // The default remains 'Ativo' to preserve backward compatibility with the webhook flow.
        $estadoInicial = $validated['estado'] ?? 'Ativo';

        $template = PlanTemplate::findOrFail($validated['template_id']);

        // ── 1. Find or create the client ──────────────────────────────────
        // Search by email first (most reliable), fallback to phone if no email provided.
        $cliente = null;
        if (! empty($validated['email'])) {
            $cliente = Cliente::where('email', $validated['email'])->first();
        }
        if (! $cliente) {
            $cliente = Cliente::where('contato', $validated['contato'])->first();
        }

        if (! $cliente) {
            $bi = $validated['nif'] ?? ('LOJA:' . strtoupper(substr(md5($validated['contato']), 0, 8)));
            $cliente = Cliente::create([
                'bi'      => $bi,
                'nome'    => $validated['nome'],
                'email'   => $validated['email'] ?? null,
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
            $plano->estado = $estadoInicial;
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
            'estado'            => $estadoInicial,
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

    /**
     * Activate a plan that was previously created as 'Pendente'.
     * Called by the loja payment webhook after payment is confirmed.
     *
     * POST /api/janela-activar/{plano_id}
     *   loja_request_id  int  optional  for audit tracing
     */
    public function activar(Request $request, int $planoId)
    {
        $plano = Plano::find($planoId);

        if (! $plano) {
            return response()->json(['success' => false, 'error' => 'plano_not_found'], 404);
        }

        // Only transition from Pendente (or any non-active state) to Ativo.
        // If already Ativo, treat as success (idempotent).
        if (strtolower(trim($plano->estado ?? '')) !== 'ativo') {
            $plano->estado = 'Ativo';
            $plano->save();

            \Log::info('AutovendaJanela: plano activado após pagamento', [
                'plano_id'        => $plano->id,
                'loja_request_id' => $request->input('loja_request_id'),
            ]);
        }

        return response()->json([
            'success'           => true,
            'action'            => 'plano_activated',
            'plano_id'          => $plano->id,
            'proxima_renovacao' => $plano->proxima_renovacao,
        ]);
    }
}
