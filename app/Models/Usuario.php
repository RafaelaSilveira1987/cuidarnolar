<?php

namespace App\Models;

class Usuario extends BaseModel
{
    protected string $table = 'tb_usuarios';


    public function findByUsername(string $username): array|false
    {
        $username = trim($username);

        if ($username === '') {
            return false;
        }

        return $this->rawFirst(
            "SELECT *
         FROM {$this->table}
         WHERE username = :username
            OR email = :email
         LIMIT 1",
            [
                ':username' => $username,
                ':email' => $username,
            ]
        );
    }

    public function findByUuid(string $uuid): array|false
    {
        $uuid = trim($uuid);

        if ($uuid === '') {
            return false;
        }

        return $this->rawFirst(
            "SELECT *
             FROM {$this->table}
             WHERE uuid = :uuid
             LIMIT 1",
            [
                ':uuid' => $uuid,
            ]
        );
    }

    public function findWithTipo(int $id): array|false
    {
        return $this->rawFirst(
            "SELECT u.*, t.nome_tipo AS tipo_usuario
             FROM {$this->table} u
             LEFT JOIN tb_tipos_usuarios t ON t.id = u.tipo_usuario_id
             WHERE u.id = :id
             LIMIT 1",
            [':id' => $id]
        );
    }

    public function listarAdmin(string $busca = ''): array
    {
        $busca = trim($busca);
        $where = '';
        $params = [];

        if ($busca !== '') {
            $where = "WHERE u.nome_completo LIKE :busca
                       OR u.username LIKE :busca
                       OR u.email LIKE :busca
                       OR t.nome_tipo LIKE :busca";
            $params[':busca'] = '%' . $busca . '%';
        }

        return $this->rawAll(
            "SELECT u.id, u.uuid, u.nome_completo, u.email, u.telefone, u.username,
                    u.ultimo_login, u.status, u.tipo_usuario_id, u.precisa_alterar_senha,
                    u.last_password_change, t.nome_tipo AS tipo_usuario
             FROM {$this->table} u
             LEFT JOIN tb_tipos_usuarios t ON t.id = u.tipo_usuario_id
             {$where}
             ORDER BY u.status ASC, t.prioridade ASC, u.nome_completo ASC",
            $params
        );
    }


    public function listarComTiposECuidadores(string $busca = ''): array
    {
        $busca = trim($busca);
        $where = '';
        $params = [];

        if ($busca !== '') {
            $where = "WHERE u.nome_completo LIKE :busca
                       OR u.username LIKE :busca
                       OR u.email LIKE :busca
                       OR t.nome_tipo LIKE :busca
                       OR c.nome_completo LIKE :busca";
            $params[':busca'] = '%' . $busca . '%';
        }

        return $this->rawAll(
            "SELECT
                u.id,
                u.uuid,
                u.nome_completo,
                u.email,
                u.telefone,
                u.username,
                u.ultimo_login,
                u.status,
                u.tipo_usuario_id,
                u.precisa_alterar_senha,
                u.last_password_change,
                u.cuidador_id,
                t.nome_tipo AS tipo_usuario,
                c.uuid AS cuidador_uuid,
                c.nome_completo AS cuidador_nome,
                c.status AS cuidador_status
             FROM {$this->table} u
             LEFT JOIN tb_tipos_usuarios t ON t.id = u.tipo_usuario_id
             LEFT JOIN tb_cuidador c ON c.id = u.cuidador_id
             {$where}
             ORDER BY u.status ASC, t.prioridade ASC, u.nome_completo ASC, u.username ASC",
            $params
        );
    }

    public function tiposUsuario(): array
    {
        return $this->rawAll(
            "SELECT id, nome_tipo, descricao, prioridade
             FROM tb_tipos_usuarios
             ORDER BY prioridade ASC, nome_tipo ASC"
        );
    }

    public function criarUsuario(array $data): array
    {
        $payload = $this->normalizarPayload($data);
        $senha = (string)($data['senha'] ?? '');
        $confirmacao = (string)($data['senha_confirmacao'] ?? '');
        $errors = $this->validarUsuario($payload, null);
        $errors += $this->validarSenhaForte($senha, $payload['username'], $payload['email']);

        if ($senha !== $confirmacao) {
            $errors['senha_confirmacao'] = 'A confirmação da senha não confere.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'id' => null, 'errors' => $errors];
        }

        $payload['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        $payload['precisa_alterar_senha'] = !empty($data['precisa_alterar_senha']) ? 1 : 0;
        $payload['ultimo_login'] = '1970-01-01 00:00:00';
        $payload['last_password_change'] = date('Y-m-d H:i:s');

        $id = $this->insert($payload);

        return ['ok' => true, 'id' => $id, 'errors' => []];
    }

    public function atualizarUsuarioPorUuid(string $uuid, array $data): array
    {
        $usuario = $this->findByUuid($uuid);
        if (!$usuario) {
            return ['ok' => false, 'id' => null, 'errors' => ['geral' => 'Usuário não encontrado.']];
        }

        $id = (int)$usuario['id'];
        $payload = $this->normalizarPayload($data, false);
        $errors = $this->validarUsuario($payload, $id);

        if ($errors !== []) {
            return ['ok' => false, 'id' => $id, 'errors' => $errors];
        }

        $this->update($id, $payload);

        return ['ok' => true, 'id' => $id, 'errors' => []];
    }

    public function alterarSenha(int $id, string $senhaAtual, string $novaSenha, string $confirmacao, bool $exigirSenhaAtual = true): array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['ok' => false, 'errors' => ['geral' => 'Usuário não encontrado.']];
        }

