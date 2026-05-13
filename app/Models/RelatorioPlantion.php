<?php

namespace App\Models;

/**
 * Model for Relatório de Plantão
 *
 * Status values:
 *   - turno:      concluido | intercorrencia | andamento
 *   - sinal:      normal | atencao | critico
 *   - medicacao:  administrado | pendente
 */
class RelatorioPlantion extends BaseModuleModel
{
    protected string $table          = 'tb_relatorio_plantao';
    protected string $searchColumn   = 'p.nome_completo';
    protected string $orderBy        = 'r.data_plantao';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'paciente_id',
        'data_plantao',
        'turno',              // manha | tarde | noite
        'enfermeiro_id',
        'status_turno',       // concluido | intercorrencia | andamento
        'evolucao',
        'assinado',
        'assinado_em',
    ];

    protected array $nullable = [
        'evolucao',
        'assinado_em',
    ];

    // -------------------------------------------------------------------------
    // Leitura
    // -------------------------------------------------------------------------

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where  = $search !== ''
            ? ' WHERE p.nome_completo LIKE :search_paciente OR e.nome LIKE :search_enfermeiro'
            : '';
        $params = $search !== '' ? [
            ':search_paciente'   => "%{$search}%",
            ':search_enfermeiro' => "%{$search}%",
        ] : [];

        $total = (int) $this->query(
            'SELECT COUNT(*) FROM tb_relatorio_plantao r
             JOIN tb_pacientes p  ON p.id  = r.paciente_id
             JOIN tb_usuarios  e  ON e.id  = r.enfermeiro_id' . $where,
            $params
        )->fetchColumn();

        $data = $this->query(
            $this->baseSelect() . $where . ' ORDER BY r.data_plantao DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data'         => $data,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) max(1, ceil($total / $perPage)),
        ];
    }

    /** Retorna todos os turnos do paciente em uma data específica */
    public function findByPacienteAndDate(int $pacienteId, string $data): array
    {
        return $this->rawAll(
            $this->baseSelect() . ' WHERE r.paciente_id = :paciente_id AND r.data_plantao = :data ORDER BY r.turno ASC',
            [':paciente_id' => $pacienteId, ':data' => $data]
        );
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst($this->baseSelect() . ' WHERE r.id = :id', [':id' => $id]);
    }

    /** Retorna sinais vitais de um relatório */
    public function getSinaisVitais(int $relatorioId): array
    {
        return $this->rawAll(
            'SELECT * FROM tb_sinais_vitais WHERE relatorio_id = :id ORDER BY id ASC',
            [':id' => $relatorioId]
        );
    }

    /** Retorna medicações de um relatório */
    public function getMedicacoes(int $relatorioId): array
    {
        return $this->rawAll(
            'SELECT * FROM tb_medicacoes_plantao WHERE relatorio_id = :id ORDER BY horario_previsto ASC',
            [':id' => $relatorioId]
        );
    }

    /** Retorna intercorrências de um relatório */
    public function getIntercorrencias(int $relatorioId): array
    {
        return $this->rawAll(
            'SELECT * FROM tb_intercorrencias WHERE relatorio_id = :id ORDER BY horario ASC',
            [':id' => $relatorioId]
        );
    }

    // -------------------------------------------------------------------------
    // Assinatura
    // -------------------------------------------------------------------------

    public function assinar(int $id): bool
    {
        return $this->update($id, [
            'assinado'    => 1,
            'assinado_em' => date('Y-m-d H:i:s'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Form helpers
    // -------------------------------------------------------------------------

    public function formOptions(): array
    {
        return ['paciente_id' => $this->activePatients()];
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function baseSelect(): string
    {
        return 'SELECT r.*,
                       p.nome_completo AS paciente_nome,
                       p.data_nascimento,
                       p.diagnostico,
                       e.nome AS enfermeiro_nome,
                       e.coren AS enfermeiro_coren
                FROM tb_relatorio_plantao r
                JOIN tb_pacientes p ON p.id = r.paciente_id
                JOIN tb_usuarios  e ON e.id = r.enfermeiro_id';
    }
}
