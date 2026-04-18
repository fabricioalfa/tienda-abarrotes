<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        return $response->withHeaders([
            // Anti-caché / anti-bfcache
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0, private',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',

            // Evita que la app sea embebida en iframes (clickjacking)
            'X-Frame-Options' => 'DENY',

            // Evita que el navegador detecte automáticamente el tipo MIME (MIME sniffing)
            'X-Content-Type-Options' => 'nosniff',

            // Activa el filtro XSS del navegador (legacy, pero sin daño)
            'X-XSS-Protection' => '1; mode=block',

            // Controla qué información se envía en el Referer header
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // Política de permisos: deshabilitar APIs no usadas
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',

            // Content Security Policy — ajustada para Vite + Alpine.js + fonts
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'",   // Alpine.js requiere unsafe-eval para expresiones
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
                "font-src 'self' https://fonts.bunny.net",
                "img-src 'self' data:",
                "connect-src 'self'",
                "frame-ancestors 'none'",               // refuerza X-Frame-Options
                "base-uri 'self'",
                "form-action 'self'",
            ]),
        ]);
    }
}
