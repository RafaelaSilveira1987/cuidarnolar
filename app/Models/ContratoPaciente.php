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


    public function contratoAtivoPorPaciente(int $pacienteId): array|false
    {
        return $this->rawFirst(
            "SELECT *
             FROM {$this->table}
             WHERE paciente_id = :paciente_id
               AND status = 'Ativo'
             ORDER BY vigencia_inicio DESC, id DESC
             LIMIT 1",
            [':paciente_id' => $pacienteId]
        );
    }

    public function historicoPorPaciente(int $pacienteId): array
    {
        return $this->rawAll(
            "SELECT *
             FROM {$this->table}
             WHERE paciente_id = :paciente_id
             ORDER BY
                CASE WHEN status = 'Ativo' THEN 0 ELSE 1 END,
                vigencia_inicio DESC,
                id DESC",
            [':paciente_id' => $pacienteId]
        );
    }

    public function inferirTipoCobertura(?array $contrato): string
    {
        $texto = mb_strtolower((string)($contrato['tipo_servico'] ?? ''), 'UTF-8');

        foreach (['24h', '12h', '8h', '6h'] as $tipo) {
            if (str_contains($texto, $tipo)) {
                return $tipo;
            }
        }

        return '12h';
    }


    public function salvarAtivoPaciente(int $pacienteId, array $data): array|false
    {
        $tipoServico = trim((string)($data['tipo_servico'] ?? ''));
        if ($tipoServico === '') {
            return $this->contratoAtivoPorPaciente($pacienteId);
        }

        $payload = [
            'paciente_id' => $pacienteId,
            'tipo_servico' => $tipoServico,
            'valor_mensal' => $this->normalizarValorMonetario($data['valor_mensal'] ?? 0),
            'dia_vencimento' => (int)($data['dia_vencimento'] ?? 10),
            'forma_pagamento' => trim((string)($data['forma_pagamento'] ?? '')) ?: null,
            'vigencia_inicio' => trim((string)($data['vigencia_inicio'] ?? date('Y-m-d'))) ?: date('Y-m-d'),
            'vigencia_fim' => trim((string)($data['vigencia_fim'] ?? '')) ?: null,
            'status' => in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Inativo', 'Encerrado'], true) ? $data['status'] : 'Ativo',
            'observacoes' => trim((string)($data['observacoes'] ?? '')) ?: null,
        ];

        $ativo = $this->contratoAtivoPorPaciente($pacienteId);
        if ($ativo) {
            $this->updateRecord((int)$ativo['id'], $payload);
            return $this->findForShow((int)$ativo['id']);
        }

        $id = $this->createRecord($payload);
        return $this->findForShow((int)$id);
    }

    private function normalizarValorMonetario(mixed $valor): float
    {
        $str = trim((string)$valor);
        if ($str === '') {
            return 0.0;
        }

        $str = str_replace(['R$', ' '], '', $str);
        if (str_contains($str, ',') && str_contains($str, '.')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (str_contains($str, ',')) {
            $str = str_replace(',', '.', $str);
        }

        return (float)$str;
    }

    public function formOptions(): array
    {
        return ['paciente_id' => $this->activePatients()];
    }
}
