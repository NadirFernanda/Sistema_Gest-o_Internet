<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Models\FamilyPlanRequest;
use App\Models\ResellerPurchase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GpoReconciliationController extends Controller
{
    private const PER_PAGE = 30;

    public function index(Request $request)
    {
        $rows      = $this->buildRows($request);
        $totalAoa  = $rows->sum('amount');
        $totalRows = $rows->count();

        $page    = (int) ($request->get('page', 1));
        $paginated = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $totalRows,
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.reconciliation.gpo', [
            'rows'     => $paginated,
            'totalAoa' => $totalAoa,
            'total'    => $totalRows,
        ]);
    }

    public function export(Request $request)
    {
        $rows     = $this->buildRows($request);
        $filename = 'reconciliacao_gpo_completa_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $fh = fopen('php://output', 'w');
            fwrite($fh, "\xEF\xBB\xBF"); // BOM UTF-8 para Excel
            fputcsv($fh, [
                'Referência GPO (merchantReference)',
                'Tipo',
                'Descrição',
                'Valor (AOA)',
                'Cliente',
                'Data Pagamento',
                'Estado',
            ], ';');

            foreach ($rows as $r) {
                fputcsv($fh, [
                    $r['ref'],
                    $r['type_label'],
                    $r['description'],
                    number_format((float) $r['amount'], 2, ',', '.'),
                    $r['customer'],
                    $r['date'] ? $r['date']->format('d/m/Y H:i:s') : '',
                    $r['status'],
                ], ';');
            }
            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buildRows(Request $request): Collection
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $type     = $request->get('type');
        $search   = trim((string) $request->get('q', ''));

        $rows = collect();

        // ── 1. Planos individuais (autovenda) ─────────────────────────────────
        if (! $type || $type === 'individual') {
            $q = AutovendaOrder::where('status', AutovendaOrder::STATUS_PAID)
                ->whereNotNull('paid_at');

            if ($dateFrom) $q->where('paid_at', '>=', $dateFrom . ' 00:00:00');
            if ($dateTo)   $q->where('paid_at', '<=', $dateTo   . ' 23:59:59');
            if ($search)   $q->where(fn($s) =>
                $s->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('customer_name',  'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
            );

            $rows = $rows->concat(
                $q->get()->map(fn($o) => [
                    'type'       => 'individual',
                    'type_label' => 'Plano Individual',
                    'ref'        => $o->payment_reference ?? '—',
                    'description'=> $o->plan_name ?? $o->plan_id,
                    'amount'     => (float) $o->amount_aoa,
                    'customer'   => implode(' / ', array_filter([$o->customer_name, $o->customer_phone, $o->customer_email])),
                    'date'       => $o->paid_at,
                    'status'     => 'Pago',
                    'source_id'  => $o->id,
                ])
            );
        }

        // ── 2. Planos familiares / empresariais ───────────────────────────────
        if (! $type || $type === 'family') {
            $q = FamilyPlanRequest::where('payment_method', FamilyPlanRequest::METHOD_GPO)
                ->whereIn('status', [
                    FamilyPlanRequest::STATUS_ACTIVATED,
                    FamilyPlanRequest::STATUS_PENDING,
                    FamilyPlanRequest::STATUS_CONFIRMED,
                ]);

            // Para planos familiares usamos updated_at como proxy de paid_at
            if ($dateFrom) $q->where('updated_at', '>=', $dateFrom . ' 00:00:00');
            if ($dateTo)   $q->where('updated_at', '<=', $dateTo   . ' 23:59:59');
            if ($search)   $q->where(fn($s) =>
                $s->where('gpo_reference',    'like', "%{$search}%")
                  ->orWhere('payment_reference','like', "%{$search}%")
                  ->orWhere('customer_name',   'like', "%{$search}%")
                  ->orWhere('customer_email',  'like', "%{$search}%")
                  ->orWhere('customer_phone',  'like', "%{$search}%")
            );

            $rows = $rows->concat(
                $q->get()->map(fn($r) => [
                    'type'       => 'family',
                    'type_label' => 'Plano Familiar/Empresarial',
                    'ref'        => $r->gpo_reference ?? $r->payment_reference ?? '—',
                    'description'=> $r->plan_name,
                    'amount'     => (float) ($r->plan_preco ?? 0),
                    'customer'   => implode(' / ', array_filter([$r->customer_name, $r->customer_phone])),
                    'date'       => $r->updated_at,
                    'status'     => match($r->status) {
                        'activated' => 'Activado',
                        'pending'   => 'Aguarda activação',
                        'confirmed' => 'Confirmado',
                        default     => ucfirst($r->status),
                    },
                    'source_id'  => $r->id,
                ])
            );
        }

        // ── 3. Compras de revendedores (GPO — exclui bónus) ──────────────────
        if (! $type || $type === 'reseller') {
            $q = ResellerPurchase::where('status', 'completed')
                ->where('payment_reference', 'not like', 'BONUS-%')
                ->whereNotNull('payment_reference')
                ->with('application:id,full_name');

            if ($dateFrom) $q->where('paid_at', '>=', $dateFrom . ' 00:00:00');
            if ($dateTo)   $q->where('paid_at', '<=', $dateTo   . ' 23:59:59');
            if ($search)   $q->where(fn($s) =>
                $s->where('payment_reference', 'like', "%{$search}%")
                  ->orWhereHas('application', fn($a) =>
                      $a->where('full_name', 'like', "%{$search}%")
                       ->orWhere('email',    'like', "%{$search}%")
                  )
            );

            $rows = $rows->concat(
                $q->get()->map(fn($p) => [
                    'type'       => 'reseller',
                    'type_label' => 'Compra Revendedor',
                    'ref'        => $p->payment_reference ?? '—',
                    'description'=> ($p->plan_name ?? '?') . ' ×' . $p->quantity,
                    'amount'     => (float) $p->net_amount_aoa,
                    'customer'   => optional($p->application)->full_name ?? '—',
                    'date'       => $p->paid_at,
                    'status'     => 'Pago',
                    'source_id'  => $p->id,
                ])
            );
        }

        // Ordenar por data desc (mais recentes primeiro)
        return $rows->sortByDesc(fn($r) => optional($r['date'])->timestamp ?? 0)->values();
    }
}
