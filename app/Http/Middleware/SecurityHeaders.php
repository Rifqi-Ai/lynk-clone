<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Add security headers to every response.
     *
     * - X-Frame-Options: prevent clickjacking (no embedding in iframes)
     * - X-Content-Type-Options: prevent MIME sniffing
     * - Referrer-Policy: don't leak full URLs to external sites
     * - Permissions-Policy: disable unused browser features
     * - Content-Security-Policy: defense-in-depth against XSS (allows our assets + Google OAuth)
     * - Strict-Transport-Security: force HTTPS (production only)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Content-Security-Policy (CSP) — XSS defense in depth
        //
        // Policy rationale:
        // - default-src 'self': only allow resources from same origin by default
        // - script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://accounts.google.com:
        //   - 'unsafe-inline' is required for Vite-built inline scripts (NUXT/Vue pattern)
        //   - jsdelivr for heroicons/blade-ui-kit if used externally
        //   - accounts.google.com for OAuth callback
        // - style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net:
        //   - 'unsafe-inline' for Tailwind utility classes in style attributes
        //   - Google Fonts + Bunny Fonts (privacy-respecting alternative)
        // - font-src: Bunny Fonts CDN
        // - img-src 'self' data: https: — allows product thumbnails + avatar URLs
        // - connect-src 'self' https://cdn.jsdelivr.net — for Vite HMR in dev
        // - frame-src https://accounts.google.com https://www.google.com — Google OAuth popup
        // - form-action 'self' https://accounts.google.com — OAuth form posts
        // - object-src 'none' — disable Flash/Java applets
        // - base-uri 'self' — prevent <base> tag injection
        // - frame-ancestors 'self' — equivalent to X-Frame-Options SAMEORIGIN
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://accounts.google.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
            "img-src 'self' data: https: blob:",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "connect-src 'self' https://cdn.jsdelivr.net",
            'frame-src https://accounts.google.com https://www.google.com',
            "form-action 'self' https://accounts.google.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            'upgrade-insecure-requests',
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS only over HTTPS (production)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
