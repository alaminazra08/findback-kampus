(function () {
    'use strict';
    var settings = document.currentScript.dataset;
    var key = 'findback.admin.tab.authorized.v1';
    var valid = false;
    try { valid = sessionStorage.getItem(key) === '1'; } catch (e) {}
    if (valid) return;
    // New tabs are not authorized just because another tab has a valid Laravel cookie.
    // Do not expose the admin page while logging out.
    var css = document.createElement('style');
    css.textContent = 'html{visibility:hidden!important}';
    document.head.appendChild(css);
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = settings.logout;
    var token = document.createElement('input');
    token.type = 'hidden'; token.name = '_token'; token.value = settings.token;
    form.appendChild(token);
    document.addEventListener('DOMContentLoaded', function () {
        document.body.appendChild(form);
        form.submit();
    }, { once: true });
})();
