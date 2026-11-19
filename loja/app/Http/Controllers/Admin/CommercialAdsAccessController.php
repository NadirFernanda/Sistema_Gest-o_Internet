<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\CommercialAdsAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommercialAdsAccessController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        abort_unless(CommercialAdsAccess::enabled(), 404);

        $submittedSecret = $request->input('commercial_secret');
        if (! is_string($submittedSecret)
            || strlen($submittedSecret) > 512
            || ! CommercialAdsAccess::matches($submittedSecret)) {
            return redirect()->route('admin.ads.index')
                ->withErrors(['commercial_secret' => 'Credencial comercial inválida.']);
        }

        CommercialAdsAccess::grant($request);

        return redirect()->route('admin.ads.index');
    }

    public function destroy(Request $request): RedirectResponse
    {
        CommercialAdsAccess::revoke($request);

        return redirect()->route('admin.ads.index');
    }
}
