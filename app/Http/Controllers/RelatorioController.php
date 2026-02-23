<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Relatorio;
use Illuminate\Support\Facades\Artisan;

class RelatorioController extends Controller
{
    /**
     * Exibe a página de relatórios gerais multi-aba.
     */
    public function geral()
    {
        // Preferir metadados no BD para mostrar histórico confiável
        $relatorios = Relatorio::orderBy('generated_at', 'desc')->limit(200)->get();
        $historico = [];
        foreach ($relatorios as $r) {
            $historico[] = [
                'period' => $r->period,
                'name' => $r->filename,
                'url' => route('relatorios.gerais.download', ['period' => $r->period, 'file' => $r->filename]),
                'date' => $r->generated_at ? $r->generated_at->format('d/m/Y H:i') : '-',
                'timestamp' => $r->generated_at ? $r->generated_at->getTimestamp() : 0,
            ];
        }

        return view('relatorios-gerais', compact('historico'));
    }

    /**
     * Dispara a geração do relatório agora (invocado pela UI).
     */
    public function gerarAgora(Request $request)
    {
        if (!Auth::check()) abort(403);
        // usar Artisan para executar o comando de forma síncrona
        try {
            Artisan::call('relatorio:geral --period=daily');
            return redirect()->route('relatorios.gerais')->with('status', 'Geração iniciada. Verifique o histórico em alguns instantes.');
        } catch (\Exception $ex) {
            Log::error('Erro gerando relatório via UI: ' . $ex->getMessage());
            return redirect()->route('relatorios.gerais')->with('error', 'Erro ao iniciar geração do relatório: ' . $ex->getMessage());
        }
    }
    /**
     * Download the latest report file for the given period (diario|semanal|mensal).
     */
    public function download(Request $request, string $period, $file = null)
    {
        // Authentication is enforced by route middleware; do not redirect here
        // because a redirect can cause the browser to save an HTML login page
        // with a .xlsx extension. If the route is reached without auth, return
        // a 403/404 instead.

        // Temporary diagnostic log to help identify corrupt downloads.
        Log::info('relatorios.download.called', [
            'url' => $request->fullUrl(),
            'period' => $period,
            'requested_file' => $file,
            'auth' => Auth::check(),
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ]);

        $allowed = ['diario', 'semanal', 'mensal'];
        if (!in_array($period, $allowed, true)) {
            abort(404);
        }
        // If a specific filename was requested (from histórico), serve it directly
        if ($file) {
            $basename = basename($file);
            $path = 'relatorios/' . $basename;
            if (!Storage::exists($path)) {
                Log::warning('relatorios.download.missing_file', ['path' => $path]);
                abort(404, 'Arquivo de relatório não encontrado: ' . $basename);
            }
            Log::info('relatorios.download.serving', ['path' => $path, 'basename' => $basename, 'size' => Storage::size($path)]);
            $full = Storage::path($path);
            $headers = ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            return response()->download($full, $basename, $headers);
        }

        // otherwise find the latest file matching the requested period
        $files = Storage::files('relatorios');

        // Prefer explicit "relatorio_geral_<period>" files (or files containing both
        // "relatorio_geral" and the period). If none, fall back to the older
        // permissive matching (e.g. 'diario' or 'ultimas_'). This prevents returning
        // unrelated reports like 'relatorio_cobrancas_mensal_...' when a
        // multi-sheet general report is expected.
        $preferred = array_filter($files, function ($f) use ($period) {
            $basename = strtolower(basename($f));
            if (str_contains($basename, 'relatorio_geral_' . $period)) return true;
            if (str_contains($basename, 'relatorio_geral') && str_contains($basename, $period)) return true;
            return false;
        });

        if (!empty($preferred)) {
            $matches = $preferred;
        } else {
            // fallback to legacy/permissive matching
            $matches = array_filter($files, function ($f) use ($period) {
                $basename = strtolower(basename($f));
                if ($period === 'diario') {
                    return str_contains($basename, 'relatorio_geral_diario') || str_contains($basename, 'diario') || str_contains($basename, 'ultimas_');
                }
                if (str_contains($basename, 'relatorio_geral_' . $period)) return true;
                return str_contains($basename, $period);
            });
        }

        if (empty($matches)) {
            Log::warning('relatorios.download.no_matches', ['period' => $period]);
            abort(404, 'Nenhum relatório disponível para o período: ' . $period);
        }

        // sort by last modified desc
        usort($matches, function ($a, $b) {
            return Storage::lastModified($b) <=> Storage::lastModified($a);
        });

        $path = $matches[0];

        if (!Storage::exists($path)) {
            Log::warning('relatorios.download.path_missing_after_match', ['path' => $path]);
            abort(404, 'Arquivo de relatório não encontrado.');
        }

        $basename = basename($path);
        Log::info('relatorios.download.serving_final', ['path' => $path, 'basename' => $basename, 'size' => Storage::size($path)]);
        $full = Storage::path($path);
        $headers = ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        return response()->download($full, $basename, $headers);
    }
}
