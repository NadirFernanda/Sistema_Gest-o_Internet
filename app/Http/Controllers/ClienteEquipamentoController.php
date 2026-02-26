<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteEquipamento;
use App\Models\EstoqueEquipamento;
use App\Models\AuditLog;
use App\Jobs\WriteAuditLogJob;
use App\Notifications\ClienteDevolucaoEquipamentoEmail;
use App\Notifications\ClienteDevolucaoEquipamentoWhatsApp;
use Illuminate\Http\Request;

class ClienteEquipamentoController extends Controller
{
    public function create($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $equipamentos = EstoqueEquipamento::all();
        $vinculados = ClienteEquipamento::where('cliente_id', $clienteId)->with('equipamento')->get();
        // load recent audit logs related to these vinculos
        $auditsGrouped = collect([]);
        try {
            $ids = $vinculados->pluck('id')->filter()->values()->all();
            if (!empty($ids)) {
                $audits = AuditLog::whereIn('resource_id', $ids)
                    ->where(function($q){
                        $q->whereRaw("LOWER(COALESCE(resource_type, auditable_type, '')) LIKE '%clienteequipamento%'");
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();
                $auditsGrouped = $audits->groupBy(function($a){ return $a->resource_id ?? $a->auditable_id ?? null; });
            }
        } catch (\Throwable $e) {
            \Log::warning('Falha ao carregar audit logs para cliente_equipamento.create: ' . $e->getMessage());
        }
        // Debug: log the linked equipments shown on the create page to help diagnose stale/incorrect values
        try {
            \Log::info('cliente_equipamento.create.vinculados', [
                'cliente_id' => $clienteId,
                'count' => $vinculados->count(),
                'items' => $vinculados->map(function($v){ return ['id' => $v->id, 'forma_ligacao' => $v->forma_ligacao ?? null, 'updated_at' => $v->updated_at ?? null]; })->toArray(),
            ]);
        } catch (\Throwable $e) {
            // ignore logging errors
        }
        return view('cliente_equipamento.create', compact('cliente', 'equipamentos', 'vinculados', 'auditsGrouped'));
    }

    public function store(Request $request, $clienteId)
    {
        \Log::info('cliente_equipamento.request.store', ['cliente_id' => $clienteId, 'payload' => $request->all()]);
        $request->validate([
            'estoque_equipamento_id' => [
                'required',
                'exists:estoque_equipamentos,id',
                function ($attribute, $value, $fail) use ($clienteId) {
                    if (\App\Models\ClienteEquipamento::where('cliente_id', $clienteId)->where('estoque_equipamento_id', $value)->exists()) {
                        $fail('Este equipamento já foi vinculado a esse cliente');
                    }
                }
            ],
            'quantidade' => 'required|integer|min:1',
            'morada' => 'required|string|max:255',
            'ponto_referencia' => 'required|string|max:255',
            'forma_ligacao' => 'required|string|in:Ponto a Ponto,Multiponto,Fibra,V-Sat',
        ]);
        // check estoque availability
        $estoque = EstoqueEquipamento::find($request->estoque_equipamento_id);
        if (!$estoque) {
            return back()->withErrors(['estoque_equipamento_id' => 'Equipamento inválido.'])->withInput();
        }
        if ($request->quantidade > $estoque->quantidade) {
            return back()->withErrors(['quantidade' => "Quantidade solicitada ({$request->quantidade}) excede o estoque disponível ({$estoque->quantidade})."])->withInput();
        }

        // perform create and decrement stock atomically with row lock to avoid race conditions
        \DB::transaction(function() use ($request, $clienteId) {
            $estoque = EstoqueEquipamento::where('id', $request->estoque_equipamento_id)->lockForUpdate()->first();
            if (!$estoque) {
                throw new \Illuminate\Validation\ValidationException(\Validator::make([], []), response()->json(['estoque_equipamento_id' => 'Equipamento inválido.'], 422));
            }
            if ((int)$request->quantidade > (int)$estoque->quantidade) {
                throw new \Illuminate\Validation\ValidationException(\Validator::make([], []), response()->json(['quantidade' => "Quantidade solicitada ({$request->quantidade}) excede o estoque disponível ({$estoque->quantidade})."], 422));
            }

            ClienteEquipamento::create([
                'cliente_id' => $clienteId,
                'estoque_equipamento_id' => $request->estoque_equipamento_id,
                'quantidade' => $request->quantidade,
                'morada' => $request->morada,
                'ponto_referencia' => $request->ponto_referencia,
                'forma_ligacao' => $request->forma_ligacao,
            ]);

            // decrement stock
            $estoque->quantidade = max(0, $estoque->quantidade - (int)$request->quantidade);
            $estoque->save();
        });

        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Equipamento vinculado ao cliente!');
    }

    public function edit($clienteId, $vinculoId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $equipamentos = EstoqueEquipamento::all();
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);
        return view('cliente_equipamento.edit', compact('cliente', 'equipamentos', 'vinculo'));
    }

    public function update(Request $request, $clienteId, $vinculoId)
    {
        \Log::info('cliente_equipamento.request.update', ['cliente_id' => $clienteId, 'vinculo_id' => $vinculoId, 'payload' => $request->all()]);

        // Log all SQL queries for this request to diagnose why update isn't persisting
        \DB::listen(function ($query) {
            try {
                \Log::info('sql.query', ['sql' => $query->sql, 'bindings' => $query->bindings, 'time' => $query->time]);
            } catch (\Throwable $e) {
                // ignore logging failures
            }
        });
        $request->validate([
            'estoque_equipamento_id' => [
                'required',
                'exists:estoque_equipamentos,id',
                function ($attribute, $value, $fail) use ($clienteId, $vinculoId) {
                    if (\App\Models\ClienteEquipamento::where('cliente_id', $clienteId)
                        ->where('estoque_equipamento_id', $value)
                        ->where('id', '!=', $vinculoId)
                        ->exists()) {
                        $fail('Este equipamento já está vinculado a este cliente.');
                    }
                }
            ],
            'quantidade' => 'required|integer|min:1',
            'morada' => 'required|string|max:255',
            'ponto_referencia' => 'required|string|max:255',
            'forma_ligacao' => 'required|string|in:Ponto a Ponto,Multiponto,Fibra,V-Sat',
        ]);
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);

        // pre-validate availability to provide friendly errors
        $oldQty = (int) ($vinculo->quantidade ?? 0);
        $oldEstoqueId = $vinculo->estoque_equipamento_id;
        $newEstoqueId = (int) $request->estoque_equipamento_id;
        $newQty = (int) $request->quantidade;

        if ($oldEstoqueId === $newEstoqueId) {
            $estoque = EstoqueEquipamento::find($newEstoqueId);
            $delta = $newQty - $oldQty;
            if ($delta > 0 && $delta > ($estoque->quantidade ?? 0)) {
                return back()->withErrors(['quantidade' => "Quantidade solicitada ({$newQty}) excede o estoque disponível ({$estoque->quantidade})."])->withInput();
            }
        } else {
            $newEst = EstoqueEquipamento::find($newEstoqueId);
            if (!$newEst || $newQty > ($newEst->quantidade ?? 0)) {
                return back()->withErrors(['quantidade' => "Quantidade solicitada ({$newQty}) excede o estoque disponível ({$newEst->quantidade})."])->withInput();
            }
        }

        \DB::transaction(function() use ($request, $vinculo) {
            $oldQty = (int) ($vinculo->quantidade ?? 0);
            $oldEstoqueId = $vinculo->estoque_equipamento_id;
            $newEstoqueId = (int) $request->estoque_equipamento_id;
            $newQty = (int) $request->quantidade;

            if ($oldEstoqueId === $newEstoqueId) {
                // same estoque: lock row and check availability for the delta
                $estoque = EstoqueEquipamento::where('id', $newEstoqueId)->lockForUpdate()->firstOrFail();
                $delta = $newQty - $oldQty; // positive means we need more
                if ($delta > 0 && $delta > $estoque->quantidade) {
                    throw new \Illuminate\Validation\ValidationException(\Validator::make([], []), response()->json(['quantidade' => "Quantidade solicitada ({$newQty}) excede o estoque disponível ({$estoque->quantidade})."], 422));
                }
                $estoque->quantidade = max(0, $estoque->quantidade - $delta);
                $estoque->save();
            } else {
                // different estoque: lock both rows (order by id to avoid deadlocks), restore old, deduct new
                $firstId = min($oldEstoqueId, $newEstoqueId);
                $secondId = max($oldEstoqueId, $newEstoqueId);
                $rows = EstoqueEquipamento::whereIn('id', [$firstId, $secondId])->lockForUpdate()->get()->keyBy('id');

                $oldEst = $rows->get($oldEstoqueId);
                $newEst = $rows->get($newEstoqueId);

                if ($oldEst) {
                    $oldEst->quantidade = $oldEst->quantidade + $oldQty;
                    $oldEst->save();
                }

                if (!$newEst || $newQty > $newEst->quantidade) {
                    throw new \Illuminate\Validation\ValidationException(\Validator::make([], []), response()->json(['quantidade' => "Quantidade solicitada ({$newQty}) excede o estoque disponível ({" . ($newEst->quantidade ?? '0') . "})."], 422));
                }
                $newEst->quantidade = max(0, $newEst->quantidade - $newQty);
                $newEst->save();
            }

            \Log::info('cliente_equipamento.update.before', ['id' => $vinculo->id, 'forma_ligacao_before' => $vinculo->forma_ligacao, 'request_forma' => $request->forma_ligacao]);

            // Apply incoming data using fill() so we can inspect getDirty() before saving
            $vinculo->fill([
                'estoque_equipamento_id' => $newEstoqueId,
                'quantidade' => $newQty,
                'morada' => $request->morada,
                'ponto_referencia' => $request->ponto_referencia,
                'forma_ligacao' => $request->forma_ligacao,
            ]);

            \Log::info('cliente_equipamento.update.after_fill', [
                'id' => $vinculo->id,
                'attributes_after_fill' => $vinculo->getAttributes(),
                'getDirty_after_fill' => $vinculo->getDirty(),
            ]);

            $saved = $vinculo->save();

            \Log::info('cliente_equipamento.update.result', [
                'id' => $vinculo->id,
                'save_return' => $saved,
                'getChanges' => $vinculo->getChanges(),
                'getDirty' => $vinculo->getDirty(),
                'attributes' => $vinculo->getAttributes(),
            ]);

            // Refresh from DB to confirm persisted state
            try {
                $vinculo->refresh();
            } catch (\Exception $e) {
                \Log::warning('cliente_equipamento.update.refresh_failed', ['id' => $vinculo->id, 'error' => $e->getMessage()]);
            }

            \Log::info('cliente_equipamento.update.after', ['id' => $vinculo->id, 'forma_ligacao_after' => $vinculo->forma_ligacao, 'attributes_after_refresh' => $vinculo->getAttributes()]);
        });

        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Vínculo atualizado com sucesso!');
    }

