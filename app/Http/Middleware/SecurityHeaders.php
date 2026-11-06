<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // The debug error page relies on inline assets and only exists outside production.
        if (! (config('app.debug') && $response->getStatusCode() >= 500)) {
            $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $assets = "'self'";
        $connect = "'self'";

        if (Vite::isRunningHot()) {
            $hot = trim(file_get_contents(Vite::hotFile()));
            $assets .= ' '.$hot;
            $connect .= ' '.$hot.' '.preg_replace('/^http/', 'ws', $hot);
        }

        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "img-src 'self' data:",
            "font-src 'self'",
            'script-src '.$assets,
            'style-src '.$assets,
            'connect-src '.$connect,
        ]);
    }
}
