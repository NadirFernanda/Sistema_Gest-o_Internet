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
