<?php

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        $secure = env('SESSION_SECURE', 'false') === 'true';
        $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;
        $sessionPath = BASE_PATH . '/storage/sessions';

        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0775, true);
        }

        session_save_path($sessionPath);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

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

    public static function destroy(): void
    {
        session_unset();
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
}
