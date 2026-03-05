<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerApplication;
use Illuminate\Http\Request;

class ResellerAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = ResellerApplication::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20)->withQueryString();

        $statusCounts = ResellerApplication::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.resellers.index', [
            'applications' => $applications,
            'statusCounts' => $statusCounts,
        ]);
    }
}
