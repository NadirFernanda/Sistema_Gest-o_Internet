@php
  $campaigns = \App\Models\AdCampaign::available($placement)->with('bonusPlan')->get()
    ->filter(function ($candidate) {
      return $candidate->campaign_type !== 'own'
        || ! $candidate->bonus_plan_slug
        || ($candidate->bonusPlan?->active
          && \App\Models\WifiCode::where('plan_id', $candidate->bonus_plan_slug)
            ->where('status', \App\Models\WifiCode::STATUS_AVAILABLE)
            ->whereNull('reseller_purchase_id')
            ->whereNull('autovenda_order_id')
            ->exists());
    });
  $campaign = $campaigns->isEmpty() ? null : $campaigns->random();
@endphp

@if($campaign)
  <aside class="sponsored-ad" aria-label="{{ $campaign->campaign_type === 'own' ? 'Campanha promocional AngolaWiFi' : 'Publicidade' }}"
         data-impression-url="{{ route('ads.impression', $campaign->id) }}"
         data-placement="{{ $placement }}">
    <span class="sponsored-ad__label">{{ $campaign->campaign_type === 'own' ? 'Campanha promocional AngolaWiFi' : 'Publicidade · ' . $campaign->advertiser_name }}</span>
    <a class="sponsored-ad__link" href="{{ route('ads.click', $campaign->id) }}" @if($campaign->campaign_type === 'sponsored') rel="sponsored" @endif>
      <img class="sponsored-ad__image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($campaign->image_path) }}" alt="{{ $campaign->title }}" loading="lazy">
      <span class="sponsored-ad__content">
        <strong>{{ $campaign->title }}</strong>
        @if($campaign->description)<span>{{ $campaign->description }}</span>@endif
        <span class="sponsored-ad__button">{{ $campaign->button_text }}</span>
      </span>
    </a>
  </aside>

  @pushOnce('styles')
    <style>
      .sponsored-ad{max-width:1100px;margin:1.5rem auto;padding:0 1.25rem}
      .sponsored-ad__label{display:block;margin-bottom:.4rem;color:#64748b;font-size:.72rem}
      .sponsored-ad__link{display:flex;align-items:center;gap:1rem;overflow:hidden;border:1px solid #e2e8f0;border-radius:12px;background:#fff;text-decoration:none;color:#1a202c}
      .sponsored-ad__image{width:30%;max-height:180px;object-fit:cover}
      .sponsored-ad__content{display:flex;flex:1;flex-direction:column;align-items:flex-start;gap:.4rem;padding:1rem}
      .sponsored-ad__content strong{font-size:1.05rem}
      .sponsored-ad__content>span:not(.sponsored-ad__button){color:#64748b;font-size:.9rem}
      .sponsored-ad__button{display:inline-block;margin-top:.2rem;padding:.45rem .8rem;border-radius:6px;background:#f7b500;color:#1a202c;font-size:.82rem;font-weight:700}
      @media(max-width:600px){.sponsored-ad__link{align-items:stretch;flex-direction:column;gap:0}.sponsored-ad__image{width:100%;max-height:200px}.sponsored-ad__content{padding:.85rem}}
    </style>
  @endPushOnce

  @pushOnce('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var slots = document.querySelectorAll('.sponsored-ad[data-impression-url]');
        if (!('IntersectionObserver' in window)) return;
        var observer = new IntersectionObserver(function (entries, currentObserver) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            currentObserver.unobserve(entry.target);
            fetch(entry.target.dataset.impressionUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({ placement: entry.target.dataset.placement }),
              credentials: 'same-origin',
              keepalive: true
            });
          });
        }, { threshold: 0.5 });
        slots.forEach(function (slot) { observer.observe(slot); });
      });
    </script>
  @endPushOnce
@endif
