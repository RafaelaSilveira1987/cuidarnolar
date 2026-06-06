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
        'contrato_paciente_id',
        'escala_ocorrencia_id',
        'plano_id',
        'data',
        'tipo_transacao',
        'categoria_id',
        'moeda',
        'valor',
        'status',
        'data_vencimento',
        'mes_referencia',
        'data_pagamento',
        'descricao',
        'detalhes',
        'origem',
        'observacoes',
    ];
    protected array $nullable = [
        'responsavel_id',
        'cuidador_id',
        'paciente_id',
        'contrato_paciente_id',
        'escala_ocorrencia_id',
        'plano_id',
        'categoria_id',
        'moeda',
        'valor',
        'data_vencimento',
        'mes_referencia',
        'data_pagamento',
        'descricao',
        'detalhes',
        'origem',
        'observacoes',
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

    public function findForShowByUuid(string $uuid): array|false
    {
        $uuid = trim($uuid);
        if ($uuid === '') {
            return false;
        }

        $record = $this->rawFirst($this->baseSelect() . ' WHERE f.uuid = :uuid LIMIT 1', [':uuid' => $uuid]);
        return $record ? $this->formatRecord($record) : false;
    }


    public function formasPagamentoBaixa(): array
    {
        return [
            'Pix' => 'PIX',
            'Boleto' => 'Boleto',
            'Cartão' => 'Cartão',
            'Depósito' => 'Transferência/Depósito',
            'Dinheiro' => 'Dinheiro',
        ];
    }

    public function registrarRecebimento(int $id, array $dados): array
    {
        $record = $this->findForShow($id);
        if (!$record) {
            return ['ok' => false, 'errors' => ['geral' => 'Conta a receber não encontrada.']];
        }

        if (($record['tipo_transacao'] ?? '') !== 'Entrada') {
            return ['ok' => false, 'errors' => ['geral' => 'Este lançamento não é uma conta a receber.']];
        }

        if (($record['status'] ?? '') === 'Pago') {
            return ['ok' => false, 'errors' => ['geral' => 'Esta conta já está marcada como paga.']];
        }

        $errors = [];
        $dataPagamento = trim((string)($dados['data_pagamento'] ?? ''));
        $forma = trim((string)($dados['moeda'] ?? ''));
        $valorRecebido = $this->normalizarValorMonetario($dados['valor_recebido'] ?? ($record['valor'] ?? 0));
        $observacaoBaixa = trim((string)($dados['observacao_baixa'] ?? ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataPagamento)) {
            $errors['data_pagamento'] = 'Informe uma data válida.';
        }

        if (!array_key_exists($forma, $this->formasPagamentoBaixa())) {
            $errors['moeda'] = 'Selecione a forma de pagamento.';
        }

        if ($valorRecebido <= 0) {
            $errors['valor_recebido'] = 'Informe um valor recebido maior que zero.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $valorFormatado = function_exists('formatMoney')
            ? formatMoney($valorRecebido)
            : 'R$ ' . number_format($valorRecebido, 2, ',', '.');

        $linhaBaixa = 'Baixa registrada em ' . date('d/m/Y H:i')
            . ' | Recebido em ' . date('d/m/Y', strtotime($dataPagamento))
            . ' | Valor: ' . $valorFormatado
            . ' | Forma: ' . $forma;

        if ($observacaoBaixa !== '') {
            $linhaBaixa .= ' | Obs.: ' . $observacaoBaixa;
        }

        $observacoesAtuais = trim((string)($record['observacoes'] ?? ''));
        $observacoes = $observacoesAtuais !== ''
            ? $observacoesAtuais . PHP_EOL . $linhaBaixa
            : $linhaBaixa;

        // ATENÇÃO:
        // Não use updateRecord() aqui. O BaseModuleModel preenche todos os campos
        // do fillable e, em update parcial, transforma campos não enviados em NULL.
        // Isso quebrava tipo_transacao, paciente_id, contrato_paciente_id etc.
        // Na baixa devemos alterar somente os campos da baixa.
        $sql = "UPDATE {$this->table}
                   SET status = :status,
                       data_pagamento = :data_pagamento,
                       data = :data,
                       moeda = :moeda,
                       valor = :valor,
                       observacoes = :observacoes
                 WHERE id = :id";

        $this->query($sql, [
            ':status' => 'Pago',
            ':data_pagamento' => $dataPagamento,
            ':data' => $dataPagamento . ' ' . date('H:i:s'),
            ':moeda' => $forma,
            ':valor' => $valorRecebido,
            ':observacoes' => $observacoes,
            ':id' => $id,
        ]);

        return ['ok' => true, 'errors' => []];
    }

    private function normalizarValorMonetario(mixed $valor): float
    {
        if (is_int($valor) || is_float($valor)) {
            return (float)$valor;
        }

        $valor = trim((string)$valor);
        $valor = str_replace(['R$', ' '], '', $valor);

        if (str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        $valor = preg_replace('/[^0-9.\-]/', '', $valor) ?? '0';

        return (float)$valor;
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




    /**
     * Gera contas a receber de um contrato específico dentro do cadastro do paciente.
     * Assim o fluxo fica: Paciente > Contratos > Gerar financeiro.
     */
    public function gerarReceitasContratoPaciente(int $contratoId, string $dataInicio, string $dataFim): array
    {
        if ($contratoId <= 0) {
            return [
                'criadas' => 0,
                'ignoradas' => 0,
                'contratos' => 0,
                'mensagem' => 'Contrato inválido.',
            ];
        }

        if (!$this->dataValida($dataInicio) || !$this->dataValida($dataFim)) {
            return [
                'criadas' => 0,
                'ignoradas' => 0,
                'contratos' => 0,
                'mensagem' => 'Período inválido.',
            ];
        }

        if (strtotime($dataInicio) > strtotime($dataFim)) {
            [$dataInicio, $dataFim] = [$dataFim, $dataInicio];
        }

        $contratos = $this->contratosAtivosNoPeriodo($dataInicio, $dataFim, $contratoId);
        if ($contratos === []) {
            return [
                'criadas' => 0,
                'ignoradas' => 0,
                'contratos' => 0,
                'mensagem' => 'Nenhum contrato ativo com valor mensal/contrato maior que zero foi encontrado para o período informado.',
            ];
        }

        return $this->gerarReceitasParaContratos($contratos, $dataInicio, $dataFim);
    }

    /**
     * Gera contas a receber mensais a partir dos contratos ativos.
     * Regra: um lançamento por contrato/mês, respeitando vigência e dia de vencimento.
     */
    public function gerarReceitasContratos(string $dataInicio, string $dataFim): array
    {
        if (!$this->dataValida($dataInicio) || !$this->dataValida($dataFim)) {
            return [
                'criadas' => 0,
                'ignoradas' => 0,
                'contratos' => 0,
                'mensagem' => 'Período inválido.',
            ];
        }

        if (strtotime($dataInicio) > strtotime($dataFim)) {
            [$dataInicio, $dataFim] = [$dataFim, $dataInicio];
        }

        $contratos = $this->contratosAtivosNoPeriodo($dataInicio, $dataFim);

        return $this->gerarReceitasParaContratos($contratos, $dataInicio, $dataFim);
    }




    private function contratosAtivosNoPeriodo(string $dataInicio, string $dataFim, ?int $contratoId = null): array
    {
        $whereContrato = '';
        $params = [':inicio' => $dataInicio, ':fim' => $dataFim];

        if ($contratoId !== null && $contratoId > 0) {
            $whereContrato = ' AND c.id = :contrato_id';
            $params[':contrato_id'] = $contratoId;
        }

        return $this->rawAll(
            "SELECT c.*, p.nome_completo AS paciente_nome,
                    COALESCE(c.responsavel_financeiro_id, c.responsavel_legal_id, p.responsavel_id) AS responsavel_id,
                    CASE
                        WHEN c.tipo_cobranca = 'Mensal' AND COALESCE(c.valor_mensal, 0) > 0 THEN c.valor_mensal
                        WHEN c.tipo_cobranca = 'Semanal' AND COALESCE(c.valor_semanal, 0) > 0 THEN c.valor_semanal
                        WHEN c.tipo_cobranca = 'Por plantão' AND COALESCE(c.valor_plantao, 0) > 0 THEN c.valor_plantao
                        WHEN COALESCE(c.valor_mensal, 0) > 0 THEN c.valor_mensal
                        WHEN COALESCE(c.valor_semanal, 0) > 0 THEN c.valor_semanal
                        WHEN COALESCE(c.valor_plantao, 0) > 0 THEN c.valor_plantao
                        ELSE 0
                    END AS valor_financeiro
             FROM tb_contratos_paciente c
             JOIN tb_pacientes p ON p.id = c.paciente_id
             WHERE c.status = 'Ativo'
               AND c.vigencia_inicio <= :fim
               AND (c.vigencia_fim IS NULL OR c.vigencia_fim >= :inicio)" . $whereContrato . "
             HAVING valor_financeiro > 0
             ORDER BY p.nome_completo ASC, c.vigencia_inicio ASC",
            $params
        );
    }

    private function gerarReceitasParaContratos(array $contratos, string $dataInicio, string $dataFim): array
    {
        $categoriaId = $this->categoriaMensalidadeId();
        $criadas = 0;
        $ignoradas = 0;

        foreach ($contratos as $contrato) {
            $mesAtual = new \DateTimeImmutable(date('Y-m-01', strtotime((string)$dataInicio)));
            $mesLimite = new \DateTimeImmutable(date('Y-m-01', strtotime((string)$dataFim)));

            while ($mesAtual <= $mesLimite) {
                $mesRef = $mesAtual->format('Y-m');
                $vencimento = $this->dataVencimentoDoMes($mesRef, (int)($contrato['dia_vencimento'] ?? 10));

                if (!$this->vencimentoDentroDaRegra($vencimento, $dataInicio, $dataFim, $contrato)) {
                    $mesAtual = $mesAtual->modify('+1 month');
                    continue;
                }

                $contratoId = (int)($contrato['id'] ?? 0);
                if ($contratoId <= 0 || $this->existeReceitaContratoMes($contratoId, $mesRef)) {
                    $ignoradas++;
                    $mesAtual = $mesAtual->modify('+1 month');
                    continue;
                }

                $valorGerado = $this->valorContratoParaMes($contrato, $mesRef);
                if ($valorGerado <= 0) {
                    $ignoradas++;
                    $mesAtual = $mesAtual->modify('+1 month');
                    continue;
                }

                $pacienteNome = (string)($contrato['paciente_nome'] ?? 'Paciente');
                $tipoCobranca = (string)($contrato['tipo_cobranca'] ?? 'Mensal');
                $descricaoBase = match ($tipoCobranca) {
                    'Por plantão' => 'Cobrança por plantão',
                    'Semanal' => 'Cobrança semanal',
                    default => 'Mensalidade',
                };
                $descricao = $descricaoBase . ' ' . $this->mesReferenciaPtBr($mesRef) . ' - ' . $pacienteNome;
                $detalhes = 'Gerado automaticamente a partir do contrato do paciente #' . $contratoId . '.';

                if ($tipoCobranca === 'Por plantão') {
                    $qtdPlantoes = $this->quantidadePlantoesContratoMes($contrato, $mesRef);
                    $detalhes .= ' Regra: ' . $this->descricaoRegraPlantaoContrato($contrato)
                        . ' | Plantões estimados: ' . $qtdPlantoes
                        . ' | Valor por plantão: R$ ' . number_format((float)($contrato['valor_plantao'] ?? 0), 2, ',', '.');
                }

                if ($tipoCobranca === 'Semanal') {
                    $semanas = $this->quantidadeSemanasContratoMes($contrato, $mesRef);
                    $detalhes .= ' Semanas cobradas: ' . $semanas
                        . ' | Valor semanal: R$ ' . number_format((float)($contrato['valor_semanal'] ?? 0), 2, ',', '.');
                }

                $this->insert([
                    'responsavel_id' => $contrato['responsavel_id'] !== null ? (int)$contrato['responsavel_id'] : null,
                    'cuidador_id' => null,
                    'paciente_id' => (int)$contrato['paciente_id'],
                    'contrato_paciente_id' => $contratoId,
                    'plano_id' => (string)$contratoId,
                    'data' => $vencimento . ' 00:00:00',
                    'data_vencimento' => $vencimento,
                    'mes_referencia' => $mesRef,
                    'tipo_transacao' => 'Entrada',
                    'categoria_id' => $categoriaId,
                    'moeda' => $this->normalizarFormaPagamento($contrato['forma_pagamento'] ?? null),
                    'valor' => $valorGerado,
                    'descricao' => $descricao,
                    'detalhes' => $detalhes,
                    'origem' => 'contrato',
                    'status' => 'Pendente',
                    'observacoes' => $descricao,
                ]);

                $criadas++;
                $mesAtual = $mesAtual->modify('+1 month');
            }
        }

        return [
            'criadas' => $criadas,
            'ignoradas' => $ignoradas,
            'contratos' => count($contratos),
            'mensagem' => "{$criadas} conta(s) criada(s), {$ignoradas} já existente(s)/ignorada(s), " . count($contratos) . ' contrato(s) avaliado(s).',
        ];
    }

    private function valorContratoParaMes(array $contrato, string $mesRef): float
    {
        $tipo = (string)($contrato['tipo_cobranca'] ?? 'Mensal');

        if ($tipo === 'Por plantão') {
            $valorPlantao = (float)($contrato['valor_plantao'] ?? 0);
            return round($valorPlantao * $this->quantidadePlantoesContratoMes($contrato, $mesRef), 2);
        }

        if ($tipo === 'Semanal') {
            $valorSemanal = (float)($contrato['valor_semanal'] ?? 0);
            return round($valorSemanal * $this->quantidadeSemanasContratoMes($contrato, $mesRef), 2);
        }

        return round((float)($contrato['valor_mensal'] ?? 0), 2);
    }

    private function quantidadeSemanasContratoMes(array $contrato, string $mesRef): int
    {
        [$inicio, $fim] = $this->intervaloAtivoContratoNoMes($contrato, $mesRef);
        if (!$inicio || !$fim || $fim < $inicio) {
            return 0;
        }

        $dias = $inicio->diff($fim)->days + 1;
        return max(1, (int)ceil($dias / 7));
    }

    private function quantidadePlantoesContratoMes(array $contrato, string $mesRef): int
    {
        [$inicio, $fim] = $this->intervaloAtivoContratoNoMes($contrato, $mesRef);
        if (!$inicio || !$fim || $fim < $inicio) {
            return 0;
        }

        $escala = strtolower((string)($contrato['escala_contratada'] ?? ''));
        $vigenciaInicio = !empty($contrato['vigencia_inicio'])
            ? new \DateTimeImmutable((string)$contrato['vigencia_inicio'])
            : $inicio;

        if (str_contains($escala, 'segunda') || str_contains($escala, 'sexta')) {
            return $this->contarDiasUteis($inicio, $fim);
        }

        if (str_contains($escala, '24')) {
            return $inicio->diff($fim)->days + 1;
        }

        if (str_contains($escala, '12x36') || str_contains($escala, '12x')) {
            $total = 0;
            for ($dia = $inicio; $dia <= $fim; $dia = $dia->modify('+1 day')) {
                $diff = $vigenciaInicio->diff($dia)->days;
                if ($dia >= $vigenciaInicio && $diff % 2 === 0) {
                    $total++;
                }
            }
            return $total;
        }

        return $inicio->diff($fim)->days + 1;
    }

    /** @return array{0:?\DateTimeImmutable,1:?\DateTimeImmutable} */
    private function intervaloAtivoContratoNoMes(array $contrato, string $mesRef): array
    {
        $mesInicio = new \DateTimeImmutable($mesRef . '-01');
        $mesFim = $mesInicio->modify('last day of this month');

        $inicio = !empty($contrato['vigencia_inicio'])
            ? new \DateTimeImmutable((string)$contrato['vigencia_inicio'])
            : $mesInicio;
        $fim = !empty($contrato['vigencia_fim'])
            ? new \DateTimeImmutable((string)$contrato['vigencia_fim'])
            : $mesFim;

        if ($inicio < $mesInicio) {
            $inicio = $mesInicio;
        }
        if ($fim > $mesFim) {
            $fim = $mesFim;
        }

        if ($fim < $inicio) {
            return [null, null];
        }

        return [$inicio, $fim];
    }

    private function contarDiasUteis(\DateTimeImmutable $inicio, \DateTimeImmutable $fim): int
    {
        $total = 0;
        for ($dia = $inicio; $dia <= $fim; $dia = $dia->modify('+1 day')) {
            $semana = (int)$dia->format('N');
            if ($semana >= 1 && $semana <= 5) {
                $total++;
            }
        }
        return $total;
    }

    private function descricaoRegraPlantaoContrato(array $contrato): string
    {
        $escala = trim((string)($contrato['escala_contratada'] ?? ''));
        return $escala !== '' ? $escala : 'Personalizada';
    }

    private function existeReceitaContratoMes(int $contratoId, string $mesRef): bool
    {
        $total = (int)$this->query(
            "SELECT COUNT(*)
             FROM {$this->table}
             WHERE contrato_paciente_id = :contrato_id
               AND mes_referencia = :mes_ref
               AND tipo_transacao = 'Entrada'",
            [':contrato_id' => $contratoId, ':mes_ref' => $mesRef]
        )->fetchColumn();

        return $total > 0;
    }

    private function categoriaMensalidadeId(): ?int
    {
        try {
            $id = $this->query(
                "SELECT id FROM tb_categorias_financeiro
                 WHERE nome = 'Mensalidade' AND tipo = 'entrada' AND ativo = 1
                 ORDER BY id ASC LIMIT 1"
            )->fetchColumn();

            return $id ? (int)$id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function dataVencimentoDoMes(string $mesRef, int $dia): string
    {
        $base = new \DateTimeImmutable($mesRef . '-01');
        $ultimoDia = (int)$base->format('t');
        $dia = min(max(1, $dia), $ultimoDia);

        return $base->setDate((int)$base->format('Y'), (int)$base->format('m'), $dia)->format('Y-m-d');
    }

    private function vencimentoDentroDaRegra(string $vencimento, string $dataInicio, string $dataFim, array $contrato): bool
    {
        if ($vencimento < $dataInicio || $vencimento > $dataFim) {
            return false;
        }

        if ($vencimento < (string)$contrato['vigencia_inicio']) {
            return false;
        }

        if (!empty($contrato['vigencia_fim']) && $vencimento > (string)$contrato['vigencia_fim']) {
            return false;
        }

        return true;
    }

    private function normalizarFormaPagamento(mixed $forma): ?string
    {
        $forma = trim((string)$forma);
        $map = [
            'PIX' => 'Pix',
            'Pix' => 'Pix',
            'pix' => 'Pix',
            'Boleto' => 'Boleto',
            'boleto' => 'Boleto',
            'Dinheiro' => 'Dinheiro',
            'dinheiro' => 'Dinheiro',
            'Depósito' => 'Depósito',
            'Deposito' => 'Depósito',
            'Transferência' => 'Depósito',
            'Transferencia' => 'Depósito',
            'Cartão' => 'Cartão',
            'Cartao' => 'Cartão',
            'cartao' => 'Cartão',
            'cartão' => 'Cartão',
        ];

        return $map[$forma] ?? null;
    }

    private function mesReferenciaPtBr(string $mesRef): string
    {
        [$ano, $mes] = explode('-', $mesRef);
        $nomes = [
            '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
            '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
            '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro',
        ];

        return ($nomes[$mes] ?? $mes) . '/' . $ano;
    }

    private function dataValida(string $data): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) === 1;
    }



    /**
     * Prévia de contas a pagar dos cuidadores a partir dos plantões finalizados.
     * A tabela de plantões em Configurações fornece o valor sugerido, mas a tela permite ajuste manual.
     */
    public function previewContasPagarPlantao(string $dataInicio, string $dataFim): array
    {
        if (!$this->dataValida($dataInicio) || !$this->dataValida($dataFim)) {
            return [];
        }

        if (strtotime($dataInicio) > strtotime($dataFim)) {
            [$dataInicio, $dataFim] = [$dataFim, $dataInicio];
        }

        $rows = $this->rawAll(
            "SELECT
                o.id AS ocorrencia_id,
                o.escala_base_id,
                o.paciente_id,
                o.cuidador_id,
                o.data_plantao,
                o.inicio,
                o.fim,
                TIME(o.inicio) AS hora_inicio,
                TIME(o.fim) AS hora_fim,
                o.tipo_plantao,
                o.status,
                p.nome_completo AS paciente_nome,
                c.nome_completo AS cuidador_nome
             FROM tb_escala_ocorrencias o
             JOIN tb_pacientes p ON p.id = o.paciente_id
             JOIN tb_cuidador c ON c.id = o.cuidador_id
             LEFT JOIN {$this->table} f
                    ON f.escala_ocorrencia_id = o.id
                   AND f.tipo_transacao <> 'Entrada'
                   AND f.status <> 'Cancelado'
             WHERE o.cuidador_id IS NOT NULL
               AND o.status = 'finalizado'
               AND o.data_plantao BETWEEN :inicio AND :fim
               AND f.id IS NULL
             ORDER BY c.nome_completo ASC, o.inicio ASC",
            [':inicio' => $dataInicio, ':fim' => $dataFim]
        );

        $regras = $this->tabelaPlantoesAtiva();

        return array_map(function (array $row) use ($regras): array {
            $periodo = $this->periodoPlantao($row);
            $regra = $this->regraPlantaoParaOcorrencia($row, $periodo, $regras);
            $valor = (float)($regra['valor_cuidador'] ?? 0) + (float)($regra['valor_extra'] ?? 0);

            $row['periodo_calculado'] = $periodo;
            $row['regra_plantao_id'] = $regra['id'] ?? null;
            $row['regra_titulo'] = $regra['titulo'] ?? 'Sem regra na tabela';
            $row['valor_sugerido'] = $valor;
            $row['valor_sugerido_formatado'] = function_exists('formatMoney')
                ? formatMoney($valor)
                : 'R$ ' . number_format($valor, 2, ',', '.');
            $row['data_exibicao'] = date('d/m/Y', strtotime((string)$row['data_plantao']));
            $row['horario_exibicao'] = substr((string)$row['inicio'], 11, 5) . ' às ' . substr((string)$row['fim'], 11, 5);

            return $row;
        }, $rows);
    }

    /** Gera saídas pendentes para os plantões selecionados na prévia. */
    public function gerarContasPagarPlantao(
        string $dataInicio,
        string $dataFim,
        array $ocorrenciaIds,
        array $valores,
        string $dataVencimento,
        string $observacao = ''
    ): array {
        if (!$this->dataValida($dataInicio) || !$this->dataValida($dataFim) || !$this->dataValida($dataVencimento)) {
            return ['ok' => false, 'criadas' => 0, 'ignoradas' => 0, 'errors' => ['Período ou vencimento inválido.']];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ocorrenciaIds))));
        if ($ids === []) {
            return ['ok' => false, 'criadas' => 0, 'ignoradas' => 0, 'errors' => ['Selecione pelo menos um plantão para gerar o contas a pagar.']];
        }

        $selecionados = array_fill_keys($ids, true);
        $preview = array_values(array_filter(
            $this->previewContasPagarPlantao($dataInicio, $dataFim),
            static fn(array $row): bool => isset($selecionados[(int)$row['ocorrencia_id']])
        ));

        if ($preview === []) {
            return ['ok' => false, 'criadas' => 0, 'ignoradas' => 0, 'errors' => ['Nenhum plantão selecionado está disponível para geração. Pode já ter sido gerado antes.']];
        }

        $categoriaId = $this->categoriaSalarioCuidadorId();
        $criadas = 0;
        $ignoradas = 0;
        $errors = [];
        $observacao = trim($observacao);

        foreach ($preview as $row) {
            $ocorrenciaId = (int)$row['ocorrencia_id'];
            if ($this->existePagamentoPlantao($ocorrenciaId)) {
                $ignoradas++;
                continue;
            }

            $valor = $this->normalizarValorMonetario($valores[$ocorrenciaId] ?? $row['valor_sugerido'] ?? 0);
            if ($valor <= 0) {
                $errors[] = 'Valor inválido para o plantão #' . $ocorrenciaId . ' (' . ($row['cuidador_nome'] ?? 'cuidador') . ').';
                $ignoradas++;
                continue;
            }

            $descricao = 'Pagamento plantão ' . ($row['data_exibicao'] ?? date('d/m/Y', strtotime((string)$row['data_plantao'])))
                . ' - ' . ($row['cuidador_nome'] ?? 'Cuidador');

            $detalhes = 'Gerado pelo fechamento de cuidadores. Plantão #' . $ocorrenciaId
                . ' | Paciente: ' . ($row['paciente_nome'] ?? '-')
                . ' | Horário: ' . ($row['horario_exibicao'] ?? '-')
                . ' | Tipo: ' . ($row['tipo_plantao'] ?? '-')
                . ' | Regra: ' . ($row['regra_titulo'] ?? '-');

            $obs = $detalhes;
            if ($observacao !== '') {
                $obs .= PHP_EOL . 'Obs. do fechamento: ' . $observacao;
            }

            $this->insert([
                'responsavel_id' => null,
                'cuidador_id' => (int)$row['cuidador_id'],
                'paciente_id' => (int)$row['paciente_id'],
                'contrato_paciente_id' => null,
                'escala_ocorrencia_id' => $ocorrenciaId,
                'plano_id' => null,
                'data' => $dataVencimento . ' 00:00:00',
                'data_vencimento' => $dataVencimento,
                'mes_referencia' => date('Y-m', strtotime((string)$row['data_plantao'])),
                'data_pagamento' => null,
                'tipo_transacao' => 'Saída',
                'categoria_id' => $categoriaId,
                'moeda' => null,
                'valor' => $valor,
                'descricao' => $descricao,
                'detalhes' => $detalhes,
                'origem' => 'plantao',
                'status' => 'Pendente',
                'observacoes' => $obs,
            ]);

            $criadas++;
        }

        return [
            'ok' => $criadas > 0 && $errors === [],
            'criadas' => $criadas,
            'ignoradas' => $ignoradas,
            'errors' => $errors,
            'mensagem' => "{$criadas} conta(s) a pagar criada(s), {$ignoradas} ignorada(s).",
        ];
    }

    private function tabelaPlantoesAtiva(): array
    {
        try {
            return $this->rawAll(
                "SELECT * FROM tb_tabela_plantoes
                 WHERE ativo = 1
                 ORDER BY ordem ASC, id ASC"
            );
        } catch (\Throwable) {
            return [];
        }
    }

    private function regraPlantaoParaOcorrencia(array $row, string $periodo, array $regras): ?array
    {
        $tipo = strtolower(trim((string)($row['tipo_plantao'] ?? '')));
        $periodoNormalizado = strtolower($periodo);

        foreach ($regras as $regra) {
            if (strtolower((string)$regra['tipo_plantao']) === $tipo && strtolower((string)$regra['periodo']) === $periodoNormalizado) {
                return $regra;
            }
        }

        foreach ($regras as $regra) {
            if (strtolower((string)$regra['tipo_plantao']) === $tipo) {
                return $regra;
            }
        }

        return null;
    }

    private function periodoPlantao(array $row): string
    {
        $tipo = strtolower(trim((string)($row['tipo_plantao'] ?? '')));
        if ($tipo === '24h') {
            return '24h';
        }

        $hora = substr((string)($row['hora_inicio'] ?? $row['inicio'] ?? ''), 0, 5);
        if ($hora >= '18:00' || $hora < '06:00') {
            return 'Noturno';
        }

        return 'Diurno';
    }

    private function existePagamentoPlantao(int $ocorrenciaId): bool
    {
        $total = (int)$this->query(
            "SELECT COUNT(*)
             FROM {$this->table}
             WHERE escala_ocorrencia_id = :ocorrencia_id
               AND tipo_transacao <> 'Entrada'
               AND status <> 'Cancelado'",
            [':ocorrencia_id' => $ocorrenciaId]
        )->fetchColumn();

        return $total > 0;
    }

    private function categoriaSalarioCuidadorId(): ?int
    {
        try {
            $id = $this->query(
                "SELECT id FROM tb_categorias_financeiro
                 WHERE nome = 'Salário cuidador' AND tipo = 'saida' AND ativo = 1
                 ORDER BY id ASC LIMIT 1"
            )->fetchColumn();

            return $id ? (int)$id : null;
        } catch (\Throwable) {
            return null;
        }
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

        $formatarData = static function (mixed $valor): string {
            $valor = trim((string) $valor);
            if ($valor === '') {
                return '';
            }

            $data = substr($valor, 0, 10);
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
                return $valor;
            }

            return date('d/m/Y', strtotime($data));
        };

        $row['data_exibicao'] = $formatarData($row['data'] ?? '');
        $row['vencimento_exibicao'] = $formatarData(
            $row['data_vencimento'] ?? (isset($row['data']) ? substr((string) $row['data'], 0, 10) : '')
        );
        $row['pagamento_exibicao'] = $formatarData($row['data_pagamento'] ?? '');

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
            'despesas' => $this->db->query("SELECT SUM(valor) FROM tb_financeiro WHERE tipo_transacao <> 'Entrada'")->fetchColumn() ?? 0,
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
            'pagar_pendentes' => $this->db->query("SELECT COUNT(*) FROM tb_financeiro WHERE status = 'Pendente' AND tipo_transacao <> 'Entrada'")->fetchColumn() ?? 0,
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