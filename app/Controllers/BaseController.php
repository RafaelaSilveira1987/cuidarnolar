<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Core\View;
use App\Models\AuditLog;
use Throwable;

abstract class BaseController
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function view(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $data['_user'] = Session::get('user');
        $data['_flashes'] = Session::getFlashes();
        $data['_csrf'] = Session::getCsrfToken();
        View::render($template, $data, $layout);
    }

    protected function redirect(string $uri): void
    {
        header('Location: ' . url($uri));
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Registra auditoria sem quebrar o fluxo principal caso a tabela/log falhe.
     */
    protected function audit(string $acao, string $modulo = 'sistema', array $detalhes = [], ?int $usuarioId = null): void
    {
        try {
            (new AuditLog())->registrar($acao, $modulo, $detalhes, $usuarioId);
        } catch (Throwable) {
            // Auditoria nunca deve impedir a ação principal do sistema.
        }
    }

    /**
     * Monta uma lista compacta de campos alterados para auditoria.
     */
    protected function auditChanges(array $antes, array $depois): array
    {
        $mudancas = [];

        foreach ($depois as $campo => $valorDepois) {
            if (str_starts_with((string)$campo, '_')) {
                continue;
            }

            $valorAntes = $antes[$campo] ?? null;

            if (is_array($valorAntes) || is_array($valorDepois)) {
                $normalAntes = json_encode($valorAntes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $normalDepois = json_encode($valorDepois, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $normalAntes = trim((string)$valorAntes);
                $normalDepois = trim((string)$valorDepois);
            }

            if ($normalAntes === $normalDepois) {
                continue;
            }

            $mudancas[$campo] = [
                'antes' => $this->auditLimitValue($valorAntes),
                'depois' => $this->auditLimitValue($valorDepois),
            ];
        }

        return $mudancas;
    }

    private function auditLimitValue(mixed $valor): mixed
    {
        if (is_array($valor)) {
            return array_map(fn(mixed $item): mixed => $this->auditLimitValue($item), $valor);
        }

        if (is_string($valor) && mb_strlen($valor, 'UTF-8') > 500) {
            return mb_substr($valor, 0, 500, 'UTF-8') . '…';
        }

        return $valor;
    }
}
