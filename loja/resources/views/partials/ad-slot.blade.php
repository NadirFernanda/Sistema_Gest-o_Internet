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
      <img class="sponsored-ad__image" src="{{ route('ads.image', $campaign->id) }}" alt="{{ $campaign->title }}" loading="lazy">
      <span class="sponsored-ad__content">
        <span class="sponsored-ad__eyebrow">{{ $campaign->campaign_type === 'own' ? 'Oferta especial' : 'Conheça esta oferta' }}</span>
        <strong>{{ $campaign->title }}</strong>
        @if($campaign->description)<span>{{ $campaign->description }}</span>@endif
        <span class="sponsored-ad__button">{{ $campaign->button_text }}</span>
      </span>
    </a>
  </aside>

  @if($placement === 'home_banner')
    <div class="sponsored-popup" id="sponsoredPopup-{{ $campaign->id }}" data-popup-key="angolawifi-ad-popup-{{ $campaign->id }}" hidden>
      <div class="sponsored-popup__backdrop" data-popup-close></div>
      <section class="sponsored-popup__dialog" role="dialog" aria-modal="true" aria-labelledby="sponsoredPopupTitle-{{ $campaign->id }}">
        <button class="sponsored-popup__close" type="button" aria-label="Fechar anúncio" data-popup-close>&times;</button>
        <span class="sponsored-ad__label">{{ $campaign->campaign_type === 'own' ? 'Campanha promocional AngolaWiFi' : 'Publicidade · ' . $campaign->advertiser_name }}</span>
        <a class="sponsored-popup__link" href="{{ route('ads.click', $campaign->id) }}" @if($campaign->campaign_type === 'sponsored') rel="sponsored" @endif>
          <img class="sponsored-popup__image" src="{{ route('ads.image', $campaign->id) }}" alt="{{ $campaign->title }}" loading="eager">
          <span class="sponsored-popup__content">
            <strong id="sponsoredPopupTitle-{{ $campaign->id }}">{{ $campaign->title }}</strong>
            @if($campaign->description)<span>{{ $campaign->description }}</span>@endif
            <span class="sponsored-ad__button">{{ $campaign->button_text }}</span>
          </span>
        </a>
      </section>
    </div>
  @endif

  @pushOnce('styles')
    <style>
      .sponsored-ad{max-width:1100px;margin:1.5rem auto;padding:0 1.25rem;animation:ad-rise-in .65s cubic-bezier(.2,.75,.25,1) both}
      .sponsored-ad__label{display:block;margin-bottom:.5rem;color:#64748b;font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
      .sponsored-ad__link{position:relative;display:flex;align-items:stretch;gap:0;overflow:hidden;border:1px solid #e2e8f0;border-radius:16px;background:linear-gradient(115deg,#fff 45%,#fffbeb);text-decoration:none;color:#1a202c;box-shadow:0 10px 30px rgba(15,23,42,.08);transition:transform .25s ease,box-shadow .25s ease,border-color .25s ease}
      .sponsored-ad__link:hover{transform:translateY(-4px);border-color:#f7b500;box-shadow:0 18px 38px rgba(15,23,42,.14)}
      .sponsored-ad__image{display:block;flex:0 0 38%;width:38%;min-height:220px;max-height:270px;object-fit:cover;background:#f1f5f9}
      .sponsored-ad__content{position:relative;display:flex;flex:1;flex-direction:column;align-items:flex-start;justify-content:center;gap:.65rem;padding:clamp(1.25rem,3vw,2.25rem)}
      .sponsored-ad__eyebrow{color:#9a6700;font-size:.72rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
      .sponsored-ad__content strong{font-size:clamp(1.25rem,2.4vw,1.8rem);line-height:1.15}
      .sponsored-ad__content>span:not(.sponsored-ad__button):not(.sponsored-ad__eyebrow){color:#64748b;font-size:.95rem;line-height:1.55}
      .sponsored-ad__button{display:inline-flex;align-items:center;gap:.5rem;margin-top:.2rem;padding:.7rem 1.1rem;border-radius:8px;background:#f7b500;color:#1a202c;font-size:.88rem;font-weight:800;box-shadow:0 5px 14px rgba(247,181,0,.24);transition:transform .2s ease,background .2s ease}
      .sponsored-ad__link:hover .sponsored-ad__button{transform:translateX(3px);background:#e0a800}
      .sponsored-ad__button::after{content:"→";font-size:1.1rem}
      .sponsored-popup[hidden]{display:none}
      .sponsored-popup{position:fixed;inset:0;z-index:13000;display:grid;place-items:center;padding:1rem}
      .sponsored-popup__backdrop{position:absolute;inset:0;background:rgba(2,6,23,.68);backdrop-filter:blur(5px);animation:ad-fade-in .25s ease both}
      .sponsored-popup__dialog{position:relative;z-index:1;width:min(680px,100%);max-height:calc(100vh - 2rem);overflow:auto;padding:1.25rem;border:1px solid rgba(255,255,255,.7);border-radius:18px;background:#fff;box-shadow:0 28px 90px rgba(2,6,23,.35);animation:ad-popup-in .4s cubic-bezier(.2,.8,.2,1) both}
      .sponsored-popup__close{position:absolute;top:.65rem;right:.65rem;z-index:2;display:grid;place-items:center;width:38px;height:38px;border:1px solid #e2e8f0;border-radius:50%;background:rgba(255,255,255,.94);color:#334155;font-size:1.65rem;line-height:1;cursor:pointer;box-shadow:0 3px 12px rgba(15,23,42,.12)}
      .sponsored-popup__close:hover{background:#fffbeb;border-color:#f7b500}
      .sponsored-popup__link{display:flex;flex-direction:column;overflow:hidden;border-radius:12px;background:linear-gradient(135deg,#fff 40%,#fffbeb);color:#1a202c;text-decoration:none}
      .sponsored-popup__image{display:block;width:100%;max-height:340px;object-fit:cover;background:#f1f5f9}
      .sponsored-popup__content{display:flex;flex-direction:column;align-items:flex-start;gap:.65rem;padding:1.25rem}
      .sponsored-popup__content strong{padding-right:2rem;font-size:clamp(1.3rem,4vw,1.8rem);line-height:1.15}
      .sponsored-popup__content>span:not(.sponsored-ad__button){color:#64748b;line-height:1.55}
      @keyframes ad-rise-in{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
      @keyframes ad-fade-in{from{opacity:0}to{opacity:1}}
      @keyframes ad-popup-in{from{opacity:0;transform:translateY(22px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
      @media(max-width:600px){.sponsored-ad__link{flex-direction:column}.sponsored-ad__image{flex:auto;width:100%;min-height:0;max-height:220px;aspect-ratio:16/9}.sponsored-ad__content{padding:1rem}.sponsored-popup__dialog{padding:.75rem;border-radius:14px}.sponsored-popup__image{max-height:38vh}.sponsored-popup__content{padding:1rem}}
      @media(prefers-reduced-motion:reduce){.sponsored-ad,.sponsored-popup__backdrop,.sponsored-popup__dialog{animation:none!important}.sponsored-ad__link,.sponsored-ad__button{transition:none!important}}
    </style>
  @endPushOnce

  @pushOnce('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var slots = document.querySelectorAll('.sponsored-ad[data-impression-url]');
        function recordImpression(slot) {
          var csrf = document.querySelector('meta[name="csrf-token"]');
          if (!csrf) return;
          fetch(slot.dataset.impressionUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrf.content
            },
            body: JSON.stringify({ placement: slot.dataset.placement }),
            credentials: 'same-origin',
            keepalive: true
          });
        }
        if ('IntersectionObserver' in window) {
          var observer = new IntersectionObserver(function (entries, currentObserver) {
            entries.forEach(function (entry) {
              if (!entry.isIntersecting) return;
              currentObserver.unobserve(entry.target);
              recordImpression(entry.target);
            });
          }, { threshold: 0.5 });
          slots.forEach(function (slot) { observer.observe(slot); });
        }

        document.querySelectorAll('.sponsored-popup').forEach(function (popup) {
          var storageKey = popup.dataset.popupKey;
          try {
            if (window.sessionStorage.getItem(storageKey)) return;
          } catch (error) {
            console.warn('Não foi possível verificar se a campanha já foi apresentada nesta sessão.', error);
            return;
          }

          var closeButton = popup.querySelector('.sponsored-popup__close');
          var previousFocus = document.activeElement;
          var closePopup = function () {
            popup.hidden = true;
            document.body.style.overflow = '';
            if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
          };

          popup.querySelectorAll('[data-popup-close]').forEach(function (control) {
            control.addEventListener('click', closePopup);
          });
          document.addEventListener('keydown', function (event) {
            if (popup.hidden) return;
            if (event.key === 'Escape') {
              closePopup();
              return;
            }
            if (event.key === 'Tab') {
              event.preventDefault();
              var focusTarget = event.shiftKey ? popup.querySelector('.sponsored-popup__close') : popup.querySelector('.sponsored-popup__link');
              focusTarget.focus();
            }
          });

          window.setTimeout(function () {
            try {
              window.sessionStorage.setItem(storageKey, 'shown');
            } catch (error) {
              console.warn('Não foi possível guardar a apresentação da campanha nesta sessão.', error);
            }
            popup.hidden = false;
            document.body.style.overflow = 'hidden';
            closeButton.focus();
            var slot = document.querySelector('.sponsored-ad[data-impression-url]');
            if (slot) recordImpression(slot);
          }, 8000);
        });
      });
    </script>
  @endPushOnce
@endif
