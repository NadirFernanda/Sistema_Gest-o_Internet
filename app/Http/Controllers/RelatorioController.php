<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
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
            if (str_contains($basename, 'diario')) $period = 'diario';
            elseif (str_contains($basename, 'semanal')) $period = 'semanal';
            elseif (str_contains($basename, 'mensal')) $period = 'mensal';
            if ($period) {
                $historico[] = [
                    'period' => $period,
                    'name' => $basename,
                    'url' => route('relatorios.gerais.download', ['period' => $period]),
                    'date' => date('d/m/Y H:i', \Storage::lastModified($file)),
                ];
            }
        }
        // Ordenar por data desc
        usort($historico, fn($a, $b) => strcmp($b['date'], $a['date']));
        return view('relatorios-gerais', compact('historico'));
    }
{
    /**
     * Download the latest report file for the given period (diario|semanal|mensal).
     */
    public function download(Request $request, string $period)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allowed = ['diario', 'semanal', 'mensal'];
        if (!in_array($period, $allowed, true)) {
            abort(404);
        }

        $files = Storage::files('relatorios');
        // filter by period substring
        $matches = array_filter($files, function ($f) use ($period) {
            return str_contains(strtolower($f), $period);
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
