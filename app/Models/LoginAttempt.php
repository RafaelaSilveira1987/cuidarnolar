<?php

namespace App\Models;

class LoginAttempt extends BaseModel
{
    protected string $table = 'tb_login_tentativas';

    public function bloqueado(string $username, string $ip): bool
    {
        if (!$this->tabelaExiste()) {
            return false;
        }

        $janelaMinutos = (int)env('LOGIN_LOCKOUT_MINUTES', 15);
        $limite = (int)env('LOGIN_MAX_ATTEMPTS', 5);

        $row = $this->rawFirst(
            "SELECT COUNT(*) AS total
             FROM {$this->table}
             WHERE sucesso = 0
               AND criado_em >= DATE_SUB(NOW(), INTERVAL {$janelaMinutos} MINUTE)
               AND (username = :username OR ip = :ip)",
            [
                ':username' => $username,
                ':ip' => $ip,
            ]
        );

        return (int)($row['total'] ?? 0) >= $limite;
    }

    public function registrarFalha(string $username, string $ip): void
    {
        if (!$this->tabelaExiste()) {
            return;
        }

        $this->query(
            "INSERT INTO {$this->table} (username, ip, sucesso, criado_em)
             VALUES (:username, :ip, 0, NOW())",
            [':username' => $username, ':ip' => $ip]
        );
    }

    public function registrarSucesso(string $username, string $ip, int $usuarioId): void
    {
        if (!$this->tabelaExiste()) {
            return;
        }

        $this->query(
            "INSERT INTO {$this->table} (usuario_id, username, ip, sucesso, criado_em)
             VALUES (:usuario_id, :username, :ip, 1, NOW())",
            [':usuario_id' => $usuarioId, ':username' => $username, ':ip' => $ip]
        );

        $this->query(
            "DELETE FROM {$this->table}
             WHERE sucesso = 0 AND (username = :username OR ip = :ip)",
            [':username' => $username, ':ip' => $ip]
        );
    }

    private function tabelaExiste(): bool
    {
        try {
            $stmt = $this->query(
                "SELECT COUNT(*) FROM information_schema.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabela",
                [':tabela' => $this->table]
            );
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Throwable) {
            return false;
        }
    }
}
