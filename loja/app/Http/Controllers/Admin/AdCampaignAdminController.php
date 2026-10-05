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
            'destination_url' => ['nullable', 'required_if:campaign_type,sponsored', 'url:http,https', 'max:2048'],
            'button_text' => ['required', 'string', 'max:40'],
            'placement' => ['required', Rule::in(array_keys(AdCampaign::PLACEMENTS))],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => $endDateRules,
            'has_bonus' => ['nullable', 'boolean'],
            'bonus_plan_slug' => [$hasBonus ? 'required' : 'nullable', 'exists:voucher_plans,slug'],
            'purchase_plan_slugs' => [$hasBonus ? 'required' : 'nullable', 'array', $hasBonus ? 'min:1' : 'sometimes'],
            'purchase_plan_slugs.*' => ['string', 'distinct', 'exists:voucher_plans,slug'],
        ], [
            'campaign_type.required' => 'Seleccione o tipo de campanha.',
            'campaign_type.in' => 'O tipo de campanha seleccionado não é válido.',
            'advertiser_name.required_if' => 'Indique o nome do anunciante externo.',
            'advertiser_name.string' => 'O nome do anunciante deve ser texto.',
            'advertiser_name.max' => 'O nome do anunciante não pode exceder 120 caracteres.',
            'title.required' => 'Indique o título da campanha.',
            'title.max' => 'O título não pode exceder 160 caracteres.',
            'description.max' => 'A descrição não pode exceder 500 caracteres.',
            'image.required' => 'Seleccione uma imagem para a campanha.',
            'image.uploaded' => 'O envio da imagem falhou. Verifique o tamanho do ficheiro e tente novamente.',
            'image.image' => 'O ficheiro seleccionado tem de ser uma imagem válida.',
            'image.mimes' => 'A imagem deve estar no formato JPG, PNG ou WebP.',
            'image.max' => 'A imagem não pode exceder 5 MB.',
            'destination_url.required_if' => 'Indique o link de destino do anúncio patrocinado.',
            'destination_url.url' => 'Indique um link válido que comece por http:// ou https://.',
            'destination_url.max' => 'O link de destino é demasiado longo.',
            'button_text.required' => 'Indique o texto do botão.',
            'button_text.max' => 'O texto do botão não pode exceder 40 caracteres.',
            'placement.required' => 'Seleccione onde a campanha será apresentada.',
            'placement.in' => 'A posição seleccionada não é válida.',
            'starts_at.date' => 'A data de início não é válida.',
            'ends_at.date' => 'A data de fim não é válida.',
            'ends_at.after' => 'A data de fim deve ser posterior à data de início.',
            'bonus_plan_slug.required' => 'Seleccione o voucher WiFi que será oferecido.',
            'bonus_plan_slug.exists' => 'O plano de voucher seleccionado não existe.',
            'purchase_plan_slugs.required' => 'Seleccione pelo menos um plano de compra elegível.',
            'purchase_plan_slugs.array' => 'A selecção dos planos elegíveis não é válida.',
            'purchase_plan_slugs.min' => 'Seleccione pelo menos um plano de compra elegível.',
            'purchase_plan_slugs.*.distinct' => 'Não repita planos de compra elegíveis.',
            'purchase_plan_slugs.*.exists' => 'Um dos planos de compra seleccionados já não está disponível.',
        ], [
            'campaign_type' => 'tipo de campanha',
            'advertiser_name' => 'nome do anunciante',
            'title' => 'título',
            'description' => 'descrição',
            'image' => 'imagem',
            'destination_url' => 'link de destino',
            'button_text' => 'texto do botão',
            'placement' => 'posição',
            'starts_at' => 'data de início',
            'ends_at' => 'data de fim',
            'bonus_plan_slug' => 'voucher bónus',
            'purchase_plan_slugs' => 'planos de compra elegíveis',
        ]);

        $data['advertiser_name'] = $data['campaign_type'] === 'own'
            ? 'AngolaWiFi'
            : $data['advertiser_name'];
        if (empty($data['destination_url'])) {
            $data['destination_url'] = $hasBonus && ! empty($data['purchase_plan_slugs'])
                ? route('store.checkout', ['plan' => $data['purchase_plan_slugs'][0]])
                : url('/');
        }
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
