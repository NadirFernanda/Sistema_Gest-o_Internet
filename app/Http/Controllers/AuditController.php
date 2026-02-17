<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('user')) {
            $query->where('actor_name', 'like', '%'.$request->input('user').'%');
        }
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $audits = $query->paginate(25)->withQueryString();

        return view('admin.audits.index', compact('audits'));
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeletionAudit;

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
}
