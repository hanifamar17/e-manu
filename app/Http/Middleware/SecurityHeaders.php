<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $h = $response->headers;

        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $h->set('X-Robots-Tag', 'noindex, nofollow');

        if (! $h->has('Referrer-Policy')) {
            $path = $request->is('*surat/*', '*acc-link/*', '*ringkasan/*', '*layar/*');
            $h->set('Referrer-Policy', $path ? 'no-referrer' : 'strict-origin-when-cross-origin');
        }

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=86400');
        }

        return $response;
    }
}