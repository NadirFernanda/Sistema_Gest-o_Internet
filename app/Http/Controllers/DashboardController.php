<?php

namespace App\Http\Controllers;

use App\Models\MikroTikSite;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $sitesComErro = MikroTikSite::where('api_status', 'erro')
            ->whereNotNull('api_checked_at')
            ->get(['id', 'nome', 'host', 'api_last_error', 'api_checked_at']);

        return view('dashboard', compact('sitesComErro'));
    }
}
