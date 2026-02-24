<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ClienteEquipamento;
use App\Models\EstoqueEquipamento;
use Illuminate\Http\Request;

class ClienteEquipamentoController extends Controller
{
    public function create($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $equipamentos = EstoqueEquipamento::all();
        $vinculados = ClienteEquipamento::where('cliente_id', $clienteId)->with('equipamento')->get();
        return view('cliente_equipamento.create', compact('cliente', 'equipamentos', 'vinculados'));
    }

    public function store(Request $request, $clienteId)
    {
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

            $vinculo->update([
                'estoque_equipamento_id' => $newEstoqueId,
                'quantidade' => $newQty,
                'morada' => $request->morada,
                'ponto_referencia' => $request->ponto_referencia,
                'forma_ligacao' => $request->forma_ligacao,
            ]);

            \Log::info('cliente_equipamento.update.after', ['id' => $vinculo->id, 'forma_ligacao_after' => $vinculo->forma_ligacao]);
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
}
        

