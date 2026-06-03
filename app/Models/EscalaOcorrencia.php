<?php

namespace App\Models;

use DateTime;
use InvalidArgumentException;

class EscalaOcorrencia extends BaseModuleModel
{
    protected string $table = 'tb_escala_ocorrencias';
    protected string $orderBy = 'inicio';
    protected string $orderDirection = 'ASC';

    protected array $fillable = [
        'escala_base_id',
        'paciente_id',
        'cuidador_id',
        'data_plantao',
        'inicio',
        'fim',
        'tipo_plantao',
        'status',
        'origem',
        'observacoes',
    ];

    protected array $nullable = [
        'cuidador_id',
        'observacoes',
    ];

    private ?bool $escalaProfissionalTemCorEscala = null;

    private function escalaProfissionalTemCorEscala(): bool
    {
        if ($this->escalaProfissionalTemCorEscala !== null) {
            return $this->escalaProfissionalTemCorEscala;
        }

        $row = $this->rawFirst(
            "SHOW COLUMNS FROM tb_escala_profissionais LIKE 'cor_escala'"
        );

        return $this->escalaProfissionalTemCorEscala = (bool)$row;
    }

    /**
     * Mantém compatibilidade com controllers antigos que chamam createRecord().
     * Aqui ele NÃO cria duplicado: ele atualiza o slot existente quando já houver
     * plantão para a mesma escala/paciente/data/início/fim.
     */
    public function createRecord(array $data): int
    {
        return $this->salvarOuAtualizarSlot($data);
    }

    /** Mantém compatibilidade com controllers antigos que chamam updateRecord(). */
    public function updateRecord(int $id, array $data): bool
    {
        return $this->atualizar($id, $data);
    }

    public function criar(array $data): int
    {
        return $this->salvarOuAtualizarSlot($data);
    }

    public function salvarOuAtualizarSlot(array $data): int
    {
        $data = $this->normalizarPayload($data);

        $existente = $this->rawFirst(
            "SELECT id
             FROM {$this->table}
             WHERE escala_base_id = :escala_base_id
               AND paciente_id = :paciente_id
               AND data_plantao = :data_plantao
               AND inicio = :inicio
               AND fim = :fim
             LIMIT 1",
            [
                ':escala_base_id' => (int)$data['escala_base_id'],
                ':paciente_id' => (int)$data['paciente_id'],
                ':data_plantao' => $data['data_plantao'],
                ':inicio' => $data['inicio'],
                ':fim' => $data['fim'],
            ]
        );

        if ($existente) {
            $this->update((int)$existente['id'], $this->filterData($data));
            return (int)$existente['id'];
        }

        return $this->insert($this->filterData($data));
    }

    public function atualizar(int $id, array $data): bool
    {
        $atual = $this->find($id);

        if (!$atual) {
            return false;
        }

        $data = array_merge($atual, $data);
        $data = $this->normalizarPayload($data);

        return $this->update($id, $this->filterData($data));
    }

    public function excluir(int $id): bool
    {
        return $this->delete($id);
    }

    public function trocarCuidadores(int $origemId, int $destinoId): bool
    {
        if ($origemId <= 0 || $destinoId <= 0 || $origemId === $destinoId) {
            return false;
        }

        $origem = $this->find($origemId);
        $destino = $this->find($destinoId);

        if (!$origem || !$destino) {
            return false;
        }

        $this->query(
            "UPDATE {$this->table}
             SET cuidador_id = :cuidador_id
             WHERE id = :id",
            [
                ':cuidador_id' => $destino['cuidador_id'] ?: null,
                ':id' => $origemId,
            ]
        );

        $this->query(
            "UPDATE {$this->table}
             SET cuidador_id = :cuidador_id
             WHERE id = :id",
            [
                ':cuidador_id' => $origem['cuidador_id'] ?: null,
                ':id' => $destinoId,
            ]
        );

        return true;
    }


    /**
     * Fecha o período operacional: plantões confirmados viram finalizados.
     * O contas a pagar dos cuidadores passa a considerar somente estes registros.
     */
    public function finalizarPeriodo(int $escalaBaseId, int $pacienteId, string $periodoInicio, string $periodoFim): int
    {
        if ($escalaBaseId <= 0 || $pacienteId <= 0) {
            return 0;
        }

        $inicio = preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodoInicio)
            ? $periodoInicio
            : date('Y-m-d', strtotime($periodoInicio) ?: time());

