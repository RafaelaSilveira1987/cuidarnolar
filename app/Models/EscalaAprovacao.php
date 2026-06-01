<?php

namespace App\Models;

use PDO;

class EscalaAprovacao extends BaseModel
{
    /**
     * Aceita os nomes mais prováveis da tabela.
     * No seu caso, o correto deve ser: tb_escala_aprovacoes.
     */
    private array $tabelasPossiveis = [
        'tb_escala_aprovacoes',
        'tb_escala_aprovacao',
        'escala_aprovacoes',
        'escala_aprovacao',
    ];

    public function registrar(
        int $escalaBaseId,
        int $pacienteId,
        string $periodoInicio,
        string $periodoFim,
        int $totalPlantoes = 0
    ): bool {
        $tabela = $this->resolverTabela();

        if (!$tabela) {
            return false;
        }

        $colunas = $this->colunasTabela($tabela);

        if ($colunas === []) {
            return false;
        }

        $agora = date('Y-m-d H:i:s');
        $usuarioId = $this->usuarioLogadoId();

        $status = $this->valorEnumSeguro($tabela, 'status', [
            'aprovada',
            'aprovado',
            'confirmado',
            'confirmada',
            'OK',
            'ok',
        ]);

        $origem = $this->valorEnumSeguro($tabela, 'origem', [
            'Manual',
            'manual',
            'sistema',
            'aprovacao',
            'Aprovacao',
        ]);

        $dados = [
            'uuid' => $this->gerarUuid(),

            'escala_base_id' => $escalaBaseId,
            'paciente_id' => $pacienteId,

            'periodo_inicio' => $periodoInicio,
            'periodo_fim' => $periodoFim,

            'data_inicio' => $periodoInicio,
            'data_fim' => $periodoFim,

            'inicio' => $periodoInicio . ' 00:00:00',
            'fim' => $periodoFim . ' 23:59:59',

            'status' => $status,
            'origem' => $origem,

            'aprovado_por' => $usuarioId,
            'aprovador_id' => $usuarioId,
            'usuario_id' => $usuarioId,
            'created_by' => $usuarioId,
            'updated_by' => $usuarioId,

            'aprovado_em' => $agora,
            'data_aprovacao' => $agora,
            'created_at' => $agora,
            'updated_at' => $agora,
            'criado_em' => $agora,
            'atualizado_em' => $agora,

            'observacoes' => 'Aprovação da escala do período. Plantões novos confirmados: ' . $totalPlantoes,
            'total_plantoes' => $totalPlantoes,
        ];

        $insert = [];

        foreach ($dados as $campo => $valor) {
            if (isset($colunas[$campo]) && $valor !== null) {
                $insert[$campo] = $valor;
            }
        }

        if ($insert === []) {
            return false;
        }

        $campos = array_keys($insert);
        $placeholders = array_map(static fn ($campo) => ':' . $campo, $campos);

        $params = [];
        foreach ($insert as $campo => $valor) {
            $params[':' . $campo] = $valor;
        }

        $updates = [];
        foreach ([
            'status',
            'origem',
            'aprovado_por',
            'aprovador_id',
            'usuario_id',
            'updated_by',
            'aprovado_em',
            'data_aprovacao',
            'updated_at',
            'atualizado_em',
            'observacoes',
            'total_plantoes',
        ] as $campo) {
            if (isset($insert[$campo])) {
                $updates[] = "{$campo} = VALUES({$campo})";
            }
        }

        $sql = "INSERT INTO {$tabela} (" . implode(', ', $campos) . ")
                VALUES (" . implode(', ', $placeholders) . ")";

        if ($updates !== []) {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updates);
        }

        $this->query($sql, $params);

        return true;
    }


    public function buscarPorPeriodo(
        int $escalaBaseId,
        int $pacienteId,
        string $periodoInicio,
        string $periodoFim
    ): ?array {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return null;
        }

        $colunas = $this->colunasTabela($tabela);
        $campoInicio = isset($colunas['data_inicio']) ? 'data_inicio' : (isset($colunas['periodo_inicio']) ? 'periodo_inicio' : null);
        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);

        if (!$campoInicio || !$campoFim || !isset($colunas['escala_base_id'], $colunas['paciente_id'])) {
            return null;
        }

        return $this->rawFirst(
            "SELECT *
             FROM {$tabela}
             WHERE escala_base_id = :escala_base_id
               AND paciente_id = :paciente_id
               AND {$campoInicio} = :periodo_inicio
               AND {$campoFim} = :periodo_fim
             LIMIT 1",
            [
                ':escala_base_id' => $escalaBaseId,
                ':paciente_id' => $pacienteId,
                ':periodo_inicio' => $periodoInicio,
                ':periodo_fim' => $periodoFim,
            ]
        ) ?: null;
    }

    private function resolverTabela(): ?string
    {
        foreach ($this->tabelasPossiveis as $tabela) {
            if ($this->tabelaExiste($tabela)) {
                return $tabela;
            }
        }

        return null;
    }

    private function tabelaExiste(string $tabela): bool
    {
        $stmt = $this->query(
            "SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabela",
            [':tabela' => $tabela]
        );

        return (int)$stmt->fetchColumn() > 0;
    }

    private function colunasTabela(string $tabela): array
    {
        $stmt = $this->query(
            "SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :tabela",
            [':tabela' => $tabela]
        );

        $colunas = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $colunas[$row['COLUMN_NAME']] = $row;
        }

        return $colunas;
    }

    private function valorEnumSeguro(string $tabela, string $coluna, array $preferidos): ?string
    {
        $colunas = $this->colunasTabela($tabela);
        $tipo = (string)($colunas[$coluna]['COLUMN_TYPE'] ?? '');

        if (!str_starts_with(strtolower($tipo), 'enum(')) {
            return $preferidos[0] ?? null;
        }

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $tipo, $matches);

        $permitidos = array_map(
            static fn ($valor) => stripcslashes($valor),
            $matches[1] ?? []
        );

        foreach ($preferidos as $valor) {
            if (in_array($valor, $permitidos, true)) {
                return $valor;
            }
        }

        return $permitidos[0] ?? null;
    }

    private function usuarioLogadoId(): ?int
    {
        $possiveis = [
            $_SESSION['user']['id'] ?? null,
            $_SESSION['_user']['id'] ?? null,
            $_SESSION['usuario']['id'] ?? null,
            $_SESSION['usuario_id'] ?? null,
        ];

        foreach ($possiveis as $id) {
            if ((int)$id > 0) {
                return (int)$id;
            }
        }

        return null;
    }

    private function gerarUuid(): string
    {
        $data = random_bytes(16);

        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
