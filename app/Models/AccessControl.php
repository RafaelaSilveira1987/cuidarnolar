<?php

namespace App\Models;

use PDO;

class AccessControl extends BaseModel
{
    protected string $table = 'tb_acl_permissoes';

    private ?bool $aclDisponivel = null;

    public function permissoesDoUsuario(int $usuarioId, ?int $tipoUsuarioId, string $perfil = ''): array
    {
        if ($this->ehAdmin($perfil, $tipoUsuarioId)) {
            return ['*'];
        }

        if (!$this->aclDisponivel()) {
            return [];
        }

        $permissoes = [];

        if ($tipoUsuarioId && $tipoUsuarioId > 0) {
            $permissoes = array_merge($permissoes, $this->rawAll(
                "SELECT p.chave
                 FROM tb_acl_tipo_usuario_permissoes tp
                 INNER JOIN tb_acl_permissoes p ON p.id = tp.permissao_id
                 WHERE tp.tipo_usuario_id = :tipo_usuario_id
                   AND p.ativo = 1",
                [':tipo_usuario_id' => $tipoUsuarioId]
            ));
        }

        if ($usuarioId > 0 && $this->tabelaExiste('tb_acl_usuario_permissoes')) {
            $permissoes = array_merge($permissoes, $this->rawAll(
                "SELECT p.chave
                 FROM tb_acl_usuario_permissoes up
                 INNER JOIN tb_acl_permissoes p ON p.id = up.permissao_id
                 WHERE up.usuario_id = :usuario_id
                   AND p.ativo = 1",
                [':usuario_id' => $usuarioId]
            ));
        }

        return array_values(array_unique(array_filter(array_map(
            static fn(array $row): string => (string)($row['chave'] ?? ''),
            $permissoes
        ))));
    }

    public function usuarioPode(array $user, string $permissao): bool
    {
        $permissao = trim($permissao);
        if ($permissao === '') {
            return true;
        }

        if (($user['perfil'] ?? '') === 'admin') {
            return true;
        }

        $permissoes = $user['permissions'] ?? [];
        if (!is_array($permissoes)) {
            $permissoes = [];
        }

        return in_array('*', $permissoes, true) || in_array($permissao, $permissoes, true);
    }

    public function listarPermissoes(): array
    {
        if (!$this->aclDisponivel()) {
            return [];
        }

        return $this->rawAll(
            "SELECT *
             FROM tb_acl_permissoes
             WHERE ativo = 1
             ORDER BY modulo ASC, ordem ASC, nome ASC"
        );
    }

    public function listarTiposUsuario(): array
    {
        if (!$this->tabelaExiste('tb_tipos_usuarios')) {
            return [];
        }

        return $this->rawAll(
            "SELECT id, nome_tipo, descricao
             FROM tb_tipos_usuarios
             ORDER BY prioridade ASC, nome_tipo ASC"
        );
    }

    public function permissoesPorTipo(): array
    {
        if (!$this->aclDisponivel()) {
            return [];
        }

        $rows = $this->rawAll(
            "SELECT tipo_usuario_id, permissao_id
             FROM tb_acl_tipo_usuario_permissoes"
        );

        $map = [];
        foreach ($rows as $row) {
            $tipoId = (int)($row['tipo_usuario_id'] ?? 0);
            $permissaoId = (int)($row['permissao_id'] ?? 0);
            if ($tipoId > 0 && $permissaoId > 0) {
                $map[$tipoId][] = $permissaoId;
            }
        }

        return $map;
    }

    public function salvarPermissoesTipo(int $tipoUsuarioId, array $permissaoIds): void
    {
        if (!$this->aclDisponivel() || $tipoUsuarioId <= 0) {
            return;
        }

        $this->query(
            "DELETE FROM tb_acl_tipo_usuario_permissoes WHERE tipo_usuario_id = :tipo_usuario_id",
            [':tipo_usuario_id' => $tipoUsuarioId]
        );

        $permissaoIds = array_values(array_unique(array_filter(array_map('intval', $permissaoIds))));
        foreach ($permissaoIds as $permissaoId) {
            if ($permissaoId <= 0) {
                continue;
            }

            $this->query(
                "INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
                 VALUES (:tipo_usuario_id, :permissao_id)",
                [
                    ':tipo_usuario_id' => $tipoUsuarioId,
                    ':permissao_id' => $permissaoId,
                ]
            );
        }
    }

    private function ehAdmin(string $perfil, ?int $tipoUsuarioId): bool
    {
        if (strtolower($perfil) === 'admin') {
            return true;
        }

        if (!$tipoUsuarioId || $tipoUsuarioId <= 0 || !$this->tabelaExiste('tb_tipos_usuarios')) {
            return false;
        }

        $tipo = $this->rawFirst(
            "SELECT nome_tipo FROM tb_tipos_usuarios WHERE id = :id LIMIT 1",
            [':id' => $tipoUsuarioId]
        );

        return strtolower((string)($tipo['nome_tipo'] ?? '')) === 'administrador';
    }

    private function aclDisponivel(): bool
    {
        if ($this->aclDisponivel !== null) {
            return $this->aclDisponivel;
        }

        return $this->aclDisponivel = $this->tabelaExiste('tb_acl_permissoes')
            && $this->tabelaExiste('tb_acl_tipo_usuario_permissoes');
    }

    private function tabelaExiste(string $tabela): bool
    {
        try {
            $stmt = $this->query(
                "SELECT COUNT(*)
                 FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabela",
                [':tabela' => $tabela]
            );

            return (int)$stmt->fetchColumn() > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
