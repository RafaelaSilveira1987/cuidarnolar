<?php

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public function handle(): void
    {
        if (Session::isLoggedIn()) {
            return;
        }

        $current = $_SERVER['REQUEST_URI'] ?? '/dashboard';
        $path = parse_url($current, PHP_URL_PATH) ?: '/dashboard';
        $basePath = parse_url((string) env('APP_URL', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/dashboard';
        }

        header('Location: ' . url('/login?redirect=' . urlencode($path)));
        exit;
    }
}
