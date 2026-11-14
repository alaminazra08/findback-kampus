(function () {
  'use strict';
  function mount(signal, initialLoad) {
    const storageKey = 'fb.admin.matching.lastFilter.v2';
    const form = document.querySelector('main[data-fb-nav-page="matching"] .match-filters');
    if (!form) return;
    const path = new URL(form.action).pathname;
    if (location.pathname !== path) return;
    const reset = form.querySelector('.match-reset-curved');
    const list = document.querySelector('.match-wrap .match-table-scroll');
    if (!reset || !list) return;
    const getStored = () => { try { return sessionStorage.getItem(storageKey); } catch (_) { return null; } };
    const store = (value) => { try { if (value) sessionStorage.setItem(storageKey, value); else sessionStorage.removeItem(storageKey); } catch (_) {} };
    const initial = new URL(location.href);
    const q = initial.searchParams.get('q') || '';
    const status = initial.searchParams.get('status') || 'all';
    const hasFilter = q.trim() !== '' || (status !== 'all' && status !== '');
    const isBare = initial.search === '';
    // Preserve the last filter only while this browser tab remains open.
    if (initialLoad && isBare && getStored()) {
      const previous = getStored();
      try {
        const u = new URL(previous, location.origin);
        if (u.origin === location.origin && u.pathname === path && u.search) {
          location.replace(u.href);
          return;
        }
      } catch (_) {}
    }
    if (hasFilter) store(initial.pathname + initial.search);
    else if (isBare) store(null);
    let request = 0;
    let controller = null;
    signal.addEventListener('abort', () => {
      request++;
      controller?.abort();
    }, { once: true });
    function queryUrl() {
      const url = new URL(form.action, location.origin);
      const input = form.querySelector('[name="q"]');
      const select = form.querySelector('[name="status"]');
      const term = input ? input.value.trim() : '';
      const st = select ? select.value : 'all';
      if (term) url.searchParams.set('q', term);
      if (st && st !== 'all') url.searchParams.set('status', st);
      return url;
    }
    async function navigate(url, mode) {
      const serial = ++request;
      if (controller) controller.abort();
      controller = new AbortController();
      const y = window.scrollY;
      const x = window.scrollX;
      form.setAttribute('aria-busy', 'true');
      try {
        const response = await fetch(url.href, {
          credentials: 'same-origin',
          headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html'},
          cache: 'no-store', signal: controller.signal
        });
        if (!response.ok || response.redirected || new URL(response.url).pathname !== path) throw Error('Invalid response');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const incoming = doc.querySelector('.match-wrap .match-table-scroll');
        const current = document.querySelector('.match-wrap .match-table-scroll');
        if (!incoming || !current) throw Error('Missing matching table');
        if (serial !== request) return;
        current.replaceWith(document.importNode(incoming, true));
        const oldPage = document.querySelector('.match-wrap .match-pagination');
        const newPage = doc.querySelector('.match-wrap .match-pagination');
        if (oldPage && newPage) oldPage.replaceWith(document.importNode(newPage, true));
        else if (oldPage && !newPage) oldPage.remove();
        else if (!oldPage && newPage) document.querySelector('.match-panel').appendChild(document.importNode(newPage, true));
        const query = url.searchParams.get('q') || '';
        const status = url.searchParams.get('status') || 'all';
        const input = form.querySelector('[name="q"]');
        const select = form.querySelector('[name="status"]');
        if (input) input.value = query;
        if (select) select.value = status;
        if (mode === 'push') history.pushState({fbMatchingFilter:true}, '', url.pathname + url.search);
        const active = query.trim() || (status !== 'all' && status !== '');
        store(active ? url.pathname + url.search : null);
        window.scrollTo(x, y);
      } catch (error) {
        if (error.name === 'AbortError' || serial !== request) return;
        // Safe fallback: full navigation, with scroll restored after load.
        try { sessionStorage.setItem('fb.matching.scroll.once', String(y)); } catch (_) {}
        location.assign(url.href);
      } finally {
        if (serial === request) form.removeAttribute('aria-busy');
      }
    }
    form.addEventListener('submit', function (event) {
      if (event.defaultPrevented) return;
      event.preventDefault();
      navigate(queryUrl(), 'push');
    }, { signal });
    reset.addEventListener('click', function (event) {
      if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      store(null);
      navigate(new URL(form.action), 'push');
    }, { signal });
    window.addEventListener('popstate', function () {
      if (location.pathname === path) navigate(new URL(location.href), 'none');
    }, { signal });
    try {
      const once = sessionStorage.getItem('fb.matching.scroll.once');
      if (once !== null) {
        sessionStorage.removeItem('fb.matching.scroll.once');
        requestAnimationFrame(() => requestAnimationFrame(() => !signal.aborted && window.scrollTo(window.scrollX, Number(once) || 0)));
      }
    } catch (_) {}
  }

  function mountFeedback(signal) {
    const toast = document.querySelector('[data-fb-matching-toast]');
    if (toast) {
      const dismiss = () => toast.remove();
      const timer = toast.classList.contains('is-error') ? null : setTimeout(dismiss, 5000);
      signal.addEventListener('abort', () => clearTimeout(timer), { once: true });
      toast.querySelector('[data-fb-matching-toast-close]').addEventListener('click', () => {
        clearTimeout(timer);
        dismiss();
      }, { signal });
    }

    const form = document.querySelector('form[data-fb-match-generate]');
    const dialog = document.getElementById('fb-match-confirm');
    if (!form || !dialog?.showModal || !form.requestSubmit) return;
    let approved = false;
    let sent = false;
    let submitter;
    const button = form.querySelector('button[type="submit"]');
    const label = button.querySelector('span');
    const originalLabel = label.textContent;
    form.addEventListener('submit', event => {
      if (event.defaultPrevented) return;
      if (sent) {
        event.preventDefault();
        return;
      }
      if (approved) {
        queueMicrotask(() => {
          if (event.defaultPrevented || signal.aborted) return;
          sent = true;
          button.disabled = true;
          label.textContent = 'Memproses…';
        });
        return;
      }
      event.preventDefault();
      if (dialog.open) return;
      submitter = event.submitter;
      dialog.showModal();
    }, { signal });
    dialog.querySelector('[data-fb-match-accept]').addEventListener('click', () => {
      if (!dialog.open || sent) return;
      dialog.close();
      approved = true;
      try { form.requestSubmit(submitter || undefined); }
      finally { approved = false; }
    }, { signal });
    window.addEventListener('pageshow', event => {
      if (!event.persisted) return;
      sent = false;
      button.disabled = false;
      label.textContent = originalLabel;
    }, { signal });
  }

  let scope;
  function render(initialLoad = false) {
    scope?.abort();
    scope = new AbortController();
    mount(scope.signal, initialLoad);
    mountFeedback(scope.signal);
  }

  document.addEventListener('fb:admin-page', () => render());
  render(true);
})();
