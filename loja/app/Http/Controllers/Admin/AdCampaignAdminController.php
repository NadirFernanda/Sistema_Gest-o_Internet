<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdCampaign;
use App\Models\VoucherPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class AdCampaignAdminController extends Controller
{
    public function index(): View
    {
        $campaigns = AdCampaign::with('bonusPlan')->orderByDesc('id')->paginate(25);

        return view('admin.ads.index', compact('campaigns'));
    }

    public function create(): View
    {
        return view('admin.ads.form', [
            'campaign' => new AdCampaign(),
            'voucherPlans' => VoucherPlan::where('active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $purchasePlanSlugs = $data['purchase_plan_slugs'] ?? [];
        $data['image_path'] = $this->storeImage($request);
        unset($data['image'], $data['has_bonus'], $data['purchase_plan_slugs']);

        $campaign = AdCampaign::create($data);
        $campaign->qualifyingPlans()->sync($purchasePlanSlugs);

        return redirect()->route('admin.ads.index')->with('success', 'Campanha criada.');
    }

    public function edit(int $campaign): View
    {
        return view('admin.ads.form', [
            'campaign' => AdCampaign::with('qualifyingPlans')->findOrFail($campaign),
            'voucherPlans' => VoucherPlan::where('active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, int $campaign): RedirectResponse
    {
        $ad = AdCampaign::findOrFail($campaign);
        $oldImage = $ad->image_path;
        $data = $this->validated($request, $ad);
        $purchasePlanSlugs = $data['purchase_plan_slugs'] ?? [];
        $newImage = $request->hasFile('image') ? $this->storeImage($request) : null;
        unset($data['image'], $data['has_bonus'], $data['purchase_plan_slugs']);

        $ad->update($data + ($newImage ? ['image_path' => $newImage] : []));
        $ad->qualifyingPlans()->sync($purchasePlanSlugs);
        if ($newImage && $oldImage !== $newImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.ads.index')->with('success', 'Campanha actualizada.');
    }

    public function destroy(int $campaign): RedirectResponse
    {
        $ad = AdCampaign::findOrFail($campaign);
        $imagePath = $ad->image_path;
        $ad->delete();
        Storage::disk('public')->delete($imagePath);

        return redirect()->route('admin.ads.index')->with('success', 'Campanha eliminada.');
    }

    private function validated(Request $request, ?AdCampaign $existing = null): array
    {
        $hasBonus = $request->boolean('has_bonus') && $request->input('campaign_type') === 'own';
        $endDateRules = ['nullable', 'date'];
        if ($request->filled('starts_at')) {
            $endDateRules[] = 'after:starts_at';
        }

        $data = $request->validate([
            'campaign_type' => ['required', Rule::in(array_keys(AdCampaign::TYPES))],
            'advertiser_name' => ['nullable', 'required_if:campaign_type,sponsored', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => [$existing ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'destination_url' => ['required', 'url:http,https', 'max:2048'],
            'button_text' => ['required', 'string', 'max:40'],
            'placement' => ['required', Rule::in(array_keys(AdCampaign::PLACEMENTS))],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => $endDateRules,
            'has_bonus' => ['nullable', 'boolean'],
            'bonus_plan_slug' => [$hasBonus ? 'required' : 'nullable', 'exists:voucher_plans,slug'],
            'purchase_plan_slugs' => [$hasBonus ? 'required' : 'nullable', 'array', $hasBonus ? 'min:1' : 'sometimes'],
            'purchase_plan_slugs.*' => ['string', 'distinct', 'exists:voucher_plans,slug'],
        ]);

        $data['advertiser_name'] = $data['campaign_type'] === 'own'
            ? 'AngolaWiFi'
            : $data['advertiser_name'];
        if (! $hasBonus) {
            $data['bonus_plan_slug'] = null;
            $data['purchase_plan_slugs'] = [];
        }
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('ads', 'public');
        if (! $path) {
            throw new RuntimeException('Não foi possível guardar a imagem da campanha.');
        }

        return $path;
    }
}
