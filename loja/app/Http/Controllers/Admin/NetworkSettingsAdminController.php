<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResellerNetworkSetting;
use Illuminate\Http\Request;

class NetworkSettingsAdminController extends Controller
{
    public function index()
    {
        $settings = ResellerNetworkSetting::orderBy('id')->get();
        return view('admin.network_settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'          => 'required|array',
            'settings.*.id'     => 'required|exists:reseller_network_settings,id',
            'settings.*.value'  => 'required|numeric|min:0|max:100',
        ]);

        foreach ($request->settings as $item) {
            $setting = ResellerNetworkSetting::find($item['id']);
            if ($setting) {
                ResellerNetworkSetting::set($setting->key, (float) $item['value']);
            }
        }

        return back()->with('status', 'Percentagens da rede actualizadas com sucesso.');
    }
}
