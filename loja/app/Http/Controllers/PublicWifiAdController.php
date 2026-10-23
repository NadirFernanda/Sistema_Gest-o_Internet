<?php

namespace App\Http\Controllers;

use App\Models\VoucherPlan;
use Illuminate\Contracts\View\View;

class PublicWifiAdController extends Controller
{
    public function index(): View
    {
        return view('store.portal-wifi-ad', [
            'plans' => VoucherPlan::active()->get([
                'slug',
                'name',
                'validity_label',
                'speed_label',
                'price_public_aoa',
            ]),
        ]);
    }
}
