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

                // Support legacy column names for payloads (before/after, old_values/new_values)
                if (Schema::hasColumn('audit_logs', 'before')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(before AS text) ILIKE ?" : "CAST(before AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(before AS text) ILIKE ?" : "CAST(before AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }
                if (Schema::hasColumn('audit_logs', 'after')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(after AS text) ILIKE ?" : "CAST(after AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(after AS text) ILIKE ?" : "CAST(after AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }
                if (Schema::hasColumn('audit_logs', 'old_values')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(old_values AS text) ILIKE ?" : "CAST(old_values AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(old_values AS text) ILIKE ?" : "CAST(old_values AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }
                if (Schema::hasColumn('audit_logs', 'new_values')) {
                    if (! $started) { $started = true; $q->whereRaw($driver === 'pgsql' ? "CAST(new_values AS text) ILIKE ?" : "CAST(new_values AS CHAR) LIKE ?", ["%{$busca}%"]); }
                    else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(new_values AS text) ILIKE ?" : "CAST(new_values AS CHAR) LIKE ?", ["%{$busca}%"]); }
                }

                // Also match users whose name/email match the free-text search so
                // audit rows that only store `actor_id` will still be returned.
                $userIds = \App\Models\User::query()
                    ->where('name', $op, "%{$busca}%")
                    ->orWhere('email', $op, "%{$busca}%")
                    ->limit(500)
                    ->pluck('id')
                    ->toArray();
                if (! empty($userIds)) {
                    $cols = [];
                    if (Schema::hasColumn('audit_logs', 'actor_id')) { $cols[] = 'actor_id'; }
                    if (Schema::hasColumn('audit_logs', 'user_id')) { $cols[] = 'user_id'; }
                    foreach ($cols as $c) {
                        if (! $started) { $q->whereIn($c, $userIds); $started = true; }
                        else { $q->orWhereIn($c, $userIds); }
                    }
                }
            });
        }

        if ($request->filled('user')) {
            $val = trim((string) $request->input('user'));
            if ($val !== '') {
                // search both stored actor_name and users table (in case actor_name is not stored)
                $query->where(function($q) use ($val, $op, $driver) {
                    $started = false;
                    if (Schema::hasColumn('audit_logs', 'actor_name')) {
                        $q->whereRaw('LOWER(actor_name) LIKE LOWER(?)', ["%{$val}%"]);
                        $started = true;
                    }

                    // also match actor_id against users whose name or email matches
                    $userIds = \App\Models\User::query()
                        ->where('name', $op, "%{$val}%")
                        ->orWhere('email', $op, "%{$val}%")
                        ->limit(500)
                        ->pluck('id')
                        ->toArray();
                    if (!empty($userIds)) {
                        $cols = [];
                        if (Schema::hasColumn('audit_logs', 'actor_id')) { $cols[] = 'actor_id'; }
                        if (Schema::hasColumn('audit_logs', 'user_id')) { $cols[] = 'user_id'; }
                        foreach ($cols as $c) {
                            if (! $started) { $q->whereIn($c, $userIds); $started = true; }
                            else { $q->orWhereIn($c, $userIds); }
                        }
                    }

                    // Fallback: search inside JSON/text columns for the user's name
                    if (Schema::hasColumn('audit_logs', 'payload_before')) {
                        if (! $started) { $q->whereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$val}%"]); $started = true; }
                        else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$val}%"]); }
                    }
                    if (Schema::hasColumn('audit_logs', 'payload_after')) {
                        if (! $started) { $q->whereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$val}%"]); $started = true; }
                        else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$val}%"]); }
                    }
                    if (Schema::hasColumn('audit_logs', 'meta')) {
                        if (! $started) { $q->whereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$val}%"]); $started = true; }
                        else { $q->orWhereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$val}%"]); }
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
        // Allow searching by resource (type/id or combined)
        if ($request->filled('resource')) {
            $val = trim((string) $request->input('resource'));
            if ($val !== '') {
                $driver = DB::getDriverName();
                $query->where(function($q) use ($val, $op, $driver) {
                    if (Schema::hasColumn('audit_logs', 'resource_type')) {
                        $q->where('resource_type', $op, "%{$val}%");
                    }
                    if (Schema::hasColumn('audit_logs', 'resource_id')) {
                        $q->orWhere('resource_id', $op, "%{$val}%");
                    }
                    if (Schema::hasColumn('audit_logs', 'resource_type') && Schema::hasColumn('audit_logs', 'resource_id')) {
                        $concat = $driver === 'pgsql'
                            ? "(resource_type || ' ' || resource_id) ILIKE ?"
                            : "CONCAT(resource_type,' ',resource_id) LIKE ?";
                        $q->orWhereRaw($concat, ["%{$val}%"]);
                    }

                    // also search payloads/meta for resource identifiers
                    if (Schema::hasColumn('audit_logs', 'payload_before')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'payload_after')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'meta')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$val}%"]); }
                });
            }
        }

        // Allow searching by summary (human-readable message contained in payloads/meta/action/module)
        if ($request->filled('summary')) {
            $val = trim((string) $request->input('summary'));
            if ($val !== '') {
                $driver = DB::getDriverName();
                $query->where(function($q) use ($val, $op, $driver) {
                    if (Schema::hasColumn('audit_logs', 'action')) { $q->where('action', $op, "%{$val}%"); }
                    if (Schema::hasColumn('audit_logs', 'module')) { $q->orWhere('module', $op, "%{$val}%"); }
                    if (Schema::hasColumn('audit_logs', 'actor_name')) { $q->orWhereRaw('LOWER(actor_name) LIKE LOWER(?)', ["%{$val}%"]); }

                    if (Schema::hasColumn('audit_logs', 'payload_before')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_before AS text) ILIKE ?" : "CAST(payload_before AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'payload_after')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(payload_after AS text) ILIKE ?" : "CAST(payload_after AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'meta')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(meta AS text) ILIKE ?" : "CAST(meta AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'before')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(before AS text) ILIKE ?" : "CAST(before AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'after')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(after AS text) ILIKE ?" : "CAST(after AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'old_values')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(old_values AS text) ILIKE ?" : "CAST(old_values AS CHAR) LIKE ?", ["%{$val}%"]); }
                    if (Schema::hasColumn('audit_logs', 'new_values')) { $q->orWhereRaw($driver === 'pgsql' ? "CAST(new_values AS text) ILIKE ?" : "CAST(new_values AS CHAR) LIKE ?", ["%{$val}%"]); }
                });
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
