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
            // trate nomes com padrão esperado (mais específicos)
            if (str_contains($lower, 'relatorio_geral_diario') || str_contains($lower, 'diario') || str_contains($lower, 'ultimas_')) {
                $period = 'diario';
            } elseif (str_contains($lower, 'relatorio_geral_semanal') || str_contains($lower, 'semanal')) {
                $period = 'semanal';
            } elseif (str_contains($lower, 'relatorio_geral_mensal') || str_contains($lower, 'mensal')) {
                $period = 'mensal';
            }
            if ($period) {
                $ts = \Storage::lastModified($file);
                $historico[] = [
                    'period' => $period,
                    'name' => $basename,
                    // link direto para o ficheiro histórico específico
                    'url' => route('relatorios.gerais.download', ['period' => $period, 'file' => $basename]),
                    'date' => date('d/m/Y H:i', $ts),
                    'timestamp' => $ts,
                ];
            }
        }
        // Ordenar por timestamp desc (garante ordem cronológica correta)
        usort($historico, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        return view('relatorios-gerais', compact('historico'));
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
            if (!Storage::exists($path)) {
                return back()->with('error', 'Arquivo de relatório não encontrado: ' . $basename);
            }
            return Storage::download($path);
        }

        // otherwise find the latest file matching the requested period
        $files = Storage::files('relatorios');
        $matches = array_filter($files, function ($f) use ($period) {
            $basename = strtolower(basename($f));
            if ($period === 'diario') {
                // aceitar ficheiros explícitos 'relatorio_geral_diario' ou nomes antigos com 'ultimas_'
                return str_contains($basename, 'relatorio_geral_diario') || str_contains($basename, 'diario') || str_contains($basename, 'ultimas_');
            }
            // procurar por padrão explícito relatorio_geral_<period> primeiro, ou fallback a palavra-chave
            if (str_contains($basename, 'relatorio_geral_' . $period)) return true;
            return str_contains($basename, $period);
        });

        if (empty($matches)) {
            return back()->with('error', 'Nenhum relatório disponível para o período: ' . $period);
        }

        // sort by last modified desc
        usort($matches, function ($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });

        $path = $matches[0];

        if (!Storage::exists($path)) {
            return back()->with('error', 'Arquivo de relatório não encontrado.');
        }

        return Storage::download($path);
    }
}
