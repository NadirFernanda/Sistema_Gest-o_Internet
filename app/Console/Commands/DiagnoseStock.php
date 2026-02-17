<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EstoqueEquipamento;
use App\Models\ClienteEquipamento;

class DiagnoseStock extends Command
{
    protected $signature = 'diagnose:stock {--fix : Consolida vínculos duplicados (mesmo cliente+mesmo equipamento) somando quantidades} {--rebuild : Reconcilia quantidades do estoque de forma segura a partir dos vínculos}';
    protected $description = 'Diagnostica inconsistências no estoque e vínculos de equipamentos';

    public function handle()
    {
        $this->info('Iniciando diagnóstico do estoque...');

        // 1) Estoques com quantidade negativa
        $negativos = EstoqueEquipamento::where('quantidade', '<', 0)->get();
        if ($negativos->count()) {
            $this->error('Estoques com quantidade negativa:');
            $this->table(
                ['id', 'nome', 'modelo', 'quantidade'],
                $negativos->map(function($e){ return [$e->id, $e->nome, $e->modelo, $e->quantidade]; })->toArray()
            );
        } else {
            $this->info('Nenhum estoque com quantidade negativa.');
        }

        // 2) ClienteEquipamento apontando para estoque inexistente
        $dangling = ClienteEquipamento::doesntHave('equipamento')->with('cliente')->get();
        if ($dangling->count()) {
            $this->error('Vínculos apontando para equipamento inexistente:');
            $this->table(
                ['vinculo_id','cliente_id','cliente_nome','estoque_equipamento_id','quantidade'],
                $dangling->map(function($v){ return [$v->id, $v->cliente_id, optional($v->cliente)->nome, $v->estoque_equipamento_id, $v->quantidade]; })->toArray()
            );
        } else {
            $this->info('Nenhum vínculo pendente com equipamento inexistente.');
        }

        // 3) Vínculos com quantidade inválida (<=0)
        $invalidQty = ClienteEquipamento::where('quantidade', '<=', 0)->with('cliente','equipamento')->get();
        if ($invalidQty->count()) {
            $this->error('Vínculos com quantidade inválida (<=0):');
            $this->table(
                ['vinculo_id','cliente','equipamento','estoque_equipamento_id','quantidade'],
                $invalidQty->map(function($v){ return [$v->id, optional($v->cliente)->nome, optional($v->equipamento)->nome, $v->estoque_equipamento_id, $v->quantidade]; })->toArray()
            );
        } else {
            $this->info('Nenhum vínculo com quantidade inválida.');
        }

        // 4) Duplicados (mesmo cliente + mesmo estoque mais de 1)
        $dupQuery = ClienteEquipamento::selectRaw('cliente_id, estoque_equipamento_id, COUNT(*) as cnt, SUM(quantidade) as total_linked')
            ->groupBy('cliente_id','estoque_equipamento_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        if ($dupQuery->count()) {
            $this->error('Vínculos duplicados encontrados (mesmo cliente + mesmo equipamento):');
            $rows = [];
            foreach ($dupQuery as $row) {
                $rows[] = [$row->cliente_id, $row->estoque_equipamento_id, $row->cnt, $row->total_linked];
            }
            $this->table(['cliente_id','estoque_equipamento_id','count','total_linked'],$rows);

            if ($this->option('fix')) {
                $this->info('Iniciando consolidação automática dos vínculos duplicados...');
                foreach ($dupQuery as $dup) {
                    \DB::transaction(function() use ($dup) {
                        $records = ClienteEquipamento::where('cliente_id', $dup->cliente_id)
                            ->where('estoque_equipamento_id', $dup->estoque_equipamento_id)
                            ->orderBy('id','asc')
                            ->get();

                        if ($records->count() <= 1) return;

                        $keep = $records->first();
                        $sum = $records->sum('quantidade');

                        // update the first record to the summed quantity
                        $keep->quantidade = $sum;
                        $keep->save();

                        // delete the rest
                        $toDelete = $records->slice(1);
                        foreach ($toDelete as $r) { $r->delete(); }
                    });
                }
                $this->info('Consolidação concluída.');

                // re-run summary of duplicates after fix
                $dupQuery = ClienteEquipamento::selectRaw('cliente_id, estoque_equipamento_id, COUNT(*) as cnt, SUM(quantidade) as total_linked')
                    ->groupBy('cliente_id','estoque_equipamento_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->get();
                if (!$dupQuery->count()) {
                    $this->info('Nenhum vínculo duplicado restante após consolidação.');
                } else {
                    $this->error('Ainda existem duplicados após tentativa de consolidação:');
                    $rows = [];
                    foreach ($dupQuery as $row) { $rows[] = [$row->cliente_id, $row->estoque_equipamento_id, $row->cnt, $row->total_linked]; }
                    $this->table(['cliente_id','estoque_equipamento_id','count','total_linked'],$rows);
                }
            }
        } else {
            $this->info('Nenhum vínculo duplicado encontrado.');
        }

        // 5) Resumo por estoque: estoque atual, total vinculado
        $this->info('Resumo por equipamento (estoque atual / total vinculado):');
        $stocks = EstoqueEquipamento::all();

        $rows = [];
        foreach ($stocks as $s) {
            $totalLinked = (int) ClienteEquipamento::where('estoque_equipamento_id', $s->id)->sum('quantidade');
            $current = (int) $s->quantidade;
            $rows[] = [$s->id, $s->nome, $s->modelo, $current, $totalLinked, ($current + $totalLinked)];
        }
        usort($rows, function($a,$b){ return $b[4] <=> $a[4]; });
        $this->table(['id','nome','modelo','quantidade_atual','total_vinculado','soma_total'],$rows);

        $this->info('Diagnóstico concluído.');

        // if rebuild option is provided, perform safe reconciliation
        if ($this->option('rebuild')) {
            $this->info('Iniciando reconstrução segura das quantidades de estoque (opção --rebuild) ...');

            $updated = [];
            foreach (EstoqueEquipamento::all() as $s) {
                \DB::transaction(function() use ($s, &$updated) {
                    $row = EstoqueEquipamento::lockForUpdate()->find($s->id);
                    if (!$row) return;

                    $totalLinked = (int) ClienteEquipamento::where('estoque_equipamento_id', $row->id)->sum('quantidade');

                    // Reconciliação segura: nova quantidade = max(0, quantidade_atual - total_vinculado)
                    $newQty = max(0, (int) $row->quantidade - $totalLinked);

                    if ($row->quantidade !== $newQty) {
                        $old = $row->quantidade;
                        $row->quantidade = $newQty;
                        $row->save();
                        $updated[] = [ $row->id, $row->nome, $old, $newQty, $totalLinked ];
                    }
                });
            }

            if (count($updated)) {
                $this->info('Foram atualizados os seguintes estoques:');
                $this->table(['id','nome','quantidade_antiga','quantidade_nova','total_vinculado'],$updated);
            } else {
                $this->info('Nenhuma alteração necessária nas quantidades de estoque.');
            }

            $this->info('Reconstrução concluída.');
        }

        return 0;
    }
}
