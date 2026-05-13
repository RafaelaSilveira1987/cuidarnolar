<?php

namespace App\Models;

class Financeiro extends BaseModuleModel
{
    protected string $table = 'tb_financeiro';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'f.data';
    protected string $orderDirection = 'DESC';
    protected array $fillable = [
        'responsavel_id',
        'cuidador_id',
        'paciente_id',
        'plano_id',
        'data',
        'tipo_transacao',
        'moeda',
        'valor',
        'status',
        'observacoes',
    ];
    protected array $nullable = [
        'responsavel_id',
        'cuidador_id',
        'paciente_id',
        'plano_id',
        'moeda',
        'valor',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        return $this->listByType($page, $perPage, $search);
    }

    public function listByType(int $page = 1, int $perPage = 15, string $search = '', string $tipo = ''): array
    {
        return $this->listWithJoins($page, $perPage, $search, $tipo);
    }

    public function findForShow(int $id): array|false
    {
        $record = $this->rawFirst($this->baseSelect() . ' WHERE f.id = :id', [':id' => $id]);
        return $record ? $this->formatRecord($record) : false;
    }

    public function resumo(): array
    {
        return $this->rawFirst(
            "SELECT
                SUM(CASE WHEN tipo_transacao = 'Entrada' THEN valor ELSE 0 END) AS entradas,
                SUM(CASE WHEN tipo_transacao <> 'Entrada' THEN valor ELSE 0 END) AS saidas,
                SUM(CASE WHEN status = 'Pendente' THEN 1 ELSE 0 END) AS pendentes
             FROM tb_financeiro"
        ) ?: ['entradas' => 0, 'saidas' => 0, 'pendentes' => 0];
    }

    public function formOptions(): array
    {
        return [
            'paciente_id' => $this->activePatients(),
            'responsavel_id' => $this->activeResponsibles(),
            'cuidador_id' => $this->activeCaregivers(),
        ];
    }

    private function listWithJoins(int $page, int $perPage, string $search, string $tipo = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $whereParts = [];
        $params = [];

        if ($search !== '') {
            $whereParts[] = '(p.nome_completo LIKE :search_paciente OR r.nome_completo LIKE :search_responsavel OR c.nome_completo LIKE :search_cuidador)';
            $params[':search_paciente'] = "%{$search}%";
            $params[':search_responsavel'] = "%{$search}%";
            $params[':search_cuidador'] = "%{$search}%";
        }

        if ($tipo === 'entrada') {
            $whereParts[] = "f.tipo_transacao = 'Entrada'";
        } elseif ($tipo === 'saida') {
            $whereParts[] = "f.tipo_transacao <> 'Entrada'";
        }

        $where = $whereParts ? ' WHERE ' . implode(' AND ', $whereParts) : '';
        $total = (int) $this->query($this->baseCount() . $where, $params)->fetchColumn();
        $rows = $this->query(
            $this->baseSelect() . $where . ' ORDER BY f.data DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data' => array_map(fn (array $row): array => $this->formatRecord($row), $rows),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    private function baseSelect(): string
    {
        return "SELECT f.*, p.nome_completo AS paciente_nome, r.nome_completo AS responsavel_nome, c.nome_completo AS cuidador_nome
                FROM tb_financeiro f
                LEFT JOIN tb_pacientes p ON p.id = f.paciente_id
                LEFT JOIN tb_responsavel r ON r.id = f.responsavel_id
                LEFT JOIN tb_cuidador c ON c.id = f.cuidador_id";
    }

    private function baseCount(): string
    {
        return "SELECT COUNT(*)
                FROM tb_financeiro f
                LEFT JOIN tb_pacientes p ON p.id = f.paciente_id
                LEFT JOIN tb_responsavel r ON r.id = f.responsavel_id
                LEFT JOIN tb_cuidador c ON c.id = f.cuidador_id";
    }

    private function formatRecord(array $row): array
    {
        $row['valor_formatado'] = formatMoney((float) ($row['valor'] ?? 0));
        return $row;
    }
}
