<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\View;
use App\Models\AccessControl;
use App\Models\AuditLog;

class AuthorizationMiddleware
{
    public function handle(string $permissao = ''): void
    {
        if ($permissao === '') {
            return;
        }

        $user = Session::user();
        if ((new AccessControl())->usuarioPode($user, $permissao)) {
            return;
        }

        try {
            (new AuditLog())->registrar('acesso_negado', 'seguranca', [
                'permissao' => $permissao,
                'uri' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
        } catch (\Throwable) {
        }

        http_response_code(403);
        View::render('errors/403', [
            'message' => 'Você não tem permissão para acessar esta área.',
        ], 'layouts/blank');
        exit;
    }
}
