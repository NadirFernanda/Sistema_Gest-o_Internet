@extends('layouts.app')

@section('title', ($campaign->exists ? 'Editar' : 'Nova') . ' campanha — Admin')

@section('content')
<style>
.ads-admin{background:#f4f6f9;min-height:60vh;padding:2rem 0 4rem;color:#1a202c;font-family:Inter,system-ui,sans-serif}.ads-wrap{max-width:760px;margin:auto;padding:0 1.25rem}.ads-head{display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem}.ads-head h1{margin:0;font-size:1.4rem}.ads-sub{margin:.25rem 0 0;color:#64748b;font-size:.85rem}.ads-card{padding:1.25rem;background:white;border:1px solid #dde2ea;border-radius:10px}.ads-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.ads-field{min-width:0}.ads-full{grid-column:1/-1}.ads-label{display:block;margin-bottom:.3rem;font-size:.8rem;font-weight:700}.ads-control{width:100%;box-sizing:border-box;padding:.58rem .7rem;border:1px solid #cbd5e1;border-radius:7px;background:#fff;font:inherit;font-size:.86rem}.ads-control:focus{outline:2px solid rgba(247,181,0,.3);border-color:#f7b500}.ads-control option:checked{background:#f7b500;color:#1a202c}.ads-check{display:flex;align-items:center;gap:.55rem;font-size:.86rem;font-weight:700}.ads-error{color:#b91c1c;font-size:.78rem;margin:.2rem 0 0}.ads-actions{display:flex;justify-content:flex-end;gap:.6rem;margin-top:1.2rem;padding-top:1rem;border-top:1px solid #eef1f5}.ads-btn{display:inline-block;border:0;border-radius:7px;padding:.55rem .9rem;background:#f7b500;color:#1a202c;text-decoration:none;font-weight:700;font-size:.84rem;cursor:pointer}.ads-muted{background:#e2e8f0}.ads-help{grid-column:1/-1;margin:0;padding:.75rem;background:#fffbeb;border:1px solid #fde68a;border-radius:7px;color:#78350f;font-size:.82rem;line-height:1.5}.ads-plan-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:.5rem}.ads-plan-option{display:flex;align-items:center;gap:.5rem;padding:.55rem;border:1px solid #e2e8f0;border-radius:7px;font-size:.84rem}.ads-plan-list input{accent-color:#f7b500}@media(max-width:600px){.ads-grid{grid-template-columns:1fr}.ads-full,.ads-help{grid-column:auto}}
.ads-file-picker{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap}.ads-file-native{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}.ads-file-native:focus-visible+.ads-file-button{outline:2px solid #f7b500;outline-offset:2px}.ads-file-button{display:inline-block;padding:.55rem .85rem;border-radius:7px;background:#f7b500;color:#1a202c;font-size:.84rem;font-weight:700;cursor:pointer}.ads-file-name{color:#64748b;font-size:.82rem}.ads-field[hidden]{display:none}
</style>
<div class="ads-admin"><div class="ads-wrap">
  <header class="ads-head"><div><h1>{{ $campaign->exists ? 'Editar campanha' : 'Nova campanha' }}</h1><p class="ads-sub">Escolha campanha própria gratuita ou publicidade de anunciante.</p></div>
    <a class="ads-btn ads-muted" href="{{ route('admin.ads.index') }}">Voltar</a></header>
  @if($errors->any())<div class="ads-card" role="alert" style="margin-bottom:1rem;color:#991b1b"><strong>Verifique os seguintes campos:</strong><ul style="padding-left:1.2rem;margin:.5rem 0 0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form method="POST" enctype="multipart/form-data" action="{{ $campaign->exists ? route('admin.ads.update', $campaign->id) : route('admin.ads.store') }}" class="ads-card">
    @csrf @if($campaign->exists) @method('PUT') @endif
    <div class="ads-grid">
      <div class="ads-field ads-full"><label class="ads-label" for="campaign_type">Tipo de campanha *</label><select class="ads-control" id="campaign_type" name="campaign_type" required>@foreach(\App\Models\AdCampaign::TYPES as $key => $label)<option value="{{ $key }}" @selected(old('campaign_type', $campaign->campaign_type ?: 'sponsored') === $key)>{{ $label }}</option>@endforeach</select></div>
      <p class="ads-help ads-full" id="campaign_type_help">Campanhas próprias são gratuitas e divulgam ofertas da AngolaWiFi, como um bónus associado à compra de um plano. Publicidade de anunciantes é para empresas externas.</p>
      <div class="ads-field ads-full" id="advertiser_field"><label class="ads-label" for="advertiser_name">Nome do anunciante *</label><input class="ads-control" id="advertiser_name" name="advertiser_name" maxlength="120" value="{{ old('advertiser_name', $campaign->advertiser_name) }}"></div>
      <div class="ads-field"><label class="ads-label" for="placement">Posição *</label><select class="ads-control" id="placement" name="placement" required>@foreach(\App\Models\AdCampaign::PLACEMENTS as $key => $label)<option value="{{ $key }}" @selected(old('placement', $campaign->placement) === $key)>{{ $label }}</option>@endforeach</select></div>
      <div class="ads-field ads-full"><label class="ads-label" for="title">Título *</label><input class="ads-control" id="title" name="title" maxlength="160" required value="{{ old('title', $campaign->title) }}"></div>
      <div class="ads-field ads-full"><label class="ads-label" for="description">Descrição</label><textarea class="ads-control" id="description" name="description" maxlength="500" rows="3">{{ old('description', $campaign->description) }}</textarea></div>
      <div class="ads-field ads-full" id="description_suggestion" hidden><p class="ads-help" style="margin-bottom:.5rem" id="description_suggestion_text" aria-live="polite"></p><button class="ads-btn ads-muted" type="button" id="use_description_suggestion">Usar descrição sugerida</button></div>
      <div class="ads-field ads-full" id="bonus_toggle_field">
        <label class="ads-check"><input type="checkbox" id="has_bonus" name="has_bonus" value="1" @checked(old('has_bonus', $campaign->bonus_plan_slug ? '1' : ''))> A campanha oferece um voucher WiFi gratuito após uma compra</label>
      </div>
      <div class="ads-field ads-full" id="bonus_fields">
        <label class="ads-label">Planos de compra elegíveis *</label>
        <div class="ads-plan-list">
          @php $selectedPurchasePlans = old('purchase_plan_slugs', $campaign->qualifyingPlans->pluck('slug')->all()); @endphp
          @foreach($voucherPlans as $voucherPlan)
            <label class="ads-plan-option"><input type="checkbox" name="purchase_plan_slugs[]" value="{{ $voucherPlan->slug }}" data-plan-name="{{ $voucherPlan->name }}" @checked(in_array($voucherPlan->slug, $selectedPurchasePlans, true))> {{ $voucherPlan->name }}</label>
          @endforeach
        </div>
        <label class="ads-label" for="bonus_plan_slug" style="margin-top:.85rem">Voucher WiFi gratuito a entregar *</label>
        <select class="ads-control" id="bonus_plan_slug" name="bonus_plan_slug">
          <option value="">Escolha o plano de bónus</option>
          @foreach($voucherPlans as $voucherPlan)
            <option value="{{ $voucherPlan->slug }}" data-plan-name="{{ $voucherPlan->name }}" data-validity="{{ $voucherPlan->validity_label }}" @selected(old('bonus_plan_slug', $campaign->bonus_plan_slug) === $voucherPlan->slug)>{{ $voucherPlan->name }} — {{ $voucherPlan->validity_label }}</option>
          @endforeach
        </select>
        <p class="ads-help" style="margin-top:.5rem">Ao confirmar o pagamento de um plano elegível, o cliente recebe também um segundo código WiFi do plano de bónus. É necessário ter códigos desse plano em stock.</p>
      </div>
      <div class="ads-field ads-full"><span class="ads-label">Imagem {{ $campaign->exists ? '(opcional para manter a actual)' : '*' }}</span><div class="ads-file-picker"><input class="ads-file-native" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="image_file_name image_help"><label class="ads-file-button" for="image">Escolher ficheiro</label><span class="ads-file-name" id="image_file_name" aria-live="polite">{{ $campaign->exists ? 'Imagem actual: ' . basename($campaign->image_path) : 'Nenhum ficheiro seleccionado' }}</span></div><small class="ads-sub" id="image_help">Formatos aceites: JPG, PNG ou WebP. Tamanho máximo: 5 MB.</small></div>
      <div class="ads-field ads-full" id="destination_field"><label class="ads-label" for="destination_url">Link de destino <span id="destination_required">*</span></label><input class="ads-control" id="destination_url" name="destination_url" type="url" maxlength="2048" value="{{ old('destination_url', $campaign->destination_url) }}"><small class="ads-sub" id="destination_help">Obrigatório para anúncios de empresas externas.</small></div>
      <div class="ads-field"><label class="ads-label" for="button_text">Texto do botão *</label><input class="ads-control" id="button_text" name="button_text" maxlength="40" required value="{{ old('button_text', $campaign->button_text ?: 'Saber mais') }}"></div>
      <div class="ads-field"><label class="ads-label" for="starts_at">Início da campanha</label><input class="ads-control" id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $campaign->starts_at?->format('Y-m-d\TH:i')) }}"></div>
      <div class="ads-field"><label class="ads-label" for="ends_at">Fim da campanha</label><input class="ads-control" id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $campaign->ends_at?->format('Y-m-d\TH:i')) }}"></div>
      <div class="ads-field ads-full"><label class="ads-check"><input type="checkbox" name="active" value="1" @checked(old('active', $campaign->exists ? $campaign->active : false))> Campanha activa</label></div>
    </div>
    <div class="ads-actions"><a class="ads-btn ads-muted" href="{{ route('admin.ads.index') }}">Cancelar</a><button class="ads-btn" type="submit">Guardar campanha</button></div>
  </form>
