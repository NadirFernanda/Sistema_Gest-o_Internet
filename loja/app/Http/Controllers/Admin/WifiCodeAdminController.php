<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * WifiCodeAdminController — Gestão do stock de códigos WiFi (autovenda).
 *
 * Os códigos WiFi são fornecidos pela operadora (LuandaWiFi) e importados
 * aqui pelo administrador antes de serem atribuídos automaticamente às
 * ordens de compra dos planos individuais.
 *
 * Fluxo:
 *   1. Admin recebe ficheiro CSV / lista de códigos da operadora.
 *   2. Admin importa os códigos nesta página (colar ou upload CSV).
 *   3. O sistema marca cada código importado como 'available'.
 *   4. Quando um cliente compra um plano individual, AutovendaOrderService
 *      retira o primeiro código 'available' do stock e o entrega ao cliente.
 */
class WifiCodeAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = WifiCode::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where('code', 'like', "%{$search}%");
        }

        $codes = $query->paginate(50)->withQueryString();

        $statusCounts = WifiCode::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.wifi_codes.index', [
            'codes'        => $codes,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Importar códigos colados como texto (um por linha).
     */
    public function importPaste(Request $request)
    {
        $request->validate([
            'codes_text' => 'required|string|max:500000',
        ]);

        $lines = preg_split('/[\r\n,;]+/', $request->input('codes_text'));
        $imported = 0;
        $skipped  = 0;

        DB::transaction(function () use ($lines, &$imported, &$skipped) {
            foreach ($lines as $line) {
                $code = strtoupper(trim($line));
                if ($code === '') continue;

                // Ignora duplicados sem lançar erro
                $exists = WifiCode::where('code', $code)->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                WifiCode::create([
                    'code'   => $code,
                    'status' => WifiCode::STATUS_AVAILABLE,
                ]);
                $imported++;
            }
        });

        return redirect()
            ->route('admin.wifi_codes.index')
            ->with('success', "Importação concluída: {$imported} código(s) adicionado(s), {$skipped} duplicado(s) ignorado(s).");
    }

    /**
     * Importar códigos via upload de ficheiro CSV/TXT (um código por linha).
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,text/plain|max:2048',
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());
        $request->merge(['codes_text' => $content]);

        return $this->importPaste($request);
    }

    /**
     * Apagar um código (apenas se estiver 'available' — nunca apagar usados).
     */
    public function destroy(WifiCode $wifiCode)
    {
        if ($wifiCode->status !== WifiCode::STATUS_AVAILABLE) {
            return back()->withErrors(['delete' => 'Só é possível eliminar códigos com estado "available".(Já usados fazem parte do histórico de vendas.)']);
        }

        $wifiCode->delete();

        return redirect()
            ->route('admin.wifi_codes.index')
            ->with('success', 'Código eliminado.');
    }
}
