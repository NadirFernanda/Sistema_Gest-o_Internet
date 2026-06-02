<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use Illuminate\Http\Request;

class AutovendaOrderAdminController extends Controller
{
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

        return view('admin.orders.index', [
            'orders'       => $orders,
            'statusCounts' => $statusCounts,
            'totalPaid'    => $totalPaid,
            'totalAoa'     => $totalAoa,
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
