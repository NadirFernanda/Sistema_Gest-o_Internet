<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AuditLogsExport;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $allowed = false;
        if (isset($user->role) && strtolower($user->role) === 'administrador') {
            $allowed = true;
        } elseif (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('audits.view')) {
            $allowed = true;
        }

        if (! $allowed) {
            abort(403);
        }

        $query = AuditLog::query();

        // filter by role / user name / date range / action / model
        // If a user name is provided, resolve matching users (by name or email)
        if ($request->filled('user')) {
            $term = $request->input('user');
            $userQ = User::query();
            $userQ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            });
            if ($request->filled('role')) {
                $roleName = $request->input('role');
                $userQ->whereHas('roles', function($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            }
            $ids = $userQ->pluck('id')->toArray();
            if (empty($ids)) {
                // no matches -> return empty result
                $logs = collect();
                return view('admin.audit_logs.index', compact('logs'))->with(['users' => collect(), 'roles' => Role::pluck('name')->toArray()]);
            }
            $query->whereIn('user_id', $ids);
        } elseif ($request->filled('role')) {
            // filter by role via users with that role
            $roleName = $request->input('role');
            $ids = User::whereHas('roles', function($q) use ($roleName) { $q->where('name', $roleName); })->pluck('id')->toArray();
            if (empty($ids)) {
                $logs = collect();
                return view('admin.audit_logs.index', compact('logs'))->with(['users' => collect(), 'roles' => Role::pluck('name')->toArray()]);
            }
            $query->whereIn('user_id', $ids);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->input('model'));
        }

        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
            $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('created_at', '>=', $from);
            } elseif ($to) {
                $query->where('created_at', '<=', $to);
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        // eager load user display names to avoid N+1 when rendering
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = collect();
        if (! empty($userIds)) {
            $users = \App\Models\User::whereIn('id', $userIds)->with('roles')->get()->keyBy('id');
        }

        $roles = Role::pluck('name')->toArray();
        return view('admin.audit_logs.index', compact('logs', 'users', 'roles'));
    }

    public function export(Request $request)
    {
        // reuse filters from index (simplified copy)
        $query = AuditLog::query();

        if ($request->filled('user')) {
            $term = $request->input('user');
            $userQ = User::query();
            $userQ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            });
            if ($request->filled('role')) {
                $roleName = $request->input('role');
                $userQ->whereHas('roles', function($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            }
            $ids = $userQ->pluck('id')->toArray();
            if (empty($ids)) {
                return response()->streamDownload(function () { echo ''; }, 'audit_logs.csv');
            }
            $query->whereIn('user_id', $ids);
        } elseif ($request->filled('role')) {
            $roleName = $request->input('role');
            $ids = User::whereHas('roles', function($q) use ($roleName) { $q->where('name', $roleName); })->pluck('id')->toArray();
            if (empty($ids)) {
                return response()->streamDownload(function () { echo ''; }, 'audit_logs.csv');
            }
            $query->whereIn('user_id', $ids);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->input('model'));
        }

        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
            $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('created_at', '>=', $from);
            } elseif ($to) {
                $query->where('created_at', '<=', $to);
            }
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // eager-load users
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = collect();
        if (! empty($userIds)) {
            $users = User::whereIn('id', $userIds)->with('roles')->get()->keyBy('id');
        }

        $callback = function() use ($logs, $users) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','created_at','user_id','user_name','user_email','roles','action','auditable_type','auditable_id','old_values','new_values','ip_address','hmac']);
            foreach ($logs as $log) {
                $u = $users->has($log->user_id) ? $users->get($log->user_id) : null;
                $roles = $u ? $u->roles->pluck('name')->join('|') : ($log->role ?? '');
                fputcsv($out, [
                    $log->id,
                    $log->created_at,
                    $log->user_id,
                    $u ? $u->name : '',
                    $u ? $u->email : '',
                    $roles,
                    $log->action,
                    $log->auditable_type,
                    $log->auditable_id,
                    $log->old_values ? json_encode($log->old_values, JSON_UNESCAPED_UNICODE) : '',
                    $log->new_values ? json_encode($log->new_values, JSON_UNESCAPED_UNICODE) : '',
                    $log->ip_address,
                    $log->hmac ?? '',
                ]);
            }
            fclose($out);
        };

        $filename = 'audit_logs_'.now()->format('Ymd_His').'.csv';
        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public function exportXlsx(Request $request)
    {
        // reuse same filters as export/index
        $query = AuditLog::query();

        if ($request->filled('user')) {
            $term = $request->input('user');
            $userQ = User::query();
            $userQ->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            });
            if ($request->filled('role')) {
                $roleName = $request->input('role');
                $userQ->whereHas('roles', function($q) use ($roleName) {
                    $q->where('name', $roleName);
                });
            }
            $ids = $userQ->pluck('id')->toArray();
            if (empty($ids)) {
                return response()->noContent();
            }
            $query->whereIn('user_id', $ids);
        } elseif ($request->filled('role')) {
            $roleName = $request->input('role');
            $ids = User::whereHas('roles', function($q) use ($roleName) { $q->where('name', $roleName); })->pluck('id')->toArray();
            if (empty($ids)) {
                return response()->noContent();
            }
            $query->whereIn('user_id', $ids);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->input('model'));
        }

        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
            $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;
            if ($from && $to) {
                $query->whereBetween('created_at', [$from, $to]);
            } elseif ($from) {
                $query->where('created_at', '>=', $from);
            } elseif ($to) {
                $query->where('created_at', '<=', $to);
            }
        }

        $query = $query->with(['user.roles'])->orderBy('created_at', 'desc');
        $filename = 'audit_logs_'.now()->format('Ymd_His').'.xlsx';
        return Excel::download(new AuditLogsExport($query), $filename);
    }
}