        $errors = [];

        if ($exigirSenhaAtual && !password_verify($senhaAtual, (string)$usuario['senha'])) {
            $errors['senha_atual'] = 'Senha atual incorreta.';
        }

        if ($novaSenha !== $confirmacao) {
            $errors['senha_confirmacao'] = 'A confirmação da senha não confere.';
        }

        $errors += $this->validarSenhaForte(
            $novaSenha,
            (string)($usuario['username'] ?? ''),
            (string)($usuario['email'] ?? '')
        );

        if (password_verify($novaSenha, (string)$usuario['senha'])) {
            $errors['nova_senha'] = 'A nova senha precisa ser diferente da senha atual.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->query(
            "UPDATE {$this->table}
             SET senha = :senha,
                 precisa_alterar_senha = 0,
                 last_password_change = NOW(),
                 password_reset_token = NULL,
                 password_reset_expires = NULL,
                 token_recuperacao = NULL,
                 token_expiracao = NULL
             WHERE id = :id",
            [
                ':senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
                ':id' => $id,
            ]
        );

        return ['ok' => true, 'errors' => []];
    }

    public function resetarSenhaPorUuid(string $uuid, string $novaSenha, bool $forcarTroca = true): array
    {
        $usuario = $this->findByUuid($uuid);
        if (!$usuario) {
            return ['ok' => false, 'errors' => ['geral' => 'Usuário não encontrado.']];
        }

        $errors = $this->validarSenhaForte(
            $novaSenha,
            (string)($usuario['username'] ?? ''),
            (string)($usuario['email'] ?? '')
        );

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $this->query(
            "UPDATE {$this->table}
             SET senha = :senha,
                 precisa_alterar_senha = :forcar,
                 last_password_change = NOW(),
                 password_reset_token = NULL,
                 password_reset_expires = NULL,
                 token_recuperacao = NULL,
                 token_expiracao = NULL
             WHERE id = :id",
            [
                ':senha' => password_hash($novaSenha, PASSWORD_DEFAULT),
                ':forcar' => $forcarTroca ? 1 : 0,
                ':id' => (int)$usuario['id'],
            ]
        );

        return ['ok' => true, 'errors' => []];
    }


    public function vincularCuidadorPorUuid(string $uuid, ?int $cuidadorId): bool
    {
        $usuario = $this->findByUuid($uuid);

        if (!$usuario) {
            return false;
        }

        $this->query(
            "UPDATE {$this->table}
             SET cuidador_id = :cuidador_id
             WHERE uuid = :uuid",
            [
                ':cuidador_id' => $cuidadorId && $cuidadorId > 0 ? $cuidadorId : null,
                ':uuid' => $uuid,
            ]
        );

        return true;
    }

    public function alternarStatusPorUuid(string $uuid): bool
    {
        $usuario = $this->findByUuid($uuid);

        if (!$usuario) {
            return false;
        }

        $statusAtual = mb_strtolower(trim((string)($usuario['status'] ?? 'ativo')), 'UTF-8');
        $novoStatus = $statusAtual === 'ativo' ? 'inativo' : 'ativo';

        $this->query(
            "UPDATE {$this->table}
         SET status = :status
         WHERE uuid = :uuid",
            [
                ':status' => $novoStatus,
                ':uuid' => $uuid,
            ]
        );

        return true;
    }

    public function markLastLogin(int $id): bool
    {
        return $this->update($id, ['ultimo_login' => date('Y-m-d H:i:s')]);
    }

    public function atualizarSenhaHash(int $id, string $hash): bool
    {
        return $this->update($id, [
            'senha' => $hash,
            'last_password_change' => date('Y-m-d H:i:s'),
        ]);
    }

    public function validarSenhaForte(string $senha, string $username = '', string $email = ''): array
    {
        $errors = [];
        $senha = (string)$senha;

        if (strlen($senha) < 8) {
            $errors['nova_senha'] = 'A senha deve ter pelo menos 8 caracteres.';
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            $errors['nova_senha'] = 'A senha deve conter pelo menos uma letra maiúscula.';
        }

        if (!preg_match('/[a-z]/', $senha)) {
            $errors['nova_senha'] = 'A senha deve conter pelo menos uma letra minúscula.';
        }

        if (!preg_match('/\d/', $senha)) {
            $errors['nova_senha'] = 'A senha deve conter pelo menos um número.';
        }

        $senhaLower = mb_strtolower($senha, 'UTF-8');
        $username = mb_strtolower(trim($username), 'UTF-8');
        $emailUser = mb_strtolower(trim(strtok($email, '@') ?: ''), 'UTF-8');

        if ($username !== '' && str_contains($senhaLower, $username)) {
            $errors['nova_senha'] = 'A senha não deve conter o nome de usuário.';
        }

        if ($emailUser !== '' && str_contains($senhaLower, $emailUser)) {
            $errors['nova_senha'] = 'A senha não deve conter o início do e-mail.';
        }

        return $errors;
    }

    private function normalizarPayload(array $data, bool $novo = true): array
    {
        $status = mb_strtolower(trim((string)($data['status'] ?? 'ativo')), 'UTF-8');
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            $status = 'ativo';
        }

        return [
            'nome_completo' => trim((string)($data['nome_completo'] ?? '')),
            'email' => trim((string)($data['email'] ?? '')),
            'telefone' => trim((string)($data['telefone'] ?? '')) ?: null,
            'username' => trim((string)($data['username'] ?? '')),
            'status' => $status,
            'tipo_usuario_id' => $this->nullableInt($data['tipo_usuario_id'] ?? null),
            'cuidador_id' => $this->nullableInt($data['cuidador_id'] ?? null),
        ];
    }

    private function validarUsuario(array $payload, ?int $ignorarId): array
    {
        $errors = [];

        if ($payload['nome_completo'] === '') {
            $errors['nome_completo'] = 'Informe o nome completo.';
        }

        if ($payload['username'] === '') {
            $errors['username'] = 'Informe o usuário de login.';
        } elseif (strlen($payload['username']) < 3) {
            $errors['username'] = 'O usuário precisa ter pelo menos 3 caracteres.';
        }

        if ($payload['email'] === '' || !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Informe um e-mail válido.';
        }

        if (($payload['tipo_usuario_id'] ?? 0) <= 0) {
            $errors['tipo_usuario_id'] = 'Selecione o tipo de usuário.';
        }

        $tipoUsuarioId = (int)($payload['tipo_usuario_id'] ?? 0);
        $cuidadorId = $payload['cuidador_id'] ?? null;

        if ($this->ehTipoCuidador($tipoUsuarioId)) {
            if (!$cuidadorId) {
                $errors['cuidador_id'] = 'Usuários do perfil Cuidador precisam estar vinculados a um cuidador ativo.';
            } elseif (!$this->cuidadorAtivo((int)$cuidadorId)) {
                $errors['cuidador_id'] = 'O cuidador vinculado precisa existir e estar ativo.';
            }
        } elseif ($cuidadorId && !$this->cuidadorAtivo((int)$cuidadorId)) {
            $errors['cuidador_id'] = 'O cuidador vinculado precisa existir e estar ativo.';
        }

        if ($payload['username'] !== '' && $this->existeValor('username', $payload['username'], $ignorarId)) {
            $errors['username'] = 'Este usuário de login já está em uso.';
        }

        if ($payload['email'] !== '' && $this->existeValor('email', $payload['email'], $ignorarId)) {
            $errors['email'] = 'Este e-mail já está em uso.';
        }

        return $errors;
    }

    private function existeValor(string $campo, string $valor, ?int $ignorarId): bool
    {
        $campo = in_array($campo, ['username', 'email'], true) ? $campo : 'username';
        $whereIgnore = $ignorarId ? ' AND id <> :id' : '';
        $params = [':valor' => $valor];
        if ($ignorarId) {
            $params[':id'] = $ignorarId;
        }

        return (int)$this->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE {$campo} = :valor {$whereIgnore}",
            $params
        )->fetchColumn() > 0;
    }

    private function ehTipoCuidador(int $tipoUsuarioId): bool
    {
        if ($tipoUsuarioId <= 0) {
            return false;
        }

        if ($tipoUsuarioId === 6) {
            return true;
        }

        try {
            $nome = (string)$this->query(
                "SELECT nome_tipo FROM tb_tipos_usuarios WHERE id = :id LIMIT 1",
                [':id' => $tipoUsuarioId]
            )->fetchColumn();

            return str_contains(mb_strtolower($nome, 'UTF-8'), 'cuidador');
        } catch (\Throwable) {
            return false;
        }
    }

    private function cuidadorAtivo(int $cuidadorId): bool
    {
        if ($cuidadorId <= 0) {
            return false;
        }

        try {
            $status = $this->query(
                "SELECT status FROM tb_cuidador WHERE id = :id LIMIT 1",
                [':id' => $cuidadorId]
            )->fetchColumn();

            return mb_strtolower(trim((string)$status), 'UTF-8') === 'ativo';
        } catch (\Throwable) {
            return false;
        }
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int)$value;
        return $int > 0 ? $int : null;
    }
}