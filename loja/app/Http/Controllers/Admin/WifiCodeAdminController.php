<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WifiCode;
use Illuminate\Http\Request;

class WifiCodeAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = WifiCode::query()->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = trim((string) $request->get('q', ''))) {
            $query->where('code', 'like', "%{$search}%");
        }

        $codes = $query->paginate(50)->withQueryString();

        $statusCounts = WifiCode::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.wifi_codes.index', [
            'codes' => $codes,
            'statusCounts' => $statusCounts,
        ]);
    }
}
