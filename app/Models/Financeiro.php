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
        'categoria_id',
        'moeda',
        'valor',
        'status',
        'data_vencimento',
        'data_pagamento',
        'observacoes',
    ];
    protected array $nullable = [
        'responsavel_id',
        'cuidador_id',
        'paciente_id',
        'plano_id',
        'categoria_id',
        'moeda',
        'valor',
        'data_vencimento',
        'data_pagamento',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        return $this->listByType($page, $perPage, $search);
    }

    public function listByType(int $page = 1, int $perPage = 15, string $search = '', string $tipo = ''): array
    {
        return $this->listWithJoins($page, $perPage, $search, $tipo, false, '');
    }

    /** Contas a receber: entradas pendentes (camada 3). */
    public function listContasReceber(int $page = 1, int $perPage = 20): array
    {
        return $this->listWithJoins($page, $perPage, '', 'entrada', true, 'receber');
    }

    /** Contas a pagar: saídas pendentes. */
    public function listContasPagar(int $page = 1, int $perPage = 20): array
    {
        return $this->listWithJoins($page, $perPage, '', 'saida', true, 'pagar');
    }

    /** Extrato por paciente e período (camada 4). */
    public function extratoPorPaciente(int $pacienteId, string $dataInicio, string $dataFim): array
    {
        $sql = $this->baseSelect() . ' WHERE f.paciente_id = :pid AND DATE(f.data) BETWEEN :i AND :f ORDER BY f.data ASC, f.id ASC';

        return $this->query($sql, [':pid' => $pacienteId, ':i' => $dataInicio, ':f' => $dataFim])->fetchAll();
    }

    /** Fluxo de caixa agregado por mês. */
    public function fluxoCaixaPorMes(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT DATE_FORMAT(f.data, '%Y-%m') AS mes,
                       SUM(CASE WHEN f.tipo_transacao = 'Entrada' THEN f.valor ELSE 0 END) AS entradas,
                       SUM(CASE WHEN f.tipo_transacao <> 'Entrada' THEN f.valor ELSE 0 END) AS saidas
                FROM {$this->table} f
                WHERE DATE(f.data) BETWEEN :i AND :f
                GROUP BY DATE_FORMAT(f.data, '%Y-%m')
                ORDER BY mes ASC";

        return $this->query($sql, [':i' => $dataInicio, ':f' => $dataFim])->fetchAll();
    }

    /** Inadimplência: receitas pendentes com vencimento anterior a hoje. */
    public function listInadimplencia(): array
    {
        $sql = $this->baseSelect() . " WHERE f.tipo_transacao = 'Entrada'
                AND f.status = 'Pendente'
                AND COALESCE(f.data_vencimento, DATE(f.data)) < CURDATE()
                ORDER BY COALESCE(f.data_vencimento, DATE(f.data)) ASC";

        return $this->query($sql)->fetchAll();
    }

    /** DRE simplificado (somente pagos no período). */
    public function dreSimplificado(string $dataInicio, string $dataFim): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN tipo_transacao = 'Entrada' AND status = 'Pago' THEN valor ELSE 0 END) AS receita_bruta,
                    SUM(CASE WHEN tipo_transacao <> 'Entrada' AND status = 'Pago' AND cuidador_id IS NOT NULL THEN valor ELSE 0 END) AS custos_cuidadores,
                    SUM(CASE WHEN tipo_transacao <> 'Entrada' AND status = 'Pago' AND cuidador_id IS NULL THEN valor ELSE 0 END) AS despesas_operacionais
                FROM {$this->table}
                WHERE DATE(data) BETWEEN :i AND :f";

        $row = $this->query($sql, [':i' => $dataInicio, ':f' => $dataFim])->fetch() ?: [];

        $receita = (float) ($row['receita_bruta'] ?? 0);
        $custos = (float) ($row['custos_cuidadores'] ?? 0);
        $desp = (float) ($row['despesas_operacionais'] ?? 0);

        return [
            'receita_bruta' => $receita,
            'custos_cuidadores' => $custos,
            'despesas_operacionais' => $desp,
            'resultado' => $receita - $custos - $desp,
        ];
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
        $cats = [];
        try {
            $cats = (new CategoriaFinanceira())->listForSelect();
        } catch (\Throwable) {
        }

        return [
            'paciente_id' => $this->activePatients(),
            'responsavel_id' => $this->activeResponsibles(),
            'cuidador_id' => $this->activeCaregivers(),
            'categoria_id' => $cats,
        ];
    }

    private function listWithJoins(
        int $page,
        int $perPage,
        string $search,
        string $tipo = '',
        bool $onlyPending = false,
        string $contaModo = ''
    ): array {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $whereParts = [];
        $params = [];

        if ($search !== '') {
            $whereParts[] = '(p.nome_completo LIKE :search_paciente OR r.nome_completo LIKE :search_responsavel OR c.nome_completo LIKE :search_cuidador OR cat.nome LIKE :search_cat)';
            $params[':search_paciente'] = "%{$search}%";
            $params[':search_responsavel'] = "%{$search}%";
            $params[':search_cuidador'] = "%{$search}%";
            $params[':search_cat'] = "%{$search}%";
        }

        if ($tipo === 'entrada') {
            $whereParts[] = "f.tipo_transacao = 'Entrada'";
        } elseif ($tipo === 'saida') {
            $whereParts[] = "f.tipo_transacao <> 'Entrada'";
        }

        if ($onlyPending) {
            $whereParts[] = "f.status = 'Pendente'";
        }

        if ($contaModo === 'receber') {
            $whereParts[] = "f.tipo_transacao = 'Entrada'";
        } elseif ($contaModo === 'pagar') {
            $whereParts[] = "f.tipo_transacao <> 'Entrada'";
        }

        $where = $whereParts ? ' WHERE ' . implode(' AND ', $whereParts) : '';
        $total = (int) $this->query($this->baseCount() . $where, $params)->fetchColumn();
        $rows = $this->query(
            $this->baseSelect() . $where . ' ORDER BY f.data DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data' => array_map(fn(array $row): array => $this->formatRecord($row), $rows),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    private function baseSelect(): string
    {
        return "SELECT f.*, p.nome_completo AS paciente_nome, r.nome_completo AS responsavel_nome, c.nome_completo AS cuidador_nome,
                       cat.nome AS categoria_nome
                FROM tb_financeiro f
                LEFT JOIN tb_pacientes p ON p.id = f.paciente_id
                LEFT JOIN tb_responsavel r ON r.id = f.responsavel_id
                LEFT JOIN tb_cuidador c ON c.id = f.cuidador_id
                LEFT JOIN tb_categorias_financeiro cat ON cat.id = f.categoria_id";
    }

    private function baseCount(): string
    {
        return "SELECT COUNT(*)
                FROM tb_financeiro f
                LEFT JOIN tb_pacientes p ON p.id = f.paciente_id
                LEFT JOIN tb_responsavel r ON r.id = f.responsavel_id
                LEFT JOIN tb_cuidador c ON c.id = f.cuidador_id
                LEFT JOIN tb_categorias_financeiro cat ON cat.id = f.categoria_id";
    }

    private function formatRecord(array $row): array
{
    $row['valor_formatado'] = formatMoney((float) ($row['valor'] ?? 0));

    $row['vencimento_exibicao'] =
        $row['data_vencimento']
        ?? (isset($row['data']) ? substr((string) $row['data'], 0, 10) : '');

    $row['atrasado'] = (
        strtolower($row['status'] ?? '') === 'pendente'
        &&
        !empty($row['data_vencimento'])
        &&
        strtotime($row['data_vencimento']) <= strtotime(date('Y-m-d'))
    );

    return $row;
}

    /**
     * Retorna o resumo financeiro (receitas, despesas, a receber, resultado).
     */
    public function dashboardResumo(): array
    {
        // Ajustando para usar a tabela financeiro
        return [
            'receitas' => $this->db->query("SELECT SUM(valor) FROM tb_financeiro WHERE tipo_transacao = 'Entrada'")->fetchColumn() ?? 0,
            'despesas' => $this->db->query("SELECT SUM(valor) FROM tb_financeiro WHERE tipo_transacao = 'Saida'")->fetchColumn() ?? 0,
            'a_receber' => $this->db->query("SELECT SUM(valor) FROM tb_financeiro WHERE status = 'Pendente' AND tipo_transacao = 'Entrada'")->fetchColumn() ?? 0,
        ];
    }

    /**
     * Retorna contagens de lançamentos e contratos.
     */
    public function dashboardCounts(): array
    {
        return [
            'lancamentos' => $this->db->query("SELECT COUNT(*) FROM tb_financeiro")->fetchColumn() ?? 0,
            'contratos_ativos' => $this->db->query("SELECT COUNT(*) FROM tb_contratos_paciente WHERE status = 'Ativo'")->fetchColumn() ?? 0,
            'receber_vencidas' => $this->db->query("SELECT COUNT(*) FROM tb_financeiro WHERE status = 'Pendente' AND tipo_transacao = 'Entrada' AND data_vencimento < NOW()")
                ->fetchColumn() ?? 0,
            'pagar_pendentes' => $this->db->query("SELECT COUNT(*) FROM tb_financeiro WHERE status = 'Pendente' AND tipo_transacao = 'Saida'")->fetchColumn() ?? 0,
        ];
    }

    /**
     * Retorna alertas de pendências financeiras.
     */
    public function dashboardAlertas(): array
{
    $sql = "
        SELECT
            COALESCE(
                descricao,
                observacoes,
                'Lançamento pendente'
            ) AS texto,

            COALESCE(
                detalhes,
                'Sem detalhes'
            ) AS detalhe

        FROM tb_financeiro

        WHERE status = 'Pendente'

        ORDER BY id DESC

        LIMIT 5
    ";

    return $this->db
        ->query($sql)
        ->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}
}