<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
{
    /**
     * Exibe a página de relatórios gerais multi-aba.
     */
    public function geral()
    {
        $files = \Storage::files('relatorios');
        $historico = [];
        foreach ($files as $file) {
            $basename = basename($file);
            $period = null;
            $lower = strtolower($basename);
            // trate nomes com padrão 'ultimas_XXh' como relatório diário
            if (str_contains($lower, 'diario') || str_contains($lower, 'ultimas_')) $period = 'diario';
            elseif (str_contains($lower, 'semanal')) $period = 'semanal';
            elseif (str_contains($lower, 'mensal')) $period = 'mensal';
            if ($period) {
                $historico[] = [
                    'period' => $period,
                    'name' => $basename,
                    // link direto para o ficheiro histórico específico
                    'url' => route('relatorios.gerais.download', ['period' => $period, 'file' => $basename]),
                    'date' => date('d/m/Y H:i', \Storage::lastModified($file)),
                ];
            }
        }
        // Ordenar por data desc
        usort($historico, fn($a, $b) => strcmp($b['date'], $a['date']));
            // determinar ficheiro mais recente por período
            $latest = ['diario' => null, 'semanal' => null, 'mensal' => null];
            foreach ($historico as $item) {
                if (!$latest[$item['period']]) {
                    $latest[$item['period']] = $item['name'];
                }
            }

            return view('relatorios-gerais', compact('historico', 'latest'));
    }
    /**
     * Download the latest report file for the given period (diario|semanal|mensal).
     */
    public function download(Request $request, string $period, $file = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowed = ['diario', 'semanal', 'mensal'];
        if (!in_array($period, $allowed, true)) {
            abort(404);
        }
        // If a specific filename was requested (from histórico), serve it directly
        if ($file) {
            $basename = basename($file);
            $path = 'relatorios/' . $basename;
            \Log::info('Relatorios: download requested', ['period' => $period, 'requested_file' => $basename, 'path' => $path]);
            if (!Storage::exists($path)) {
                return back()->with('error', 'Arquivo de relatório não encontrado: ' . $basename);
            }
            return Storage::download($path);
        }

        // otherwise find the latest file matching the requested period
        $files = Storage::files('relatorios');
        $matches = array_filter($files, function ($f) use ($period) {
            $lower = strtolower($f);
            if ($period === 'diario') {
                return str_contains($lower, 'diario') || str_contains($lower, 'ultimas_');
            }
            return str_contains($lower, $period);
        });

        if (empty($matches)) {
            \Log::warning('Relatorios: nenhum ficheiro encontrado para período', ['period' => $period]);
            return back()->with('error', 'Nenhum relatório disponível para o período: ' . $period);
        }

        // sort by last modified desc
        usort($matches, function ($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });

        $path = $matches[0];

        if (!Storage::exists($path)) {
            \Log::error('Relatorios: ficheiro seleccionado não existe', ['path' => $path]);
            return back()->with('error', 'Arquivo de relatório não encontrado.');
        }

        \Log::info('Relatorios: servindo download', ['period' => $period, 'selected_path' => $path]);
        return Storage::download($path);
    }
}
