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

  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealItems = document.querySelectorAll('.campaign, .plans-heading, .plan, .empty-state');

  if (!reduceMotion && 'IntersectionObserver' in window && revealItems.length) {
    document.documentElement.classList.add('motion-ready');

    const revealObserver = new IntersectionObserver((entries, currentObserver) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        entry.target.classList.add('is-visible');
        currentObserver.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -24px 0px' });

    revealItems.forEach((item) => {
      item.classList.add('reveal-on-scroll');
      revealObserver.observe(item);
    });
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
