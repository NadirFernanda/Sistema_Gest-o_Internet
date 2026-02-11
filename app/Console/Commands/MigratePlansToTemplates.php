<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Plano;
use App\Models\PlanTemplate;

class MigratePlansToTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:plans-to-templates {--dry-run : Do not write changes, only show what would be done}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create PlanTemplate records from existing Planos and link planos.template_id accordingly';

    public function handle()
    {
        $dry = $this->option('dry-run');

        $this->info('Scanning planos without template_id...');

        $createdTemplates = 0;
        $updatedPlanos = 0;

        // Load existing templates into map to avoid duplicates
        $templateMap = [];
        PlanTemplate::all()->each(function ($t) use (&$templateMap) {
            $key = $this->makeKey($t->name, $t->preco, $t->ciclo, $t->description ?? '', $t->estado ?? '');
            $templateMap[$key] = $t->id;
        });

        Plano::whereNull('template_id')->chunkById(100, function ($planos) use (&$templateMap, &$createdTemplates, &$updatedPlanos, $dry) {
            DB::transaction(function () use ($planos, &$templateMap, &$createdTemplates, &$updatedPlanos, $dry) {
                foreach ($planos as $plano) {
                    $key = $this->makeKey($plano->nome ?? $plano->name ?? '', $plano->preco, $plano->ciclo, $plano->descricao ?? $plano->description ?? '', $plano->estado ?? '');

                    if (!isset($templateMap[$key])) {
                        if ($dry) {
                            $this->line("[dry] Would create template for key: {$key}");
                        } else {
                            $tpl = PlanTemplate::create([
                                'name' => $plano->nome ?? $plano->name ?? 'Plano',
                                'description' => $plano->descricao ?? $plano->description ?? null,
                                'preco' => $plano->preco,
                                'ciclo' => $plano->ciclo,
                                'estado' => $plano->estado ?? 'Ativo',
                                'metadata' => null,
                            ]);
                            $templateMap[$key] = $tpl->id;
                            $createdTemplates++;
                        }
                    }

                    $templateId = $templateMap[$key] ?? null;
                    if ($templateId) {
                        if ($dry) {
                            $this->line("[dry] Would set plano id {$plano->id} -> template_id {$templateId}");
                        } else {
                            $plano->template_id = $templateId;
                            $plano->save();
                            $updatedPlanos++;
                        }
                    }
                }
            });
        });

        $this->info("Done. Templates created: {$createdTemplates}. Planos updated: {$updatedPlanos}.");

        return 0;
    }

    private function makeKey($name, $preco, $ciclo, $description, $estado)
    {
        return md5(trim((string)$name) . '|' . (string)$preco . '|' . (string)$ciclo . '|' . trim((string)$description) . '|' . (string)$estado);
    }
}