        $fim = preg_match('/^\d{4}-\d{2}-\d{2}$/', $periodoFim)
            ? $periodoFim
            : date('Y-m-d', strtotime($periodoFim) ?: time());

        if (strtotime($inicio) > strtotime($fim)) {
            [$inicio, $fim] = [$fim, $inicio];
        }

        $agora = date('Y-m-d H:i:s');
        $linhaHistorico = "\n[" . date('Y-m-d H:i:s') . "] Plantão finalizado pelo fechamento da escala.";

        $stmt = $this->query(
            "UPDATE {$this->table}
                SET status = 'finalizado',
                    observacoes = TRIM(CONCAT(COALESCE(observacoes, ''), :historico)),
                    atualizado_em = :atualizado_em
              WHERE escala_base_id = :escala_base_id
                AND paciente_id = :paciente_id
                AND data_plantao BETWEEN :inicio AND :fim
                AND status IN ('confirmado', 'em_andamento')",
            [
                ':historico' => $linhaHistorico,
                ':atualizado_em' => $agora,
                ':escala_base_id' => $escalaBaseId,
                ':paciente_id' => $pacienteId,
                ':inicio' => $inicio,
                ':fim' => $fim,
            ]
        );

        return $stmt->rowCount();
    }


    /**
     * Cancela o fechamento do período quando ainda não existe financeiro gerado.
     * Plantões finalizados voltam para confirmado, liberando ajustes operacionais.
     */
    public function cancelarFinalizacaoPeriodo(int $escalaBaseId, int $pacienteId, string $periodoInicio, string $periodoFim): int
    {
        if ($escalaBaseId <= 0 || $pacienteId <= 0) {
            return 0;
        }

        $agora = date('Y-m-d H:i:s');
        $linhaHistorico = "\n[{$agora}] Fechamento cancelado. Plantão voltou para confirmado para ajuste operacional.";

        $stmt = $this->query(
            "UPDATE {$this->table} o
                LEFT JOIN tb_financeiro f
                  ON f.escala_ocorrencia_id = o.id
                 AND f.tipo_transacao <> 'Entrada'
                 AND f.status <> 'Cancelado'
                SET o.status = 'confirmado',
                    o.observacoes = TRIM(CONCAT(COALESCE(o.observacoes, ''), :historico)),
                    o.atualizado_em = :atualizado_em
              WHERE o.escala_base_id = :escala_base_id
                AND o.paciente_id = :paciente_id
                AND o.data_plantao BETWEEN :inicio AND :fim
                AND o.status = 'finalizado'
                AND f.id IS NULL",
            [
                ':historico' => $linhaHistorico,
                ':atualizado_em' => $agora,
                ':escala_base_id' => $escalaBaseId,
                ':paciente_id' => $pacienteId,
                ':inicio' => $periodoInicio,
                ':fim' => $periodoFim,
            ]
        );

        return $stmt->rowCount();
    }

    public function contarFinalizadosComFinanceiroPeriodo(int $escalaBaseId, int $pacienteId, string $periodoInicio, string $periodoFim): int
    {
        if ($escalaBaseId <= 0 || $pacienteId <= 0) {
            return 0;
        }

        $row = $this->rawFirst(
            "SELECT COUNT(DISTINCT o.id) AS total
               FROM {$this->table} o
               JOIN tb_financeiro f
                 ON f.escala_ocorrencia_id = o.id
                AND f.tipo_transacao <> 'Entrada'
                AND f.status <> 'Cancelado'
              WHERE o.escala_base_id = :escala_base_id
                AND o.paciente_id = :paciente_id
                AND o.data_plantao BETWEEN :inicio AND :fim
                AND o.status = 'finalizado'",
            [
                ':escala_base_id' => $escalaBaseId,
                ':paciente_id' => $pacienteId,
                ':inicio' => $periodoInicio,
                ':fim' => $periodoFim,
            ]
        );

        return (int)($row['total'] ?? 0);
    }

    public function contarFinalizadosPeriodo(int $escalaBaseId, int $pacienteId, string $periodoInicio, string $periodoFim): int
    {
        if ($escalaBaseId <= 0 || $pacienteId <= 0) {
            return 0;
        }

        $row = $this->rawFirst(
            "SELECT COUNT(*) AS total
               FROM {$this->table}
              WHERE escala_base_id = :escala_base_id
                AND paciente_id = :paciente_id
                AND data_plantao BETWEEN :inicio AND :fim
                AND status = 'finalizado'",
            [
                ':escala_base_id' => $escalaBaseId,
                ':paciente_id' => $pacienteId,
                ':inicio' => $periodoInicio,
                ':fim' => $periodoFim,
            ]
        );

        return (int)($row['total'] ?? 0);
    }

    public function conflito(int $cuidadorId, string $dataPlantao, string $horaInicio, ?string $horaFim = null, ?int $ignorarId = null): bool
    {
        if ($cuidadorId <= 0) {
            return false;
        }

        if ($horaFim === null && $this->pareceDateTime($dataPlantao) && $this->pareceDateTime($horaInicio)) {
            $inicio = $this->formatarDateTime($dataPlantao);
            $fim = $this->formatarDateTime($horaInicio);
        } else {
            [$inicio, $fim] = $this->montarInicioFim($dataPlantao, $horaInicio, $horaFim ?? '19:00');
        }

        $sql = "SELECT id
                FROM {$this->table}
                WHERE cuidador_id = :cuidador_id
                  AND inicio < :fim
                  AND fim > :inicio";

        $params = [
            ':cuidador_id' => $cuidadorId,
            ':inicio' => $inicio,
            ':fim' => $fim,
        ];

        if ($ignorarId) {
            $sql .= " AND id <> :id";
            $params[':id'] = $ignorarId;
        }

        $sql .= " LIMIT 1";

        return (bool)$this->rawFirst($sql, $params);
    }


    /**
     * Compatibilidade com o EscalaController.
     * O controller chama porSemana($inicio, $fim, $pacienteId, $cuidadorId),
     * enquanto o método principal do model é porPeriodo($pacienteId, $cuidadorId, $inicio, $fim).
     */
    public function porSemana(string $inicio, string $fim, ?int $pacienteId = null, ?int $cuidadorId = null): array
    {
        $inicio = $this->normalizarInicioPeriodo($inicio);
        $fim = $this->normalizarFimPeriodo($fim);

        return $this->porPeriodo($pacienteId, $cuidadorId, $inicio, $fim);
    }

    private function normalizarInicioPeriodo(string $inicio): string
    {
        $inicio = trim($inicio);

        if ($inicio === '') {
            return date('Y-m-d 00:00:00');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) {
            return $inicio . ' 00:00:00';
        }

        return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $inicio)) ?: time());
    }

    private function normalizarFimPeriodo(string $fim): string
    {
        $fim = trim($fim);

        if ($fim === '') {
            return date('Y-m-d 23:59:59');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fim)) {
            return date('Y-m-d 00:00:00', strtotime($fim . ' +1 day'));
        }

        return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $fim)) ?: time());
    }

    public function porPeriodo(?int $pacienteId, ?int $cuidadorId, string $inicio, string $fim): array
    {
        $where = ['eo.inicio >= :inicio', 'eo.inicio < :fim'];
        $params = [
            ':inicio' => $inicio,
            ':fim' => $fim,
        ];

        if ($pacienteId) {
            $where[] = 'eo.paciente_id = :paciente_id';
            $params[':paciente_id'] = $pacienteId;
        }

        if ($cuidadorId) {
            $where[] = 'eo.cuidador_id = :cuidador_id';
            $params[':cuidador_id'] = $cuidadorId;
        }

        $corSelect = $this->escalaProfissionalTemCorEscala()
            ? 'ep.cor_escala AS cuidador_cor'
            : 'NULL AS cuidador_cor';

        return $this->rawAll(
            "SELECT
                eo.*,
                p.uuid AS paciente_uuid,
                p.nome_completo AS paciente_nome,
                c.uuid AS cuidador_uuid,
                c.nome_completo AS cuidador_nome,
                {$corSelect}
             FROM {$this->table} eo
             INNER JOIN tb_pacientes p ON p.id = eo.paciente_id
             LEFT JOIN tb_cuidador c ON c.id = eo.cuidador_id
             LEFT JOIN tb_escala_profissionais ep
                ON ep.escala_base_id = eo.escala_base_id
               AND ep.cuidador_id = eo.cuidador_id
               AND ep.ativo = 1
             WHERE " . implode(' AND ', $where) . "
             ORDER BY eo.inicio ASC, p.nome_completo ASC",
            $params
        );
    }

    private function normalizarPayload(array $data): array
    {
        $escalaBaseId = (int)($data['escala_base_id'] ?? 0);

        if ($escalaBaseId <= 0) {
            throw new InvalidArgumentException('Escala base não informada para gerar a ocorrência.');
        }

        $dataPlantao = (string)($data['data_plantao'] ?? '');
        if ($dataPlantao === '') {
            $dataPlantao = substr((string)($data['inicio'] ?? date('Y-m-d')), 0, 10);
        }

        [$inicio, $fim] = $this->resolverInicioFim($data, $dataPlantao);

        $data['paciente_id'] = $this->resolverPacienteId($data, $escalaBaseId);
        $data['data_plantao'] = $dataPlantao;
        $data['inicio'] = $inicio;
        $data['fim'] = $fim;
        $data['tipo_plantao'] = $this->resolverTipoPlantao($data, $inicio, $fim);
        $data['status'] = $data['status'] ?? 'confirmado';
        $data['origem'] = $this->resolverOrigem($data['origem'] ?? null);
        $data['cuidador_id'] = !empty($data['cuidador_id']) ? (int)$data['cuidador_id'] : null;
        $data['observacoes'] = $this->nullIfEmpty($data['observacoes'] ?? null);

        return $data;
    }

    private function resolverPacienteId(array $data, int $escalaBaseId): int
    {
        $pacienteId = (int)($data['paciente_id'] ?? 0);

        if ($pacienteId > 0) {
            return $pacienteId;
        }

        $base = $this->rawFirst(
            'SELECT paciente_id FROM tb_escala_base WHERE id = :id LIMIT 1',
            [':id' => $escalaBaseId]
        );

        $pacienteId = (int)($base['paciente_id'] ?? 0);

        if ($pacienteId <= 0) {
            throw new InvalidArgumentException('Paciente não encontrado para a escala base informada.');
        }

        return $pacienteId;
    }

    private function resolverInicioFim(array $data, string $dataPlantao): array
    {
        $inicio = (string)($data['inicio'] ?? '');
        $fim = (string)($data['fim'] ?? '');

        if ($this->pareceDateTime($inicio) && $this->pareceDateTime($fim)) {
            return [$this->formatarDateTime($inicio), $this->formatarDateTime($fim)];
        }

        $horaInicio = (string)($data['hora_inicio'] ?? $inicio ?? '');
        $horaFim = (string)($data['hora_fim'] ?? $fim ?? '');

        return $this->montarInicioFim($dataPlantao, $horaInicio, $horaFim);
    }

    private function montarInicioFim(string $dataPlantao, string $horaInicio, string $horaFim): array
    {
        $horaInicio = substr(trim($horaInicio), 0, 5);
        $horaFim = substr(trim($horaFim), 0, 5);

        if ($horaInicio === '') {
            $horaInicio = '07:00';
        }

        if ($horaFim === '') {
            $horaFim = '19:00';
        }

        $inicio = new DateTime($dataPlantao . ' ' . $horaInicio . ':00');
        $fim = new DateTime($dataPlantao . ' ' . $horaFim . ':00');

        if ($fim <= $inicio) {
            $fim->modify('+1 day');
        }

        return [$inicio->format('Y-m-d H:i:s'), $fim->format('Y-m-d H:i:s')];
    }

    private function resolverTipoPlantao(array $data, string $inicio, string $fim): string
    {
        $tipo = (string)($data['tipo_plantao'] ?? $data['tipo_cobertura'] ?? '');
        $validos = ['24h', '12h', '8h', '6h'];

        if (in_array($tipo, $validos, true)) {
            return $tipo;
        }

        $horas = max(1, (strtotime($fim) - strtotime($inicio)) / 3600);

        if ($horas >= 23) {
            return '24h';
        }

        if ($horas <= 6) {
            return '6h';
        }

        if ($horas <= 8) {
            return '8h';
        }

        return '12h';
    }

    private function resolverOrigem(?string $origem): string
    {
        $origem = trim((string)$origem);

        // Sua tabela atual usa valores como "Manual". Evita o erro de enum com "aprovacao".
        if ($origem === '' || in_array(strtolower($origem), ['aprovacao', 'aprovação', 'manual'], true)) {
            return 'Manual';
        }

        if (in_array(strtolower($origem), ['automatico', 'automático', 'automatica', 'automática'], true)) {
            return 'Automatica';
        }

        if (in_array(strtolower($origem), ['substituicao', 'substituição'], true)) {
            return 'Substituicao';
        }

        return 'Manual';
    }

    private function pareceDateTime(string $value): bool
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/', $value);
    }

    private function formatarDateTime(string $value): string
    {
        return date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $value)));
    }

    private function nullIfEmpty(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }
}
