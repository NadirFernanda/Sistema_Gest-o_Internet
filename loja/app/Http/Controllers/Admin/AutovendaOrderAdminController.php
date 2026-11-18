<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Services\AutovendaOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AutovendaOrderAdminController extends Controller
{
    public function reconcile(Request $request, AutovendaOrder $order, AutovendaOrderService $delivery)
    {
        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100'],
            'transaction_id' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'amount_aoa' => ['required', 'integer', 'min:1'],
            'confirmed_in_emis' => ['accepted'],
        ]);

        DB::transaction(function () use ($order, $data, $delivery, $request) {
            $locked = AutovendaOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->isPaid() || $locked->payment_gateway !== 'gpo') {
                throw ValidationException::withMessages(['transaction_id' => 'Este pedido já foi pago ou não pertence ao GPO.']);
            }

            $references = array_merge([$locked->payment_reference], $locked->meta['gpo_references'] ?? []);
            if (! in_array($data['payment_reference'], $references, true)
                || (int) $data['amount_aoa'] !== (int) $locked->amount_aoa) {
                throw ValidationException::withMessages(['payment_reference' => 'A referência ou o valor não correspondem ao pedido.']);
            }

            if (AutovendaOrder::where('manual_gpo_transaction_id', $data['transaction_id'])->exists()) {
                throw ValidationException::withMessages(['transaction_id' => 'Esta transacção já foi usada noutra reconciliação.']);
            }

            $locked->manual_gpo_transaction_id = $data['transaction_id'];
            $meta = $locked->meta ?? [];
            $meta['manual_gpo_reconciliation'] = [
                'payment_reference' => $data['payment_reference'],
                'verified_at' => now()->toIso8601String(),
            ];
            $locked->meta = $meta;
            $locked->save();

            $delivery->confirmPaymentAndDeliver($locked);
            Log::warning('GPO: pagamento reconciliado manualmente pelo admin', [
                'order_id' => $locked->id,
                'reference' => $data['payment_reference'],
                'transaction_id' => $data['transaction_id'],
                'admin_ip' => $request->ip(),
            ]);
        });

        $message = $order->fresh()->wifi_code
            ? 'Pagamento reconciliado e voucher atribuído. Confirme que o cliente recebeu o código.'
            : 'Pagamento reconciliado, mas não havia voucher em stock. Entregue um código manualmente ou providencie reembolso.';

        return redirect()->route('admin.autovenda.index', ['q' => $order->id])
            ->with('success', $message);
    }

    public function index(Request $request)
    {
        $query = $this->buildQuery($request);

        $orders = (clone $query)->orderByDesc('id')->paginate(25)->withQueryString();

        $statusCounts = AutovendaOrder::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Totais do período filtrado (apenas pagos)
        $paidQuery  = $this->buildQuery($request)->where('status', AutovendaOrder::STATUS_PAID);
        $totalPaid  = (clone $paidQuery)->count();
        $totalAoa   = (clone $paidQuery)->sum('amount_aoa');

        $plans = \App\Models\VoucherPlan::orderBy('sort_order')->get(['slug', 'name']);

        // Ordens pagas sem código — exigem intervenção manual (stock esgotado na altura do pagamento)
        $paidWithoutCode = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)
            ->whereNull('wifi_code')
            ->count();
        $paidWithoutBonus = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)
            ->whereNotNull('bonus_plan_slug')
            ->whereNull('bonus_wifi_code')
            ->count();
        $failedAutomaticDelivery = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)
            ->whereNotNull('wifi_code')
            ->where('delivery_retry_count', '>=', 5)
            ->where(function ($query) {
                $query->where(function ($email) {
                    $email->whereNotNull('customer_email')->where('customer_email', '!=', '')
                        ->where('delivered_via_email', false);
                })->orWhere(function ($whatsapp) {
                    $whatsapp->whereNotNull('customer_phone')->where('customer_phone', '!=', '')
                        ->where('delivered_via_whatsapp', false);
                });
            })
            ->count();

        return view('admin.orders.index', [
            'orders'          => $orders,
            'statusCounts'    => $statusCounts,
            'totalPaid'       => $totalPaid,
            'totalAoa'        => $totalAoa,
            'plans'           => $plans,
            'paidWithoutCode' => $paidWithoutCode,
            'paidWithoutBonus' => $paidWithoutBonus,
            'failedAutomaticDelivery' => $failedAutomaticDelivery,
        ]);
    }

    public function export(Request $request)
    {
        $query = $this->buildQuery($request)
            ->where('status', AutovendaOrder::STATUS_PAID)
            ->orderByDesc('paid_at');

        $orders = $query->get([
            'id', 'plan_name', 'amount_aoa', 'currency',
            'payment_reference', 'payment_gateway',
            'customer_name', 'customer_email', 'customer_phone',
            'wifi_code', 'paid_at', 'created_at',
        ]);

        $filename = 'reconciliacao_gpo_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $fh = fopen('php://output', 'w');
            // BOM UTF-8 para Excel abrir correctamente
            fwrite($fh, "\xEF\xBB\xBF");

            fputcsv($fh, [
                'ID', 'Plano', 'Valor (AOA)', 'Moeda',
                'Referência GPO', 'Gateway',
                'Nome Cliente', 'Email', 'Telefone',
                'Código WiFi', 'Data Pagamento', 'Data Criação',
            ], ';');

            foreach ($orders as $o) {
                fputcsv($fh, [
                    $o->id,
                    $o->plan_name,
                    number_format($o->amount_aoa, 2, ',', '.'),
                    $o->currency ?? 'AOA',
                    $o->payment_reference,
                    $o->payment_gateway,
                    $o->customer_name,
                    $o->customer_email,
                    $o->customer_phone,
                    $o->wifi_code,
                    optional($o->paid_at)->format('d/m/Y H:i:s'),
                    optional($o->created_at)->format('d/m/Y H:i:s'),
                ], ';');
            }

            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildQuery(Request $request)
    {
        $query = AutovendaOrder::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $request->get('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($planId = $request->get('plan_id')) {
            $query->where('plan_id', $planId);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('payment_reference', 'like', "%{$search}%")
                  ->orWhere('wifi_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filtro por período — aplicado sobre paid_at para reconciliação GPO
        if ($dateFrom = $request->get('date_from')) {
            $query->where('paid_at', '>=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('paid_at', '<=', $dateTo . ' 23:59:59');
        }

        return $query;
    }
}
