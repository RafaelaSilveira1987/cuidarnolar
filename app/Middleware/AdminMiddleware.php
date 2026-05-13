<?php

namespace App\Middleware;

use App\Core\Session;

class AdminMiddleware
{
    public function handle(): void
    {
        if (Session::isAdmin()) {
            return;
        }

        http_response_code(403);
        echo 'Acesso negado.';
        exit;
    }
}
