<?php

namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!Session::isLoggedIn()) {
            $current = $_SERVER['REQUEST_URI'] ?? '/dashboard';
            $path = parse_url($current, PHP_URL_PATH) ?: '/dashboard';
            $basePath = parse_url((string) env('APP_URL', ''), PHP_URL_PATH) ?: '';

            if ($basePath !== '' && str_starts_with($path, $basePath)) {
                $path = substr($path, strlen($basePath)) ?: '/dashboard';
            }

            header('Location: ' . url('/login?redirect=' . urlencode($path)));
            exit;
        }

        $path = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
        $basePath = parse_url((string) env('APP_URL', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        $path = '/' . ltrim($path, '/');
        $permitidasDuranteTroca = ['/minha-senha', '/logout'];

        if (!empty(Session::user()['precisa_alterar_senha']) && !in_array($path, $permitidasDuranteTroca, true)) {
            header('Location: ' . url('/minha-senha'));
            exit;
        }
    }
}
