<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = self::secureCookieEnabled();
        $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;
        $sameSite = self::sameSite();
        $sessionPath = BASE_PATH . '/storage/sessions';

        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0775, true);
        }

        session_save_path($sessionPath);

        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $secure ? '1' : '0');
        ini_set('session.cookie_samesite', $sameSite);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite,
        ]);

        session_start();

        self::enforceIdleTimeout();

        if (!isset($_SESSION['_last_regeneration']) || time() - $_SESSION['_last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }

        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
            $_SESSION['_last_activity'] = time();
        }
    }

    public static function destroy(): void
    {
        session_unset();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flashes'][$type] = $message;
    }

    public static function getFlashes(): array
    {
        $flashes = $_SESSION['_flashes'] ?? [];
        unset($_SESSION['_flashes']);
        return $flashes;
    }

    public static function getCsrfToken(): string
    {
        return $_SESSION['_csrf_token'] ?? '';
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user') && !empty(self::get('user')['id']);
    }

    public static function user(): array
    {
        return self::get('user', []);
    }

    public static function isAdmin(): bool
    {
        return (self::user()['perfil'] ?? '') === 'admin';
    }

    private static function enforceIdleTimeout(): void
    {
        $minutes = (int) env('SESSION_IDLE_TIMEOUT', env('SESSION_LIFETIME', 120));
        $timeout = max(5, $minutes) * 60;

        if (isset($_SESSION['_last_activity']) && time() - (int)$_SESSION['_last_activity'] > $timeout) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['_expired'] = true;
            return;
        }

        $_SESSION['_last_activity'] = time();
    }

    private static function secureCookieEnabled(): bool
    {
        $configured = strtolower((string)env('SESSION_SECURE', 'auto'));
        if ($configured === 'auto') {
            return SecurityHeaders::isHttps();
        }

        return filter_var($configured, FILTER_VALIDATE_BOOLEAN);
    }

    private static function sameSite(): string
    {
        $sameSite = ucfirst(strtolower((string)env('SESSION_SAMESITE', 'Strict')));
        return in_array($sameSite, ['Lax', 'Strict', 'None'], true) ? $sameSite : 'Strict';
    }
}
