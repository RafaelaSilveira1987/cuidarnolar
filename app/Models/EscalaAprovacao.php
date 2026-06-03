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


    /**
     * Marca o período como fechado/finalizado para liberar a geração do contas a pagar.
     */
    public function fecharPeriodo(
        int $escalaBaseId,
        int $pacienteId,
        string $periodoInicio,
        string $periodoFim,
        int $totalFinalizados = 0
    ): bool {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return false;
        }

        $colunas = $this->colunasTabela($tabela);
        if (!isset($colunas['paciente_id'], $colunas['escala_base_id'], $colunas['status'])) {
            return false;
        }

        $campoInicio = isset($colunas['data_inicio']) ? 'data_inicio' : (isset($colunas['periodo_inicio']) ? 'periodo_inicio' : null);
        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);

        if (!$campoInicio || !$campoFim) {
            return false;
        }

        $statusFechada = $this->valorEnumSeguro($tabela, 'status', [
            'fechada',
            'finalizada',
            'aprovada',
        ]);

        if (!$statusFechada) {
            return false;
        }

        $agora = date('Y-m-d H:i:s');
        $usuarioId = $this->usuarioLogadoId();
        $linhaHistorico = "\n[{$agora}] Fechamento da escala realizado. Plantões finalizados: {$totalFinalizados}.";

        $sets = ['status = :status'];
        $params = [
            ':status' => $statusFechada,
            ':paciente_id' => $pacienteId,
            ':escala_base_id' => $escalaBaseId,
            ':periodo_inicio' => $periodoInicio,
            ':periodo_fim' => $periodoFim,
        ];

        if (isset($colunas['fechado_por']) && $usuarioId) {
            $sets[] = 'fechado_por = :fechado_por';
            $params[':fechado_por'] = $usuarioId;
        }

        if (isset($colunas['fechado_em'])) {
            $sets[] = 'fechado_em = :fechado_em';
            $params[':fechado_em'] = $agora;
        }

        if (isset($colunas['atualizado_em'])) {
            $sets[] = 'atualizado_em = :atualizado_em';
            $params[':atualizado_em'] = $agora;
        }

        if (isset($colunas['updated_at'])) {
            $sets[] = 'updated_at = :updated_at';
            $params[':updated_at'] = $agora;
        }

        if (isset($colunas['observacoes'])) {
            $sets[] = "observacoes = TRIM(CONCAT(COALESCE(observacoes, ''), :historico))";
            $params[':historico'] = $linhaHistorico;
        }

        if (isset($colunas['total_plantoes'])) {
            $sets[] = 'total_plantoes = :total_plantoes';
            $params[':total_plantoes'] = $totalFinalizados;
        }

        $stmt = $this->query(
            "UPDATE {$tabela}
             SET " . implode(', ', $sets) . "
             WHERE paciente_id = :paciente_id
               AND escala_base_id = :escala_base_id
               AND {$campoInicio} = :periodo_inicio
               AND {$campoFim} = :periodo_fim
             LIMIT 1",
            $params
        );

        return $stmt->rowCount() > 0;
    }



    /**
     * Cancela o fechamento do período e volta a aprovação para aprovada.
     */
    public function cancelarFechamentoPeriodo(
        int $escalaBaseId,
        int $pacienteId,
        string $periodoInicio,
        string $periodoFim,
        int $totalReabertos = 0
    ): bool {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return false;
        }

        $colunas = $this->colunasTabela($tabela);
        if (!isset($colunas['paciente_id'], $colunas['escala_base_id'], $colunas['status'])) {
            return false;
        }

        $campoInicio = isset($colunas['data_inicio']) ? 'data_inicio' : (isset($colunas['periodo_inicio']) ? 'periodo_inicio' : null);
        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);
        if (!$campoInicio || !$campoFim) {
            return false;
        }

        $statusAprovada = $this->valorEnumSeguro($tabela, 'status', [
            'aprovada',
            'aprovado',
            'confirmado',
            'confirmada',
            'OK',
            'ok',
        ]);

        if (!$statusAprovada) {
            return false;
        }

        $agora = date('Y-m-d H:i:s');
        $usuarioId = $this->usuarioLogadoId();
        $linhaHistorico = "\n[{$agora}] Cancelamento do fechamento realizado. Plantões reabertos para ajuste: {$totalReabertos}.";

        $sets = ['status = :status'];
        $params = [
            ':status' => $statusAprovada,
            ':paciente_id' => $pacienteId,
            ':escala_base_id' => $escalaBaseId,
            ':periodo_inicio' => $periodoInicio,
            ':periodo_fim' => $periodoFim,
        ];

        if (isset($colunas['fechado_por'])) {
            $sets[] = 'fechado_por = NULL';
        }
        if (isset($colunas['fechado_em'])) {
            $sets[] = 'fechado_em = NULL';
        }
        if (isset($colunas['reaberto_por']) && $usuarioId) {
            $sets[] = 'reaberto_por = :reaberto_por';
            $params[':reaberto_por'] = $usuarioId;
        }
        if (isset($colunas['reaberto_em'])) {
            $sets[] = 'reaberto_em = :reaberto_em';
            $params[':reaberto_em'] = $agora;
        }
        if (isset($colunas['atualizado_em'])) {
            $sets[] = 'atualizado_em = :atualizado_em';
            $params[':atualizado_em'] = $agora;
        }
        if (isset($colunas['updated_at'])) {
            $sets[] = 'updated_at = :updated_at';
            $params[':updated_at'] = $agora;
        }
        if (isset($colunas['observacoes'])) {
            $sets[] = "observacoes = TRIM(CONCAT(COALESCE(observacoes, ''), :historico))";
            $params[':historico'] = $linhaHistorico;
        }

        $stmt = $this->query(
            "UPDATE {$tabela}
             SET " . implode(', ', $sets) . "
             WHERE paciente_id = :paciente_id
               AND escala_base_id = :escala_base_id
               AND {$campoInicio} = :periodo_inicio
               AND {$campoFim} = :periodo_fim
             LIMIT 1",
            $params
        );

        return $stmt->rowCount() > 0;
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


    /**
     * Procura aprovação ainda válida para o paciente/escala base.
     * Usado para bloquear alteração silenciosa da escala base já confirmada.
     */
    public function buscarAprovacaoAtivaPaciente(int $pacienteId, ?int $escalaBaseId = null): ?array
    {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return null;
        }

        $colunas = $this->colunasTabela($tabela);
        if (!isset($colunas['paciente_id'], $colunas['status'])) {
            return null;
        }

        $campoInicio = isset($colunas['data_inicio']) ? 'data_inicio' : (isset($colunas['periodo_inicio']) ? 'periodo_inicio' : null);
        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);
        $statusAprovados = $this->valoresEnumExistentes($tabela, 'status', [
            'aprovada',
            'aprovado',
            'confirmado',
            'confirmada',
            'OK',
            'ok',
        ]);

        if ($statusAprovados === []) {
            return null;
        }

        $where = ['paciente_id = :paciente_id'];
        $params = [':paciente_id' => $pacienteId];

        if ($escalaBaseId && isset($colunas['escala_base_id'])) {
            $where[] = 'escala_base_id = :escala_base_id';
            $params[':escala_base_id'] = $escalaBaseId;
        }

        $statusPlaceholders = [];
        foreach ($statusAprovados as $idx => $status) {
            $ph = ':status_' . $idx;
            $statusPlaceholders[] = $ph;
            $params[$ph] = $status;
        }
        $where[] = 'status IN (' . implode(', ', $statusPlaceholders) . ')';

        // Não trava mudanças por causa de aprovação antiga encerrada há muito tempo.
        if ($campoFim) {
            $where[] = "{$campoFim} >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $order = $campoInicio ? "{$campoInicio} DESC," : '';

        return $this->rawFirst(
            "SELECT *
             FROM {$tabela}
             WHERE " . implode(' AND ', $where) . "
             ORDER BY {$order} id DESC
             LIMIT 1",
            $params
        ) ?: null;
    }

    /**
     * Reabre aprovações vigentes para permitir ajuste controlado da escala base.
     */
    public function reabrirPorPaciente(int $pacienteId, int $escalaBaseId, string $observacao = ''): int
    {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return 0;
        }

        $colunas = $this->colunasTabela($tabela);
        if (!isset($colunas['paciente_id'], $colunas['escala_base_id'], $colunas['status'])) {
            return 0;
        }

        $statusAprovados = $this->valoresEnumExistentes($tabela, 'status', [
            'aprovada',
            'aprovado',
            'confirmado',
            'confirmada',
            'OK',
            'ok',
        ]);
        $statusReaberta = $this->valorEnumSeguro($tabela, 'status', [
            'reaberta',
            'em_edicao',
            'cancelada',
        ]);

        if ($statusAprovados === [] || !$statusReaberta) {
            return 0;
        }

        $agora = date('Y-m-d H:i:s');
        $usuarioId = $this->usuarioLogadoId();
        $observacao = trim($observacao) ?: 'Escala reaberta para ajuste da escala base.';
        $linhaHistorico = "\n[{$agora}] {$observacao}";

        $sets = ['status = :status_reaberta'];
        $params = [
            ':status_reaberta' => $statusReaberta,
            ':paciente_id' => $pacienteId,
            ':escala_base_id' => $escalaBaseId,
        ];

        if (isset($colunas['reaberto_por']) && $usuarioId) {
            $sets[] = 'reaberto_por = :reaberto_por';
            $params[':reaberto_por'] = $usuarioId;
        }

        if (isset($colunas['reaberto_em'])) {
            $sets[] = 'reaberto_em = :reaberto_em';
            $params[':reaberto_em'] = $agora;
        }

        if (isset($colunas['atualizado_em'])) {
            $sets[] = 'atualizado_em = :atualizado_em';
            $params[':atualizado_em'] = $agora;
        }

        if (isset($colunas['updated_at'])) {
            $sets[] = 'updated_at = :updated_at';
            $params[':updated_at'] = $agora;
        }

        if (isset($colunas['observacoes'])) {
            $sets[] = "observacoes = TRIM(CONCAT(COALESCE(observacoes, ''), :historico))";
            $params[':historico'] = $linhaHistorico;
        }

        $statusPlaceholders = [];
        foreach ($statusAprovados as $idx => $status) {
            $ph = ':status_aprovado_' . $idx;
            $statusPlaceholders[] = $ph;
            $params[$ph] = $status;
        }

        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);
        $where = "paciente_id = :paciente_id
                  AND escala_base_id = :escala_base_id
                  AND status IN (" . implode(', ', $statusPlaceholders) . ")";

        if ($campoFim) {
            $where .= " AND {$campoFim} >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        }

        $stmt = $this->query(
            "UPDATE {$tabela}
             SET " . implode(', ', $sets) . "
             WHERE {$where}",
            $params
        );

        return $stmt->rowCount();
    }

    /**
     * Acrescenta uma linha de histórico operacional na aprovação mais próxima do período.
     */
    public function anotarOperacaoPeriodo(
        int $pacienteId,
        int $escalaBaseId,
        string $periodoInicio,
        string $periodoFim,
        string $observacao
    ): bool {
        $tabela = $this->resolverTabela();
        if (!$tabela) {
            return false;
        }

        $colunas = $this->colunasTabela($tabela);
        if (!isset($colunas['paciente_id'], $colunas['escala_base_id'], $colunas['observacoes'])) {
            return false;
        }

        $campoInicio = isset($colunas['data_inicio']) ? 'data_inicio' : (isset($colunas['periodo_inicio']) ? 'periodo_inicio' : null);
        $campoFim = isset($colunas['data_fim']) ? 'data_fim' : (isset($colunas['periodo_fim']) ? 'periodo_fim' : null);
        $agora = date('Y-m-d H:i:s');
        $linhaHistorico = "\n[{$agora}] " . trim($observacao);

        $params = [
            ':paciente_id' => $pacienteId,
            ':escala_base_id' => $escalaBaseId,
            ':historico' => $linhaHistorico,
        ];

        $sets = ["observacoes = TRIM(CONCAT(COALESCE(observacoes, ''), :historico))"];
        if (isset($colunas['atualizado_em'])) {
            $sets[] = 'atualizado_em = :atualizado_em';
            $params[':atualizado_em'] = $agora;
        }
        if (isset($colunas['updated_at'])) {
            $sets[] = 'updated_at = :updated_at';
            $params[':updated_at'] = $agora;
        }

        $where = 'paciente_id = :paciente_id AND escala_base_id = :escala_base_id';
        if ($campoInicio && $campoFim) {
            $where .= " AND {$campoInicio} <= :periodo_fim AND {$campoFim} >= :periodo_inicio";
            $params[':periodo_inicio'] = $periodoInicio;
            $params[':periodo_fim'] = $periodoFim;
        }

        $stmt = $this->query(
            "UPDATE {$tabela}
             SET " . implode(', ', $sets) . "
             WHERE {$where}
             ORDER BY id DESC
             LIMIT 1",
            $params
        );

        return $stmt->rowCount() > 0;
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


    private function valoresEnumExistentes(string $tabela, string $coluna, array $preferidos): array
    {
        $colunas = $this->colunasTabela($tabela);
        $tipo = (string)($colunas[$coluna]['COLUMN_TYPE'] ?? '');

        if (!str_starts_with(strtolower($tipo), 'enum(')) {
            return array_values(array_filter($preferidos, static fn($valor) => $valor !== null && $valor !== ''));
        }

        preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $tipo, $matches);
        $permitidos = array_map(
            static fn ($valor) => stripcslashes($valor),
            $matches[1] ?? []
        );

        return array_values(array_filter(
            $preferidos,
            static fn ($valor) => in_array($valor, $permitidos, true)
        ));
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
