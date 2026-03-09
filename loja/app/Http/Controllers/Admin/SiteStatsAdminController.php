<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteStat;
use Illuminate\Http\Request;

class SiteStatsAdminController extends Controller
{
    public function index()
    {
        $stats = SiteStat::orderBy('ordem')->get();
        return view('admin.site_stats.index', compact('stats'));
    }

    public function update(Request $request, SiteStat $stat)
    {
        $validated = $request->validate([
            'valor'          => 'required|string|max:50',
            'legenda'        => 'required|string|max:100',
            'count_to'       => 'nullable|numeric|min:0|max:9999999',
            'count_decimals' => 'nullable|integer|min:0|max:4',
            'count_suffix'   => 'nullable|string|max:10',
        ]);

        $stat->update([
            'valor'          => $validated['valor'],
            'legenda'        => $validated['legenda'],
            'count_to'       => $validated['count_to'] ?? null,
            'count_decimals' => $validated['count_decimals'] ?? 0,
            'count_suffix'   => $validated['count_suffix'] ?? null,
        ]);

        return redirect()->route('admin.site_stats.index')
            ->with('success', 'Estatística actualizada com sucesso.');
    }
}
