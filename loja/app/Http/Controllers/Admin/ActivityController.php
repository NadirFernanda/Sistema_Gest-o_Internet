<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutovendaOrder;
use App\Models\EquipmentOrder;
use App\Models\FamilyPlanRequest;
use App\Models\InstallationAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
    {
        $onlineNow = count(Cache::get('store_online_visitors', []));
        return view('admin.activity.index', compact('onlineNow'));
    }

    /**
     * GET /admin/actividade/data?period=24h|7d|30d|90d
     * Returns JSON with labels + per-type datasets for Chart.js.
     */
    public function data(Request $request)
    {
        $period = $request->query('period', '7d');

        [$slots, $labelsFn, $bucketFn] = match ($period) {
            '24h' => $this->last24hConfig(),
            '7d'  => $this->lastNDaysConfig(7),
            '30d' => $this->lastNDaysConfig(30),
            '90d' => $this->lastNDaysConfig(90),
            default => $this->lastNDaysConfig(7),
        };

        $visitors   = $this->visitorData($period, $slots, $bucketFn);
        $orders     = $this->modelData(AutovendaOrder::class,          $period, $slots, $bucketFn);
        $family     = $this->modelData(FamilyPlanRequest::class,       $period, $slots, $bucketFn);
        $appts      = $this->modelData(InstallationAppointment::class, $period, $slots, $bucketFn);
        $equipment  = $this->modelData(EquipmentOrder::class,          $period, $slots, $bucketFn);

        $labels = array_map($labelsFn, $slots);

        // Summary totals
        $totals = [
            'visitors'  => array_sum($visitors),
            'orders'    => array_sum($orders),
            'family'    => array_sum($family),
            'appts'     => array_sum($appts),
            'equipment' => array_sum($equipment),
        ];

        return response()->json([
            'labels'   => $labels,
            'visitors' => $visitors,
            'orders'   => $orders,
            'family'   => $family,
            'appts'    => $appts,
            'equipment'=> $equipment,
            'totals'   => $totals,
            'period'   => $period,
        ]);
    }

    // ── Period configs ────────────────────────────────────────────────────────

    private function last24hConfig(): array
    {
        $now   = now();
        $slots = [];
        for ($i = 23; $i >= 0; $i--) {
            $slots[] = $now->copy()->subHours($i)->format('Y-m-d H');
        }
        $labelsFn = fn ($s) => substr($s, 11, 2) . 'h';
        $bucketFn = fn ($row) => $row->day . ' ' . str_pad($row->hour ?? 0, 2, '0', STR_PAD_LEFT);
        return [$slots, $labelsFn, $bucketFn];
    }

    private function lastNDaysConfig(int $n): array
    {
        $today = now()->toDateString();
        $slots = [];
        for ($i = $n - 1; $i >= 0; $i--) {
            $slots[] = now()->subDays($i)->toDateString();
        }
        $labelsFn = fn ($s) => date('d/m', strtotime($s));
        $bucketFn = fn ($row) => $row->day;
        return [$slots, $labelsFn, $bucketFn];
    }

    // ── Data fetchers ─────────────────────────────────────────────────────────

    private function visitorData(string $period, array $slots, callable $bucketFn): array
    {
        if ($period === '24h') {
            $from = now()->subHours(23)->startOfHour();
            $rows = DB::table('visitor_logs')
                ->selectRaw("CAST(date AS VARCHAR) as day, hour, sessions")
                ->where('date', '>=', $from->toDateString())
                ->get();
            $map = [];
            foreach ($rows as $row) {
                $key = $row->day . ' ' . str_pad($row->hour, 2, '0', STR_PAD_LEFT);
                $map[$key] = ($map[$key] ?? 0) + (int) $row->sessions;
            }
        } else {
            $from = now()->subDays(count($slots) - 1)->startOfDay();
            $rows = DB::table('visitor_logs')
                ->selectRaw("CAST(date AS VARCHAR) as day, SUM(sessions) as sessions")
                ->where('date', '>=', $from->toDateString())
                ->groupBy('date')
                ->get();
            $map = [];
            foreach ($rows as $row) {
                $map[$row->day] = (int) $row->sessions;
            }
        }

        return array_map(fn ($s) => $map[$s] ?? 0, $slots);
    }

    private function modelData(string $model, string $period, array $slots, callable $bucketFn): array
    {
        if ($period === '24h') {
            $from = now()->subHours(23)->startOfHour();
            $rows = $model::selectRaw("DATE(created_at) as day, EXTRACT(HOUR FROM created_at)::int as hour, COUNT(*) as cnt")
                ->where('created_at', '>=', $from)
                ->groupByRaw('DATE(created_at), EXTRACT(HOUR FROM created_at)')
                ->get();
            $map = [];
            foreach ($rows as $row) {
                $key = $row->day . ' ' . str_pad($row->hour, 2, '0', STR_PAD_LEFT);
                $map[$key] = (int) $row->cnt;
            }
        } else {
            $from = now()->subDays(count($slots) - 1)->startOfDay();
            $rows = $model::selectRaw("DATE(created_at) as day, COUNT(*) as cnt")
                ->where('created_at', '>=', $from)
                ->groupByRaw('DATE(created_at)')
                ->get();
            $map = [];
            foreach ($rows as $row) {
                $map[$row->day] = (int) $row->cnt;
            }
        }

        return array_map(fn ($s) => $map[$s] ?? 0, $slots);
    }
}