</div></div>
<script>
  (function () {
    var type = document.getElementById('campaign_type');
    var advertiser = document.getElementById('advertiser_field');
    var input = document.getElementById('advertiser_name');
    var hasBonus = document.getElementById('has_bonus');
    var bonusToggle = document.getElementById('bonus_toggle_field');
    var bonusFields = document.getElementById('bonus_fields');
    var bonusPlan = document.getElementById('bonus_plan_slug');
    var purchasePlans = document.querySelectorAll('input[name="purchase_plan_slugs[]"]');
    var description = document.getElementById('description');
    var descriptionSuggestion = document.getElementById('description_suggestion');
    var descriptionSuggestionText = document.getElementById('description_suggestion_text');
    var useDescriptionSuggestion = document.getElementById('use_description_suggestion');
    var generatedDescription = null;
    var descriptionEdited = description.value.trim() !== '';
    var destination = document.getElementById('destination_url');
    var destinationRequired = document.getElementById('destination_required');
    var destinationHelp = document.getElementById('destination_help');
    var imageInput = document.getElementById('image');
    var imageName = document.getElementById('image_file_name');
    var imageExistingName = @json($campaign->exists ? basename($campaign->image_path) : null);
    function planName(value) {
      return value.replace(/^Plano\s+/i, '').trim();
    }
    function buildDescriptionSuggestion() {
      var selectedPlans = Array.from(purchasePlans)
        .filter(function (plan) { return plan.checked; })
        .map(function (plan) { return planName(plan.dataset.planName || ''); });
      var selectedBonus = bonusPlan.options[bonusPlan.selectedIndex];
      if (!selectedPlans.length || !selectedBonus || !selectedBonus.value) return '';

      var purchaseNames = selectedPlans.length > 1
        ? selectedPlans.slice(0, -1).join(', ') + ' ou ' + selectedPlans[selectedPlans.length - 1]
        : selectedPlans[0];
      var bonusName = planName(selectedBonus.dataset.planName || '');
      var validity = selectedBonus.dataset.validity || '';
      return 'Na compra de um plano ' + purchaseNames + ', recebe um voucher WiFi ' + bonusName +
        (validity ? ' de ' + validity : '') + '.';
    }
    function updateDescriptionSuggestion() {
      var suggestion = buildDescriptionSuggestion();
      var visible = type.value !== 'sponsored' && hasBonus.checked && Boolean(suggestion);
      descriptionSuggestion.hidden = !visible;
      if (!visible) return;

      descriptionSuggestionText.textContent = 'Sugestão: ' + suggestion;
      if (!descriptionEdited || description.value === generatedDescription) {
        description.value = suggestion;
        generatedDescription = suggestion;
      }
    }
    description.addEventListener('input', function () {
      if (description.value !== generatedDescription) descriptionEdited = true;
    });
    useDescriptionSuggestion.addEventListener('click', function () {
      var suggestion = buildDescriptionSuggestion();
      if (!suggestion) return;
      description.value = suggestion;
      generatedDescription = suggestion;
      descriptionEdited = false;
    });
    purchasePlans.forEach(function (plan) {
      plan.addEventListener('change', updateDescriptionSuggestion);
    });
    bonusPlan.addEventListener('change', updateDescriptionSuggestion);
    function updateTypeFields() {
      var isSponsored = type.value === 'sponsored';
      advertiser.hidden = !isSponsored;
      input.required = isSponsored;
      input.disabled = !isSponsored;
      bonusToggle.hidden = isSponsored;
      if (isSponsored) hasBonus.checked = false;
      var showBonus = !isSponsored && hasBonus.checked;
      bonusFields.hidden = !showBonus;
      bonusPlan.required = showBonus;
      bonusPlan.disabled = !showBonus;
      purchasePlans.forEach(function (plan) { plan.disabled = !showBonus; });
      destination.required = isSponsored;
      destinationRequired.hidden = !isSponsored;
      destinationHelp.textContent = isSponsored
        ? 'Obrigatório para anúncios de empresas externas.'
        : (showBonus
          ? 'Opcional. Se ficar vazio, o botão abrirá o checkout do primeiro plano elegível.'
          : 'Opcional. Se ficar vazio, o botão abrirá a página inicial da AngolaWiFi.');
      updateDescriptionSuggestion();
    }
    type.addEventListener('change', updateTypeFields);
    hasBonus.addEventListener('change', updateTypeFields);
    imageInput.addEventListener('change', function () {
      imageName.textContent = imageInput.files.length
        ? imageInput.files[0].name
        : (imageExistingName ? 'Imagem actual: ' + imageExistingName : 'Nenhum ficheiro seleccionado');
    });
    updateTypeFields();
  })();
</script>
@endsection
