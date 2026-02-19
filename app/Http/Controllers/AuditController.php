<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');
        $driver = DB::getDriverName();
        $op = $driver === 'pgsql' ? 'ILIKE' : 'like';

        // Global free-text search (view uses input name="busca")
        if ($request->filled('busca')) {
            $busca = trim((string) $request->input('busca'));
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'like';

            $query->where(function ($q) use ($busca, $op) {
                                $q->where('actor_name', $op, "%{$busca}%")
                                    ->orWhere('resource_type', $op, "%{$busca}%")
                                    ->orWhere('resource_id', $op, "%{$busca}%");

                                // Only search `action` and `module` when those columns exist (legacy schemas may lack them)
                                if (Schema::hasColumn('audit_logs', 'action')) {
                                        $q->orWhere('action', $op, "%{$busca}%");
                                }
                                if (Schema::hasColumn('audit_logs', 'module')) {
                                        $q->orWhere('module', $op, "%{$busca}%");
                                }

                // Search JSON/text payload columns if present
                if (Schema::hasColumn('audit_logs', 'payload_before')) {
                    if ($driver === 'pgsql') {
                        $q->orWhereRaw("CAST(payload_before AS text) ILIKE ?", ["%{$busca}%"]);
                    } else {
                        $q->orWhereRaw("CAST(payload_before AS CHAR) LIKE ?", ["%{$busca}%"]);
                    }
                }
                if (Schema::hasColumn('audit_logs', 'payload_after')) {
                    if ($driver === 'pgsql') {
                        $q->orWhereRaw("CAST(payload_after AS text) ILIKE ?", ["%{$busca}%"]);
                    } else {
                        $q->orWhereRaw("CAST(payload_after AS CHAR) LIKE ?", ["%{$busca}%"]);
                    }
                }
                if (Schema::hasColumn('audit_logs', 'meta')) {
                    if ($driver === 'pgsql') {
                        $q->orWhereRaw("CAST(meta AS text) ILIKE ?", ["%{$busca}%"]);
                    } else {
                        $q->orWhereRaw("CAST(meta AS CHAR) LIKE ?", ["%{$busca}%"]);
                    }
                }
            });
        }

        if ($request->filled('user')) {
            $query->where('actor_name', 'like', '%'.$request->input('user').'%');
        }
        if ($request->filled('module') && Schema::hasColumn('audit_logs', 'module')) {
            $val = trim((string) $request->input('module'));
            if ($val !== '') {
                $query->where('module', $op, "%{$val}%");
            }
        }
        if ($request->filled('action') && Schema::hasColumn('audit_logs', 'action')) {
            $val = trim((string) $request->input('action'));
            if ($val !== '') {
                $query->where('action', $op, "%{$val}%");
            }
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $audits = $query->paginate(25)->withQueryString();

        // Provide distinct lists for UI filters (module/action) when columns exist
        $modules = [];
        $actions = [];
        if (Schema::hasColumn('audit_logs', 'module')) {
            $modules = AuditLog::query()->whereNotNull('module')->distinct()->orderBy('module')->pluck('module')->toArray();
        }
        if (Schema::hasColumn('audit_logs', 'action')) {
            $actions = AuditLog::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action')->toArray();
        }

        return view('admin.audits.index', compact('audits', 'modules', 'actions'));
    }

    public function modules(Request $request)
    {
        if (! Schema::hasColumn('audit_logs', 'module')) {
            return response()->json([]);
        }
        $q = trim((string) $request->query('q', ''));
        $query = AuditLog::query()->whereNotNull('module');
        if ($q !== '') {
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'like';
            $query->where('module', $op, "%{$q}%");
        }
        $list = $query->distinct()->orderBy('module')->limit(200)->pluck('module');
        return response()->json($list->values());
    }

    public function actions(Request $request)
    {
        if (! Schema::hasColumn('audit_logs', 'action')) {
            return response()->json([]);
        }
        $q = trim((string) $request->query('q', ''));
        $query = AuditLog::query()->whereNotNull('action');
        if ($q !== '') {
            $driver = DB::getDriverName();
            $op = $driver === 'pgsql' ? 'ILIKE' : 'like';
            $query->where('action', $op, "%{$q}%");
        }
        $list = $query->distinct()->orderBy('action')->limit(200)->pluck('action');
        return response()->json($list->values());
    }
}
