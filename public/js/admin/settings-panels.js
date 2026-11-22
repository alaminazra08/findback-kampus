(() => {
  'use strict';
  function init() {
    const root = document.querySelector('.fb-settings');
    if (!root || root.dataset.panelsReady) return;
    root.dataset.panelsReady = '1';
    const panels = [...root.querySelectorAll('details[name="fb-settings-sections"]')];
    const panelKey = 'findback.admin.settings.open-panel';
    const panelId = (panel) => panel?.getAttribute('aria-labelledby') || '';
    let storedPanel = null;
    try {
      storedPanel = sessionStorage.getItem(panelKey);
    } catch {
      // Storage may be unavailable in a restricted browser.
    }

    const initialPanel = storedPanel === null || storedPanel === 'appearance-title'
      ? 'general-title'
      : storedPanel;
    const active = panels.find((panel) => panelId(panel) === initialPanel);
    if (active) active.open = true;

    panels.forEach((panel) => {
      panel.addEventListener('toggle', () => {
        if (!root.isConnected) return;
        try {
          sessionStorage.setItem(panelKey, panelId(panels.find((item) => item.open)));
        } catch {
          // The accordion remains usable when storage is unavailable.
        }
      });
    });
  }
  init();
  document.addEventListener('fb:admin-page', init);
})();
