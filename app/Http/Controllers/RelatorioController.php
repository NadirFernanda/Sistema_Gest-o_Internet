<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RelatorioController extends Controller
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
