(() => {
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
