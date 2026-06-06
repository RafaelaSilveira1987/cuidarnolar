<?php

namespace App\Models;

class ContratoPaciente extends BaseModuleModel
{
    protected string $table = 'tb_contratos_paciente';
    protected string $searchColumn = 'p.nome_completo';
    protected string $orderBy = 'c.created_at';
    protected string $orderDirection = 'DESC';

    protected array $fillable = [
        'paciente_id',
        'responsavel_legal_id',
        'responsavel_financeiro_id',
        'tipo_servico',
        'servicos_contratados',
        'escala_contratada',
        'tipo_plantao',
        'hora_inicio_padrao',
        'hora_fim_padrao',
        'tipo_prazo',
        'tipo_cobranca',
        'valor_contrato',
        'valor_mensal',
        'valor_semanal',
        'valor_plantao',
        'dia_vencimento',
        'forma_pagamento',
        'multa_percentual',
        'juros_percentual',
        'vigencia_inicio',
        'vigencia_fim',
        'empresa_razao_social',
        'empresa_cnpj',
        'empresa_endereco',
        'empresa_responsavel_contrato',
        'paciente_snapshot',
        'responsavel_legal_snapshot',
        'responsavel_financeiro_snapshot',
        'empresa_snapshot',
        'status',
        'observacoes',
    ];

    protected array $nullable = [
        'responsavel_legal_id',
        'responsavel_financeiro_id',
        'servicos_contratados',
        'escala_contratada',
        'tipo_plantao',
        'hora_inicio_padrao',
        'hora_fim_padrao',
        'tipo_prazo',
        'tipo_cobranca',
        'valor_contrato',
        'valor_semanal',
        'valor_plantao',
        'vigencia_fim',
        'empresa_razao_social',
        'empresa_cnpj',
        'empresa_endereco',
        'empresa_responsavel_contrato',
        'paciente_snapshot',
        'responsavel_legal_snapshot',
        'responsavel_financeiro_snapshot',
        'empresa_snapshot',
        'observacoes',
    ];

    public function listForIndex(int $page = 1, int $perPage = 15, string $search = ''): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = '';
        $params = [];

        if ($search !== '') {
            $where = ' WHERE p.nome_completo LIKE :search OR c.tipo_servico LIKE :search OR c.escala_contratada LIKE :search';
            $params[':search'] = "%{$search}%";
        }

        $total = (int)$this->query(
            'SELECT COUNT(*) FROM tb_contratos_paciente c JOIN tb_pacientes p ON p.id = c.paciente_id' . $where,
            $params
        )->fetchColumn();

        $rows = $this->query(
            $this->baseSelect() . $where . ' ORDER BY c.created_at DESC, c.id DESC LIMIT :limit OFFSET :offset',
            $params + [':limit' => $perPage, ':offset' => $offset]
        )->fetchAll();

