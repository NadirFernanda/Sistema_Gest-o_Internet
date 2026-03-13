<?php

namespace App\Http\Controllers;

use App\Models\ResellerApplication;
use App\Models\ResellerPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResellerPanelController extends Controller
{
    public function index(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');

        $application = null;
        $purchases   = collect();
        $totals      = ['total_gross' => 0, 'total_net' => 0, 'codes_total' => 0];
        $monthlySpend     = 0;
        $estimatedProfit  = 0;

        if ($resellerId) {
            $application = ResellerApplication::find($resellerId);

            if ($application) {
                $purchases = ResellerPurchase::where('reseller_application_id', $application->id)
                    ->orderByDesc('id')
                    ->paginate(10);

                foreach ($purchases as $purchase) {
                    $totals['total_gross'] += $purchase->gross_amount_aoa;
                    $totals['total_net']   += $purchase->net_amount_aoa;
                    $totals['codes_total'] += $purchase->codes_count;
                    // Lucro = diferença entre valor bruto e líquido (desconto obtido)
                    $estimatedProfit += ($purchase->gross_amount_aoa - $purchase->net_amount_aoa);
                }

                $monthlySpend = $application->monthlySpendings();
            } else {
                $request->session()->forget('reseller_id');
            }
        }

        return view('reseller.panel', [
            'application'     => $application,
            'purchases'       => $purchases,
            'totals'          => $totals,
            'monthlySpend'    => $monthlySpend,
            'estimatedProfit' => $estimatedProfit,
            'minPurchase'     => (int) config('reseller.min_purchase_aoa', 10000),
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $application = ResellerApplication::where('email', $data['email'])
            ->where('status', ResellerApplication::STATUS_APPROVED)
            ->first();

        if (!$application) {
            return redirect()->route('reseller.panel')
                ->with('status', 'Nenhum revendedor aprovado encontrado para este e-mail.');
        }

        $request->session()->put('reseller_id', $application->id);

        return redirect()->route('reseller.panel');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('reseller_id');
        return redirect()->route('reseller.panel');
    }

    public function storePurchase(Request $request)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId) return redirect()->route('reseller.panel');

        $application = ResellerApplication::findOrFail($resellerId);

        $minPurchase = (int) config('reseller.min_purchase_aoa', 10000);
        $unitPrice   = (int) config('reseller.code_unit_price_aoa', 1000);

        $data = $request->validate([
            'gross_amount_aoa' => [
                'required', 'integer',
                'min:' . $minPurchase,
            ],
        ]);

        $gross           = (int) $data['gross_amount_aoa'];
        $discountPercent = $application->discountPercentFor($gross);
        $net             = (int) round($gross * (100 - $discountPercent) / 100);
        $codesCount      = max(1, (int) floor($gross / $unitPrice));

        $codes    = $this->generateCodes($codesCount);
        $purchase = new ResellerPurchase();
        $purchase->reseller_application_id = $application->id;
        $purchase->gross_amount_aoa  = $gross;
        $purchase->discount_percent  = $discountPercent;
        $purchase->net_amount_aoa    = $net;
        $purchase->codes_count       = $codesCount;

        $path = 'resellers/' . $application->id . '/purchase_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.csv';
        Storage::disk('local')->put($path, $this->buildCsv($codes));
        $purchase->csv_path = $path;
        $purchase->meta     = ['generated_codes_preview' => array_slice($codes, 0, 3)];
        $purchase->save();

        return redirect()->route('reseller.panel')
            ->with('status', "Compra de {$gross} Kz registada com {$discountPercent}% de desconto ({$codesCount} códigos). CSV disponível para download.");
    }

    public function downloadCsv(Request $request, ResellerPurchase $purchase)
    {
        $resellerId = $request->session()->get('reseller_id');
        if (!$resellerId || $purchase->reseller_application_id !== $resellerId) {
            abort(403);
        }

        if (!Storage::disk('local')->exists($purchase->csv_path)) {
            abort(404, 'CSV não encontrado.');
        }

        return response()->streamDownload(function () use ($purchase) {
            echo Storage::disk('local')->get($purchase->csv_path);
        }, 'codigos_revenda_' . $purchase->id . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function generateCodes(int $count): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        }
        return $codes;
    }

    private function buildCsv(array $codes): string
    {
        $lines = ['codigo'];
        foreach ($codes as $code) {
            $lines[] = $code;
        }
        return implode("\n", $lines) . "\n";
    }
}
