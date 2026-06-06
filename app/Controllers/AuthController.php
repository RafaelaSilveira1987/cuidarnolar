<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\AccessControl;
use App\Models\AuditLog;
use App\Models\Cuidador;
use App\Models\LoginAttempt;
use App\Models\Usuario;
use Throwable;

class AuthController extends BaseController
{
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->view('auth/login', ['redirect' => $this->input('redirect', '/dashboard')], 'layouts/auth');
    }

    public function login(): void
    {
        $username = trim((string)(
            $this->input('username', '')
            ?: $this->input('usuario', '')
            ?: $this->input('email', '')
        ));

        $senha = (string)(
            $this->input('senha', '')
            ?: $this->input('password', '')
        );

        $redirect = $this->sanitizeRedirect((string)$this->input('redirect', '/dashboard'));
        $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        $attempts = new LoginAttempt();

        if ($attempts->bloqueado($username, $ip)) {
            $this->flash('error', 'Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.');
            $this->redirect('/login?redirect=' . urlencode($redirect));
            return;
        }

        try {
            $model = new Usuario();
            $usuario = $model->findByUsername($username);
        } catch (Throwable) {
            $usuario = false;
        }

        if (!$usuario || !password_verify($senha, (string)($usuario['senha'] ?? ''))) {
            $attempts->registrarFalha($username, $ip);

            try {
                (new AuditLog())->registrar('login_falhou', 'seguranca', [
                    'username' => $username,
                    'ip' => $ip,
                ], $usuario ? (int)($usuario['id'] ?? 0) : null);
            } catch (Throwable) {
            }

            $this->flash('error', 'Usuário ou senha incorretos.');
            $this->redirect('/login?redirect=' . urlencode($redirect));
            return;
        }

        $statusUsuario = mb_strtolower(trim((string)($usuario['status'] ?? 'ativo')), 'UTF-8');
        if ($statusUsuario !== 'ativo') {
            $this->flash('error', 'Usuário inativo. Entre em contato com o administrador.');
            $this->redirect('/login?redirect=' . urlencode($redirect));
            return;
        }

        try {
            $usuarioComTipo = $model->findWithTipo((int)$usuario['id']);
            if ($usuarioComTipo) {
                $usuario = array_merge($usuario, $usuarioComTipo);
            }
        } catch (Throwable) {
            // O login não deve depender do join de tipos. Usa fallback por ID abaixo.
        }

        $tipoUsuarioId = (int)($usuario['tipo_usuario_id'] ?? 0);
        $perfil = $this->perfilSessao((string)($usuario['tipo_usuario'] ?? ''), $tipoUsuarioId);
        $cuidadorId = !empty($usuario['cuidador_id']) ? (int)$usuario['cuidador_id'] : null;

        if ($perfil === 'cuidador') {
            if (!$cuidadorId || $cuidadorId <= 0) {
                $this->registrarAcessoNegadoCuidador($username, $ip, 'cuidador_sem_vinculo', (int)$usuario['id']);
                $this->flash('error', 'Usuário cuidador sem cuidador vinculado. Entre em contato com o administrador.');
                $this->redirect('/login?redirect=' . urlencode($redirect));
                return;
            }

            $cuidador = (new Cuidador())->find($cuidadorId);
            if (!$cuidador) {
                $this->registrarAcessoNegadoCuidador($username, $ip, 'cuidador_nao_encontrado', (int)$usuario['id']);
                $this->flash('error', 'Cuidador vinculado não encontrado. Entre em contato com o administrador.');
                $this->redirect('/login?redirect=' . urlencode($redirect));
                return;
            }

            $statusCuidador = mb_strtolower(trim((string)($cuidador['status'] ?? '')), 'UTF-8');
            if ($statusCuidador !== 'ativo') {
                $this->registrarAcessoNegadoCuidador($username, $ip, 'cuidador_inativo', (int)$usuario['id']);
                $this->flash('error', 'Cuidador vinculado está inativo. Acesso bloqueado.');
                $this->redirect('/login?redirect=' . urlencode($redirect));
                return;
            }
        }

        $permissions = (new AccessControl())->permissoesDoUsuario((int)$usuario['id'], $tipoUsuarioId, $perfil);

        Session::regenerate();
        Session::set('user', [
            'id' => (int)$usuario['id'],
            'uuid' => (string)($usuario['uuid'] ?? ''),
            'nome' => $usuario['nome_completo'] ?? $usuario['username'],
            'username' => $usuario['username'],
            'email' => $usuario['email'] ?? '',
            'perfil' => $perfil,
            'tipo_usuario_id' => $tipoUsuarioId ?: null,
            'cuidador_id' => $cuidadorId,
            'permissions' => $permissions,
        ]);

        if (password_needs_rehash((string)$usuario['senha'], PASSWORD_DEFAULT)) {
            $model->atualizarSenhaHash((int)$usuario['id'], password_hash($senha, PASSWORD_DEFAULT));
        }

        $attempts->registrarSucesso($username, $ip, (int)$usuario['id']);
        $model->markLastLogin((int)$usuario['id']);

        try {
            (new AuditLog())->registrar('login_sucesso', 'seguranca', [
                'username' => $username,
                'ip' => $ip,
                'perfil' => $perfil,
                'cuidador_id' => $cuidadorId,
            ], (int)$usuario['id']);
        } catch (Throwable) {
        }

        $this->redirect($redirect);
    }

    public function logout(): void
    {
        try {
            $user = Session::user();
            if (!empty($user['id'])) {
                (new AuditLog())->registrar('logout', 'seguranca', [
                    'username' => $user['username'] ?? '',
                ], (int)$user['id']);
            }
        } catch (Throwable) {
        }

        Session::destroy();
        $this->redirect('/login');
    }

    private function perfilSessao(string $tipoUsuario, int $tipoUsuarioId): string
    {
        $tipo = mb_strtolower(trim($tipoUsuario), 'UTF-8');

        if ($tipo === 'administrador' || $tipoUsuarioId === 1) {
            return 'admin';
        }

        if (str_contains($tipo, 'cuidador') || $tipoUsuarioId === 6) {
            return 'cuidador';
        }

        if (str_contains($tipo, 'financeiro') || $tipoUsuarioId === 4) {
            return 'financeiro';
        }

        if (str_contains($tipo, 'coordena') || $tipoUsuarioId === 2) {
            return 'coordenacao';
        }

        if (str_contains($tipo, 'enferm') || str_contains($tipo, 'tecnico') || str_contains($tipo, 'técnico') || $tipoUsuarioId === 5) {
            return 'assistencial';
        }

        if (str_contains($tipo, 'visualizador') || $tipoUsuarioId === 3) {
            return 'visualizador';
        }

        return 'usuario';
    }

    private function registrarAcessoNegadoCuidador(string $username, string $ip, string $motivo, int $usuarioId): void
    {
        try {
            (new AuditLog())->registrar('login_bloqueado_cuidador', 'seguranca', [
                'username' => $username,
                'ip' => $ip,
                'motivo' => $motivo,
            ], $usuarioId ?: null);
        } catch (Throwable) {
        }
    }

    private function sanitizeRedirect(string $redirect): string
    {
        $path = parse_url($redirect, PHP_URL_PATH) ?: '/dashboard';
        $basePath = parse_url((string)env('APP_URL', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/dashboard';
        }

        if (!str_starts_with($path, '/')) {
            return '/dashboard';
        }

        return $path === '/login' ? '/dashboard' : $path;
    }
}
