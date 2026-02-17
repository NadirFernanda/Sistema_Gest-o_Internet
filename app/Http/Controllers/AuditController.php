<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeletionAudit;
use App\Exports\DeletionAuditsExport;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = DeletionAudit::query()->orderBy('created_at', 'desc');

        if ($user = $request->input('user_id')) {
            $query->where('user_id', $user);
        }
        if ($entity = $request->input('entity_type')) {
            $query->where('entity_type', $entity);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $audits = $query->paginate(20)->withQueryString();

        return view('audits.index', compact('audits'));
    }

    public function exportCsv(Request $request)
    {
        $query = DeletionAudit::query()->orderBy('created_at', 'desc');

        if ($user = $request->input('user_id')) {
            $query->where('user_id', $user);
        }
        if ($entity = $request->input('entity_type')) {
            $query->where('entity_type', $entity);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $audits = $query->get();

        $filename = 'deletion_audits_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($audits) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Entidade', 'Entity ID', 'Usuário', 'Motivo', 'Payload', 'Quando']);

            foreach ($audits as $a) {
                fputcsv($out, [
                    $a->id,
                    class_basename($a->entity_type),
                    $a->entity_id,
                    $a->user_id,
                    $a->reason,
                    is_array($a->payload) ? json_encode($a->payload) : $a->payload,
                    $a->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportXlsx(Request $request)
    {
        $query = DeletionAudit::query()->orderBy('created_at', 'desc');

        if ($user = $request->input('user_id')) {
            $query->where('user_id', $user);
        }
        if ($entity = $request->input('entity_type')) {
            $query->where('entity_type', $entity);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $audits = $query->get();

        $filename = 'deletion_audits_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new DeletionAuditsExport($audits), $filename);
    }
}
