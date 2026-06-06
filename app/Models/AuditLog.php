<?php

namespace App\Models;

class AuditLog extends BaseModel
{
    protected string $table = 'tb_auditoria';

    /**
     * Registra uma ação de auditoria sem interromper o fluxo do sistema.
     * Dados sensíveis são mascarados automaticamente antes de gravar em JSON.
     */
    public function registrar(string $acao, string $modulo = 'sistema', array $detalhes = [], ?int $usuarioId = null): void
    {
        if (!$this->tabelaExiste()) {
            return;
        }

        $user = \App\Core\Session::user();
        $usuarioId ??= (int)($user['id'] ?? 0) ?: null;

        $detalhes = $this->normalizarDetalhes($detalhes);
        $jsonDetalhes = json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($jsonDetalhes === false) {
            $jsonDetalhes = json_encode(['erro' => 'Falha ao serializar detalhes da auditoria']);
        }

        $this->query(
            "INSERT INTO {$this->table}
                (usuario_id, acao, modulo, entidade, entidade_id, ip, user_agent, detalhes, created_at)
             VALUES
                (:usuario_id, :acao, :modulo, :entidade, :entidade_id, :ip, :user_agent, :detalhes, NOW())",
            [
                ':usuario_id' => $usuarioId,
                ':acao' => $this->limitar($acao, 80),
                ':modulo' => $this->limitar($modulo, 80),
                ':entidade' => $this->limitar((string)($detalhes['entidade'] ?? ''), 80) ?: null,
                ':entidade_id' => $this->limitar((string)($detalhes['entidade_id'] ?? ''), 80) ?: null,
                ':ip' => $this->limitar((string)($_SERVER['REMOTE_ADDR'] ?? ''), 45) ?: null,
                ':user_agent' => $this->limitar((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 255) ?: null,
                ':detalhes' => $jsonDetalhes,
            ]
        );
    }

    /**
     * Atalho para ações de alteração que precisam guardar antes/depois.
     */
    public function registrarAlteracao(
        string $acao,
        string $modulo,
        string $entidade,
        string|int|null $entidadeId,
        array $antes = [],
        array $depois = [],
        array $extras = []
    ): void {
        $this->registrar($acao, $modulo, $extras + [
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'antes' => $antes,
            'depois' => $depois,
        ]);
    }

    private function normalizarDetalhes(array $detalhes): array
    {
        $detalhes = $this->mascararSensivel($detalhes);

        $detalhes['_request'] = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? null,
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
        ];

        return $detalhes;
    }

    private function mascararSensivel(mixed $valor): mixed
    {
        if (!is_array($valor)) {
            if (is_string($valor) && mb_strlen($valor, 'UTF-8') > 1500) {
                return mb_substr($valor, 0, 1500, 'UTF-8') . '…';
            }
            return $valor;
        }

        $sensivel = [
            'senha', 'password', 'token', 'remember_token', 'password_reset_token',
            'token_recuperacao', 'codigo_sms', 'csrf', '_csrf', 'cookie', 'authorization',
        ];

        $limpo = [];
        foreach ($valor as $key => $item) {
            $keyStr = mb_strtolower((string)$key, 'UTF-8');
            $ehSensivel = false;

            foreach ($sensivel as $termo) {
                if (str_contains($keyStr, $termo)) {
                    $ehSensivel = true;
                    break;
                }
            }

            $limpo[$key] = $ehSensivel ? '[mascarado]' : $this->mascararSensivel($item);
        }

        return $limpo;
    }

    private function limitar(string $valor, int $limite): string
    {
        return mb_substr(trim($valor), 0, $limite, 'UTF-8');
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
