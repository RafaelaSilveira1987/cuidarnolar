<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\View;

class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, self::SAFE_METHODS, true)) {
            return;
        }

        $tokenFromRequest = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals(Session::getCsrfToken(), (string) $tokenFromRequest)) {
            http_response_code(419);
            View::render('errors/403', [
                'message' => 'Sessão expirada ou token inválido. Recarregue a página e tente novamente.',
            ], 'layouts/blank');
            exit;
        }
    }
}