        return [
            'data' => array_map(fn(array $row): array => $this->formatContrato($row), $rows),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int)max(1, ceil($total / $perPage)),
        ];
    }

    public function contratoAtivoPorPaciente(int $pacienteId): array|false
    {
        $row = $this->rawFirst(
            $this->baseSelect() . " WHERE c.paciente_id = :paciente_id AND c.status = 'Ativo' ORDER BY c.vigencia_inicio DESC, c.id DESC LIMIT 1",
            [':paciente_id' => $pacienteId]
        );

        return $row ? $this->formatContrato($row) : false;
    }

    public function historicoPorPaciente(int $pacienteId): array
    {
        $rows = $this->rawAll(
            $this->baseSelect() . ' WHERE c.paciente_id = :paciente_id ORDER BY c.vigencia_inicio DESC, c.id DESC',
            [':paciente_id' => $pacienteId]
        );

        return array_map(fn(array $row): array => $this->formatContrato($row), $rows);
    }

    public function findByPaciente(int $pacienteId, int $contratoId): array|false
    {
        $row = $this->rawFirst(
            $this->baseSelect() . ' WHERE c.paciente_id = :paciente_id AND c.id = :id LIMIT 1',
            [':paciente_id' => $pacienteId, ':id' => $contratoId]
        );

        return $row ? $this->formatContrato($row) : false;
    }

    public function findByPacienteUuid(int $pacienteId, string $contratoUuid): array|false
    {
        $contratoUuid = trim($contratoUuid);
        if ($contratoUuid === '') {
            return false;
        }

        $row = $this->rawFirst(
            $this->baseSelect() . ' WHERE c.paciente_id = :paciente_id AND c.uuid = :uuid LIMIT 1',
            [':paciente_id' => $pacienteId, ':uuid' => $contratoUuid]
        );

        return $row ? $this->formatContrato($row) : false;
    }

    /** Compatibilidade com a tela antiga de contrato + escala. */
    public function salvarAtivoPaciente(int $pacienteId, array $data): int
    {
        $existente = $this->contratoAtivoPorPaciente($pacienteId);

        return $this->salvarContratoCompleto(
            $pacienteId,
            [
                'tipo_servico' => $data['tipo_servico'] ?? 'Contrato home care',
                'servicos_contratados' => [$data['tipo_servico'] ?? 'Cuidador'],
                'valor_mensal' => $data['valor_mensal'] ?? '0',
                'dia_vencimento' => $data['dia_vencimento'] ?? '10',
                'forma_pagamento' => $data['forma_pagamento'] ?? '',
                'vigencia_inicio' => $data['vigencia_inicio'] ?? date('Y-m-d'),
                'vigencia_fim' => $data['vigencia_fim'] ?? '',
                'status' => $data['status'] ?? 'Ativo',
                'observacoes' => $data['observacoes'] ?? '',
            ],
            null,
            $existente ? (int)$existente['id'] : null
        );
    }

    public function salvarContratoCompleto(int $pacienteId, array $data, ?array $paciente = null, ?int $contratoId = null): int
    {
        $normalized = $this->normalizarContrato($pacienteId, $data, $paciente);

        if (($normalized['status'] ?? '') === 'Ativo') {
            $this->encerrarOutrosAtivos($pacienteId, $contratoId);
        }

        $normalized = $this->filterExistingColumns($normalized);

        if ($contratoId !== null && $contratoId > 0) {
            $this->update($contratoId, $normalized);
            return $contratoId;
        }

        return $this->insert($normalized);
    }

    public function inferirTipoCobertura(?array $contrato): string
    {
        if (!$contrato) {
            return '12h';
        }

        $texto = mb_strtolower((string)(
            ($contrato['tipo_plantao'] ?? '') . ' ' .
            ($contrato['escala_contratada'] ?? '') . ' ' .
            ($contrato['tipo_servico'] ?? '')
        ), 'UTF-8');

        foreach (['24h', '12h', '8h', '6h'] as $tipo) {
            if (str_contains($texto, $tipo)) {
                return $tipo;
            }
        }

        return '12h';
    }

    public function empresaPadrao(): array
    {
        return [
            'empresa_razao_social' => defined('APP_COMPANY_NAME') ? (string)APP_COMPANY_NAME : 'Cuidar no Lar',
            'empresa_cnpj' => '',
            'empresa_endereco' => '',
            'empresa_responsavel_contrato' => '',
        ];
    }

    public function resumoFinanceiroContrato(int $contratoId): array
    {
        try {
            $row = $this->rawFirst(
                "SELECT
                    COUNT(*) AS total_lancamentos,
                    SUM(CASE WHEN status = 'Pendente' THEN valor ELSE 0 END) AS total_pendente,
                    SUM(CASE WHEN status = 'Pago' THEN valor ELSE 0 END) AS total_pago,
                    MIN(data_vencimento) AS primeiro_vencimento,
                    MAX(data_vencimento) AS ultimo_vencimento
                 FROM tb_financeiro
                 WHERE contrato_paciente_id = :contrato_id
                   AND tipo_transacao = 'Entrada'",
                [':contrato_id' => $contratoId]
            );

            return $row ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function normalizarContrato(int $pacienteId, array $data, ?array $paciente): array
    {
        $servicos = $data['servicos_contratados'] ?? [];
        if (!is_array($servicos)) {
            $servicos = [];
        }
        $servicos = array_values(array_filter(array_map('trim', array_map('strval', $servicos))));

        $tipoServico = trim((string)($data['tipo_servico'] ?? ''));
        if ($tipoServico === '') {
            $tipoServico = $servicos !== [] ? implode(', ', $servicos) : 'Contrato home care';
        }

        $tipoPrazo = trim((string)($data['tipo_prazo'] ?? 'Indeterminado'));
        $vigenciaFim = $tipoPrazo === 'Indeterminado' ? null : $this->nullableDate($data['vigencia_fim'] ?? null);

        $empresa = [
            'razao_social' => $this->nullableString($data['empresa_razao_social'] ?? null),
            'cnpj' => $this->nullableString($data['empresa_cnpj'] ?? null),
            'endereco' => $this->nullableString($data['empresa_endereco'] ?? null),
            'responsavel_contrato' => $this->nullableString($data['empresa_responsavel_contrato'] ?? null),
        ];

        $responsavelLegalId = $this->nullableInt($data['responsavel_legal_id'] ?? null)
            ?? $this->nullableInt($paciente['responsavel_id'] ?? null);
        $responsavelFinanceiroId = $this->nullableInt($data['responsavel_financeiro_id'] ?? null)
            ?? $responsavelLegalId;

        $tipoCobranca = trim((string)($data['tipo_cobranca'] ?? 'Mensal'));
        if (!in_array($tipoCobranca, ['Mensal', 'Semanal', 'Por plantão'], true)) {
            $tipoCobranca = 'Mensal';
        }

        return [
            'paciente_id' => $pacienteId,
            'responsavel_legal_id' => $responsavelLegalId,
            'responsavel_financeiro_id' => $responsavelFinanceiroId,
            'tipo_servico' => $tipoServico,
            'servicos_contratados' => $this->jsonOrNull($servicos),
            'escala_contratada' => $this->nullableString($data['escala_contratada'] ?? null),
            'tipo_plantao' => $this->inferirPlantao($data['tipo_plantao'] ?? null, $data['escala_contratada'] ?? null),
            'hora_inicio_padrao' => $this->nullableTime($data['hora_inicio_padrao'] ?? null),
            'hora_fim_padrao' => $this->nullableTime($data['hora_fim_padrao'] ?? null),
            'tipo_prazo' => in_array($tipoPrazo, ['Determinado', 'Indeterminado'], true) ? $tipoPrazo : 'Indeterminado',
            'tipo_cobranca' => $tipoCobranca,
            // Campo removido do formulário. Mantido nulo para compatibilidade com a estrutura antiga.
            'valor_contrato' => null,
            'valor_mensal' => $this->moneyOrZero($data['valor_mensal'] ?? null),
            'valor_semanal' => $this->nullableMoney($data['valor_semanal'] ?? null),
            'valor_plantao' => $this->nullableMoney($data['valor_plantao'] ?? null),
            'dia_vencimento' => $this->diaVencimento($data['dia_vencimento'] ?? 10),
            'forma_pagamento' => $this->nullableString($data['forma_pagamento'] ?? null),
            'multa_percentual' => $this->percentual($data['multa_percentual'] ?? null),
            'juros_percentual' => $this->percentual($data['juros_percentual'] ?? null),
            'vigencia_inicio' => $this->nullableDate($data['vigencia_inicio'] ?? null) ?: date('Y-m-d'),
            'vigencia_fim' => $vigenciaFim,
            'empresa_razao_social' => $empresa['razao_social'],
            'empresa_cnpj' => $empresa['cnpj'],
            'empresa_endereco' => $empresa['endereco'],
            'empresa_responsavel_contrato' => $empresa['responsavel_contrato'],
            'paciente_snapshot' => $this->snapshotPaciente($paciente),
            'responsavel_legal_snapshot' => $this->snapshotResponsavel($responsavelLegalId),
            'responsavel_financeiro_snapshot' => $this->snapshotResponsavel($responsavelFinanceiroId),
            'empresa_snapshot' => $this->jsonOrNull($empresa),
            'status' => in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Suspenso', 'Encerrado', 'Cancelado'], true)
                ? (string)$data['status']
                : 'Ativo',
            'observacoes' => $this->nullableString($data['observacoes'] ?? null),
        ];
    }

    private function encerrarOutrosAtivos(int $pacienteId, ?int $contratoId): void
    {
        $sql = "UPDATE {$this->table} SET status = 'Encerrado' WHERE paciente_id = :paciente_id AND status = 'Ativo'";
        $params = [':paciente_id' => $pacienteId];

        if ($contratoId !== null && $contratoId > 0) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $contratoId;
        }

        $this->query($sql, $params);
    }

    private function baseSelect(): string
    {
        return "SELECT c.*,
                       p.nome_completo AS paciente_nome,
                       p.uuid AS paciente_uuid,
                       p.cpf AS paciente_cpf,
                       p.rg AS paciente_rg,
                       p.data_nascimento AS paciente_data_nascimento,
                       p.endereco_completo AS paciente_endereco,
                       p.telefone_principal AS paciente_telefone,
                       p.email AS paciente_email,
                       rl.nome_completo AS responsavel_legal_nome,
                       rf.nome_completo AS responsavel_financeiro_nome
                FROM tb_contratos_paciente c
                JOIN tb_pacientes p ON p.id = c.paciente_id
                LEFT JOIN tb_responsavel rl ON rl.id = c.responsavel_legal_id
                LEFT JOIN tb_responsavel rf ON rf.id = c.responsavel_financeiro_id";
    }

    private function formatContrato(array $row): array
    {
        $row['servicos_lista'] = $this->jsonDecodeList($row['servicos_contratados'] ?? null);
        $row['valor_mensal_fmt'] = function_exists('formatMoney')
            ? formatMoney((float)($row['valor_mensal'] ?? 0))
            : 'R$ ' . number_format((float)($row['valor_mensal'] ?? 0), 2, ',', '.');
        $row['valor_cobranca'] = $this->valorBaseCobranca($row);
        $row['valor_cobranca_fmt'] = function_exists('formatMoney')
            ? formatMoney((float)$row['valor_cobranca'])
            : 'R$ ' . number_format((float)$row['valor_cobranca'], 2, ',', '.');
        return $row;
    }

    private function valorBaseCobranca(array $row): float
    {
        $tipo = (string)($row['tipo_cobranca'] ?? 'Mensal');
        $mensal = (float)($row['valor_mensal'] ?? 0);
        $semanal = (float)($row['valor_semanal'] ?? 0);
        $plantao = (float)($row['valor_plantao'] ?? 0);

        if ($tipo === 'Semanal') {
            return $semanal > 0 ? $semanal : $mensal;
        }
        if ($tipo === 'Por plantão') {
            return $plantao > 0 ? $plantao : $mensal;
        }

        if ($mensal > 0) return $mensal;
        if ($semanal > 0) return $semanal;
        return $plantao;
    }

    private function snapshotPaciente(?array $paciente): ?string
    {
        if (!$paciente) {
            return null;
        }

        return $this->jsonOrNull([
            'nome_completo' => $paciente['nome_completo'] ?? null,
            'cpf' => $paciente['cpf'] ?? null,
            'rg' => $paciente['rg'] ?? null,
            'data_nascimento' => $paciente['data_nascimento'] ?? null,
            'endereco' => $paciente['endereco_completo'] ?? null,
            'telefone' => $paciente['telefone_principal'] ?? null,
            'email' => $paciente['email'] ?? null,
        ]);
    }

    private function snapshotResponsavel(?int $responsavelId): ?string
    {
        if (!$responsavelId) {
            return null;
        }

        $responsavel = $this->rawFirst(
            "SELECT id, nome_completo, cpf, grau_parentesco, telefone, email,
                    CONCAT_WS(', ', NULLIF(CONCAT_WS(' ', endereco, numero), ''), bairro, NULLIF(CONCAT_WS('/', cidade, estado), '/')) AS endereco
             FROM tb_responsavel
             WHERE id = :id LIMIT 1",
            [':id' => $responsavelId]
        );

        return $responsavel ? $this->jsonOrNull($responsavel) : null;
    }

    private function inferirPlantao(mixed $valor, mixed $escala): ?string
    {
        $texto = mb_strtolower(trim((string)$valor . ' ' . (string)$escala), 'UTF-8');
        foreach (['24h', '12h', '8h', '6h'] as $tipo) {
            if (str_contains($texto, $tipo)) {
                return $tipo;
            }
        }
        return null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int)$value;
    }

    private function nullableDate(mixed $value): ?string
    {
        $value = trim((string)$value);
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function nullableTime(mixed $value): ?string
    {
        $value = trim((string)$value);
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            return $value . ':00';
        }
        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
            return $value;
        }
        return null;
    }

    private function moneyOrZero(mixed $value): string
    {
        return $this->nullableMoney($value) ?? '0.00';
    }

    private function nullableMoney(mixed $value): ?string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }
        $value = preg_replace('/[^0-9,.-]/', '', $value) ?? '';
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        return number_format((float)$value, 2, '.', '');
    }

    private function percentual(mixed $value): ?string
    {
        $money = $this->nullableMoney($value);
        return $money === null ? null : number_format((float)$money, 4, '.', '');
    }

    private function diaVencimento(mixed $value): int
    {
        return min(31, max(1, (int)$value));
    }

    private function jsonOrNull(mixed $value): ?string
    {
        if ($value === null || $value === [] || $value === '') {
            return null;
        }
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    private function jsonDecodeList(mixed $value): array
    {
        if (!$value) {
            return [];
        }
        $decoded = json_decode((string)$value, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }

    private function filterExistingColumns(array $data): array
    {
        return array_intersect_key($data, array_flip($this->tableColumns()));
    }

    private function tableColumns(): array
    {
        static $columns = null;
        if ($columns !== null) {
            return $columns;
        }
        $rows = $this->query("SHOW COLUMNS FROM {$this->table}")->fetchAll(\PDO::FETCH_ASSOC);
        $columns = array_map(static fn(array $row): string => (string)$row['Field'], $rows);
        return $columns;
    }
}
