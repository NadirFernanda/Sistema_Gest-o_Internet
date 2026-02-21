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

            $query->where(function ($q) use ($busca, $op, $driver) {
                $started = false;
                if (Schema::hasColumn('audit_logs', 'actor_name')) {
                    // Use a portable case-insensitive match that works across DB drivers.
                    $q->whereRaw('LOWER(actor_name) LIKE LOWER(?)', ["%{$busca}%"]);
                    $started = true;
                }
                if (Schema::hasColumn('audit_logs', 'resource_type')) {
                    if (! $started) { $q->where('resource_type', $op, "%{$busca}%"); $started = true; }
                    else { $q->orWhere('resource_type', $op, "%{$busca}%"); }
                }
                if (Schema::hasColumn('audit_logs', 'resource_id')) {
                    if (! $started) { $q->where('resource_id', $op, "%{$busca}%"); $started = true; }
                    else { $q->orWhere('resource_id', $op, "%{$busca}%"); }
                }

                // Only search `action` and `module` when those columns exist (legacy schemas may lack them)
                if (Schema::hasColumn('audit_logs', 'action')) {
                    if (! $started) { $q->where('action', $op, "%{$busca}%"); $started = true; }
                    else { $q->orWhere('action', $op, "%{$busca}%"); }
                }
                if (Schema::hasColumn('audit_logs', 'module')) {
                    if (! $started) { $q->where('module', $op, "%{$busca}%"); $started = true; }
                    else { $q->orWhere('module', $op, "%{$busca}%"); }
                }

                // Search JSON/text payload columns if present
                if (Schema::hasColumn('audit_logs', 'payload_before')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }
                if (Schema::hasColumn('audit_logs', 'payload_after')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }
                if (Schema::hasColumn('audit_logs', 'meta')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }

                // Also match users whose name/email match the free-text search so
                // audit rows that only store `actor_id` will still be returned.
                $userIds = \App\Models\User::query()
                    ->where('name', $op, "%{$busca}%")
                    ->orWhere('email', $op, "%{$busca}%")
                    ->limit(500)
                    ->pluck('id')
                    ->toArray();
                if (! empty($userIds) && Schema::hasColumn('audit_logs', 'actor_id')) {
                    if (! $started) { $q->whereIn('actor_id', $userIds); $started = true; }
                    else { $q->orWhereIn('actor_id', $userIds); }
                }
            });
        }

        if ($request->filled('user')) {
            $val = trim((string) $request->input('user'));
            if ($val !== '') {
                // search both stored actor_name and users table (in case actor_name is not stored)
                $query->where(function($q) use ($val, $op) {
                    $q->where('actor_name', $op, "%{$val}%");
                    // also match actor_id against users whose name or email matches
                    $userIds = \App\Models\User::query()
                        ->where('name', $op, "%{$val}%")
                        ->orWhere('email', $op, "%{$val}%")
                        ->limit(500)
                        ->pluck('id')
                        ->toArray();
                    if (!empty($userIds) && Schema::hasColumn('audit_logs', 'actor_id')) {
                        $q->orWhereIn('actor_id', $userIds);
                    }
                });
            }
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
