<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminTabSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!$request->isMethod('GET') || !$response->isSuccessful() || !str_contains(strtolower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return $response;
        }
        $html = $response->getContent();
        if (!is_string($html) || stripos($html, '</head>') === false) {
            return $response;
        }
        $csrf = json_encode(csrf_token(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $logout = json_encode(route('logout'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        $script = <<<'JS'
<script data-findback-tab-guard>
(function () {
    'use strict';
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
    form.action = __LOGOUT__;
    var token = document.createElement('input');
    token.type = 'hidden'; token.name = '_token'; token.value = __CSRF__;
    form.appendChild(token);
    document.documentElement.appendChild(form);
    form.submit();
})();
</script>
JS;
        $script = str_replace(['__LOGOUT__', '__CSRF__'], [$logout, $csrf], $script);
        $response->setContent(preg_replace('~</head>~i', $script . "\n</head>", $html, 1));
        $response->headers->remove('Content-Length');
        $response->headers->set('Cache-Control', 'no-store, private');
        return $response;
    }
}
