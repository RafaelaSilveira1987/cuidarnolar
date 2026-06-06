<?php

namespace App\Core;

class SecurityHeaders
{
    public static function send(): void
    {
        if (headers_sent()) {
            return;
        }

        $https = self::isHttps();
        $csp = trim((string)env('SECURITY_CSP', self::defaultCsp()));

        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('X-Robots-Tag: noindex, nofollow', false);
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Cross-Origin-Resource-Policy: same-origin');
        header('Cross-Origin-Opener-Policy: same-origin');

        if ($csp !== '') {
            header('Content-Security-Policy: ' . $csp);
        }

        if ($https && filter_var(env('SECURITY_HSTS', 'true'), FILTER_VALIDATE_BOOLEAN)) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    }

    private static function defaultCsp(): string
{
    return "default-src 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'self'; "
        . "object-src 'none'; "
        . "img-src 'self' data: blob:; "
        . "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "connect-src 'self' https://cdn.jsdelivr.net; "
        . "upgrade-insecure-requests";
}
}