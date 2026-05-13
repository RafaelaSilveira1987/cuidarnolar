<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Usuario;
use Throwable;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', ['redirect' => $this->input('redirect', '/dashboard')], 'layouts/auth');
    }

    public function login(): void
    {
        $username = trim((string) $this->input('username', ''));
        $senha = (string) $this->input('senha', '');
        $redirect = $this->sanitizeRedirect((string) $this->input('redirect', '/dashboard'));

        try {
            $model = new Usuario();
            $usuario = $model->findByUsername($username);
        } catch (Throwable) {
            $usuario = false;
        }

        if (!$usuario || !password_verify($senha, (string) $usuario['senha'])) {
            $this->flash('error', 'Usuario ou senha incorretos.');
            $this->redirect('/login?redirect=' . urlencode($redirect));
        }

        if (($usuario['status'] ?? 'ativo') !== 'ativo') {
            $this->flash('error', 'Usuario inativo. Entre em contato com o administrador.');
            $this->redirect('/login?redirect=' . urlencode($redirect));
        }

        Session::set('user', [
            'id' => $usuario['id'],
            'nome' => $usuario['nome_completo'] ?? $usuario['username'],
            'username' => $usuario['username'],
            'email' => $usuario['email'] ?? '',
            'perfil' => $this->mapPerfil($usuario['tipo_usuario'] ?? ''),
            'tipo_usuario_id' => $usuario['tipo_usuario_id'] ?? null,
        ]);

        $model->markLastLogin((int) $usuario['id']);

        $this->redirect($redirect);
    }

    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/login');
    }

    private function mapPerfil(string $tipoUsuario): string
    {
        return strtolower($tipoUsuario) === 'administrador' ? 'admin' : 'usuario';
    }

    private function sanitizeRedirect(string $redirect): string
    {
        $path = parse_url($redirect, PHP_URL_PATH) ?: '/dashboard';
        $basePath = parse_url((string) env('APP_URL', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/dashboard';
        }

        if (!str_starts_with($path, '/')) {
            return '/dashboard';
        }

        return $path === '/login' ? '/dashboard' : $path;
    }
}
