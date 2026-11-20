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
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
