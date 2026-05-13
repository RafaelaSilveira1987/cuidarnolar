<?php

namespace App\Models;

class Usuario extends BaseModel
{
    protected string $table = 'tb_usuarios';

    public function findByUsername(string $username): array|false
    {
        return $this->rawFirst(
            "SELECT u.*, t.nome_tipo AS tipo_usuario
             FROM {$this->table} u
             LEFT JOIN tb_tipos_usuarios t ON t.id = u.tipo_usuario_id
             WHERE u.username = :username
             LIMIT 1",
            [':username' => $username]
        );
    }

    public function markLastLogin(int $id): bool
    {
        return $this->update($id, ['ultimo_login' => date('Y-m-d H:i:s')]);
    }
}