    public function destroy($clienteId, $vinculoId)
    {
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);

        \DB::transaction(function() use ($vinculo) {
            // restore stock with row lock
            $estoque = EstoqueEquipamento::where('id', $vinculo->estoque_equipamento_id)->lockForUpdate()->first();
            if ($estoque) {
                $estoque->quantidade = $estoque->quantidade + (int)($vinculo->quantidade ?? 0);
                $estoque->save();
            }
            $vinculo->delete();
        });

        return redirect()->route('cliente_equipamento.create', $clienteId)->with('success', 'Vínculo removido com sucesso!');
    }

    public function solicitarDevolucao(Request $request, $clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $prazoDias = (int) ($request->input('prazo_dias', 7));
        $motivo = $request->input('motivo', 'Solicitação manual pelo admin');

        $vinculos = ClienteEquipamento::where('cliente_id', $clienteId)
            ->where('status', ClienteEquipamento::STATUS_EMPRESTADO)
            ->get();

        foreach ($vinculos as $v) {
            $before = $v->toArray();
            $v->update([
                'status' => ClienteEquipamento::STATUS_DEVOLUCAO_SOLICITADA,
                'devolucao_solicitada_at' => now(),
                'devolucao_prazo' => now()->addDays($prazoDias)->toDateString(),
                'motivo_requisicao' => $motivo,
            ]);

            WriteAuditLogJob::dispatch([
                'resource_type' => 'ClienteEquipamento',
                'resource_id' => $v->id,
                'action' => 'solicitacao_devolucao_manual',
                'payload_before' => $before,
                'payload_after' => $v->toArray(),
                'actor_name' => auth()->user()->name ?? 'admin',
                'module' => 'cliente_equipamento',
            ]);
        }

        // Notify client
        try {
            $cliente->notify(new ClienteDevolucaoEquipamentoEmail($cliente));
            $cliente->notify(new ClienteDevolucaoEquipamentoWhatsApp($cliente));
        } catch (\Throwable $e) {
            \Log::error('Erro ao notificar cliente na solicitacao manual de devolucao: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Solicitação de devolução criada e cliente notificado.');
    }

    public function registrarDevolucao(Request $request, $clienteId, $vinculoId)
    {
        $vinculo = ClienteEquipamento::findOrFail($vinculoId);

        \DB::transaction(function() use ($vinculo) {
            // lock estoque row and restore quantity
            $estoque = EstoqueEquipamento::where('id', $vinculo->estoque_equipamento_id)->lockForUpdate()->first();
            $before = $vinculo->toArray();
            if ($estoque) {
                $estoque->quantidade = ($estoque->quantidade ?? 0) + (int)($vinculo->quantidade ?? 0);
                $estoque->save();
            }

            $vinculo->update([
                'status' => ClienteEquipamento::STATUS_DEVOLVIDO,
            ]);

            WriteAuditLogJob::dispatch([
                'resource_type' => 'ClienteEquipamento',
                'resource_id' => $vinculo->id,
                'action' => 'registrar_devolucao',
                'payload_before' => $before,
                'payload_after' => $vinculo->toArray(),
                'actor_name' => auth()->user()->name ?? 'admin',
                'module' => 'cliente_equipamento',
            ]);
        });

        return redirect()->back()->with('success', 'Devolução registrada e estoque atualizado.');
    }
}
        

