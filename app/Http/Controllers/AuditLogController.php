<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

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

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->input('model'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25)->withQueryString();

        // eager load user display names to avoid N+1 when rendering
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = [];
        if (! empty($userIds)) {
            $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');
        }

        return view('admin.audit_logs.index', compact('logs', 'users'));
    }
}
