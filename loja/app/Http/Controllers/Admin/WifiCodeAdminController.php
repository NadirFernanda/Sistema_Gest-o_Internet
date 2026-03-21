<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WifiCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * WifiCodeAdminController — Gestão do stock de códigos WiFi (autovenda).
 *
 * Os códigos WiFi são fornecidos pela operadora e importados aqui pelo
 * administrador, separados por plano (diario, semanal, mensal), antes de
 * serem atribuídos automaticamente às ordens de compra dos planos individuais.
 *
 * Fluxo:
 *   1. Admin recebe ficheiro CSV / lista de códigos da operadora.
 *   2. Admin importa os códigos nesta página, escolhendo o plano correspondente.
 *   3. O sistema marca cada código importado como 'available' com o plano indicado.
 *   4. Quando um cliente compra um plano individual, AutovendaOrderService
 *      retira o primeiro código 'available' do plano correcto e entrega ao cliente.
 */
class WifiCodeAdminController extends Controller
{
    /** Planos individuais válidos (devem corresponder aos IDs em config/store_plans.php). */
    private const VALID_PLANS = ['diario', 'semanal', 'mensal'];

    public function index(Request $request)
    {
        $query = WifiCode::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($planFilter = $request->get('plan_id')) {
            $query->where('plan_id', $planFilter);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where('code', 'like', "%{$search}%");
        }

        $codes = $query->paginate(50)->withQueryString();

        // Contagens globais de estado
        $statusCounts = WifiCode::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Contagem de disponíveis por plano
        $planCounts = WifiCode::selectRaw('plan_id, COUNT(*) as total')
            ->where('status', WifiCode::STATUS_AVAILABLE)
            ->whereIn('plan_id', self::VALID_PLANS)
            ->groupBy('plan_id')
            ->pluck('total', 'plan_id');

        return view('admin.wifi_codes.index', [
            'codes'        => $codes,
            'statusCounts' => $statusCounts,
            'planCounts'   => $planCounts,
            'validPlans'   => self::VALID_PLANS,
        ]);
    }

    /**
     * Importar códigos colados como texto (um por linha).
     */
    public function importPaste(Request $request)
    {
        $request->validate([
            'plan_id'    => 'required|string|in:' . implode(',', self::VALID_PLANS),
            'codes_text' => 'required|string|max:500000',
        ]);

        $planId = $request->input('plan_id');
        $lines  = preg_split('/[\r\n,;]+/', $request->input('codes_text'));
        $imported = 0;
        $skipped  = 0;

        DB::transaction(function () use ($lines, $planId, &$imported, &$skipped) {
            foreach ($lines as $line) {
                $code = strtoupper(trim($line));
                if ($code === '') continue;

                // Ignora duplicados sem lançar erro
                if (WifiCode::where('code', $code)->exists()) {
                    $skipped++;
                    continue;
                }

                WifiCode::create([
                    'code'    => $code,
                    'plan_id' => $planId,
                    'status'  => WifiCode::STATUS_AVAILABLE,
                ]);
                $imported++;
            }
        });

        return redirect()
            ->route('admin.wifi_codes.index', ['plan_id' => $planId])
            ->with('success', "Importação concluída ({$planId}): {$imported} código(s) adicionado(s), {$skipped} duplicado(s) ignorado(s).");
    }

    /**
     * Importar códigos via upload de ficheiro CSV/TXT (um código por linha).
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'plan_id'  => 'required|string|in:' . implode(',', self::VALID_PLANS),
            'csv_file' => 'required|file|mimes:csv,txt,text/plain|max:512000',
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
            return back()->withErrors(['delete' => 'Só é possível eliminar códigos com estado "available". Já usados fazem parte do histórico de vendas.']);
        }

        $wifiCode->delete();

        return redirect()
            ->route('admin.wifi_codes.index')
            ->with('success', 'Código eliminado.');
    }

    /**
     * Eliminar múltiplos códigos de uma vez (apenas 'available').
     */
    public function destroyBulk(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $deleted = WifiCode::whereIn('id', $request->input('ids'))
            ->where('status', WifiCode::STATUS_AVAILABLE)
            ->delete();

        $skipped = count($request->input('ids')) - $deleted;

        $msg = "{$deleted} código(s) eliminado(s).";
        if ($skipped > 0) {
            $msg .= " {$skipped} ignorado(s) (não estavam disponíveis).";
        }

        return redirect()
            ->route('admin.wifi_codes.index', $request->only(['plan_id', 'status', 'q']))
            ->with('success', $msg);
    }

    /**
     * Eliminar todos os códigos 'available' de um plano (limpeza rápida).
     */
    public function destroyAllAvailable(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'string', 'in:' . implode(',', self::VALID_PLANS)],
        ]);

        $deleted = WifiCode::where('plan_id', $request->input('plan_id'))
            ->where('status', WifiCode::STATUS_AVAILABLE)
            ->delete();

        return redirect()
            ->route('admin.wifi_codes.index')
            ->with('success', "Todos os {$deleted} código(s) disponíveis do plano «{$request->input('plan_id')}» foram eliminados.");
    }
}
