<?php

namespace App\Models;

class ContratoPaciente extends BaseModuleModel
{
    protected string $table = 'tb_contratos_paciente';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'c.vigencia_inicio';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'paciente_id',
        'tipo_servico',
        'valor_mensal',
        'dia_vencimento',
        'forma_pagamento',
        'vigencia_inicio',
        'vigencia_fim',
        'status',
        'observacoes',
    ];

    protected array $nullable = [
        'forma_pagamento',
        'vigencia_fim',
        'observacoes',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = ' WHERE p.nome_completo LIKE :s OR c.tipo_servico LIKE :s2';
            $params[':s'] = "%{$search}%";
            $params[':s2'] = "%{$search}%";
        }

        $total = (int) $this->query(
            'SELECT COUNT(*) FROM ' . $this->table . ' c JOIN tb_pacientes p ON p.id = c.paciente_id' . $where,
            $params
        )->fetchColumn();

        $data = $this->query(
            'SELECT c.*, p.nome_completo AS paciente_nome
             FROM ' . $this->table . ' c
             JOIN tb_pacientes p ON p.id = c.paciente_id' . $where . '
             ORDER BY c.vigencia_inicio DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        foreach ($data as &$row) {
            $row['valor_mensal_fmt'] = formatMoney((float) ($row['valor_mensal'] ?? 0));
        }

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    public function findForShow(int $id): array|false
    {
        return $this->rawFirst(
            'SELECT c.*, p.nome_completo AS paciente_nome
             FROM ' . $this->table . ' c
             JOIN tb_pacientes p ON p.id = c.paciente_id
             WHERE c.id = :id',
            [':id' => $id]
        );
    }

    public function formOptions(): array
    {
        return ['paciente_id' => $this->activePatients()];
    }
}
