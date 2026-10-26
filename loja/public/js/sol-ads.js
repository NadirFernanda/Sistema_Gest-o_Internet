(() => {
  const campaignSlots = document.querySelectorAll('.campaign[data-impression-url]');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, currentObserver) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting || entry.intersectionRatio < 0.5) {
          return;
        }

        currentObserver.unobserve(entry.target);
        const storageKey = `sol-ad-impression:${entry.target.dataset.campaignId}`;
        try {
          const lastImpression = Number(window.sessionStorage.getItem(storageKey));
          if (Number.isFinite(lastImpression) && lastImpression >= Date.now() - 30 * 60 * 1000) {
            return;
          }
        } catch {
          // Third-party storage can be unavailable; server-side limits still apply.
        }

        const beacon = new Image();
        beacon.onload = () => {
          try {
            window.sessionStorage.setItem(storageKey, String(Date.now()));
          } catch {
            // Third-party storage can be unavailable; server-side limits still apply.
          }
        };
        beacon.src = entry.target.dataset.impressionUrl;
        entry.target.impressionBeacon = beacon;
      });
    }, { threshold: 0.5 });

    campaignSlots.forEach((slot) => observer.observe(slot));
  }

  const button = document.getElementById('open-store');
  const fallback = document.getElementById('store-fallback-link');
  const storeUrl = 'https://angolawifi.ao/';

  if (!button || !fallback) {
    return;
  }

  const navigateTopLevel = () => {
    window.top.location.href = storeUrl;
  };

  button.addEventListener('click', () => {
    let opened;

    try {
      opened = window.open(storeUrl, '_blank');
    } catch {
      navigateTopLevel();
      return;
    }

    if (!opened) {
      navigateTopLevel();
      return;
    }

    opened.opener = null;
  });
})();
