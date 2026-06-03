<?php

namespace App\Controllers;

use App\Models\ContratoPaciente;
use App\Models\Financeiro;

class FinanceiroController extends ResourceController
{
    protected string $modelClass = Financeiro::class;
    protected string $routeBase = '/financeiro';
    protected string $viewTitle = 'Financeiro';
    protected string $singularTitle = 'Lançamento financeiro';
    protected array $columns = [
        'id' => '#',
        'data' => 'Data',
        'tipo_transacao' => 'Tipo',
        'categoria_nome' => 'Categoria',
        'valor_formatado' => 'Valor',
        'paciente_nome' => 'Paciente',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'data' => 'Data',
        'tipo_transacao' => 'Tipo',
        'categoria_nome' => 'Categoria',
        'moeda' => 'Forma',
        'valor_formatado' => 'Valor',
        'status' => 'Status',
        'data_vencimento' => 'Vencimento',
        'data_pagamento' => 'Pagamento',
        'paciente_nome' => 'Paciente',
        'responsavel_nome' => 'Responsavel',
        'cuidador_nome' => 'Cuidador',
        'observacoes' => 'Observacoes',
    ];
    protected array $requiredFields = ['data', 'tipo_transacao', 'status', 'observacoes'];
    protected array $formFields = [
        'tipo_transacao' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['Entrada' => 'Entrada', 'Saída' => 'Saida']],
        'categoria_id' => ['label' => 'Categoria', 'type' => 'select', 'empty' => 'Sem categoria'],
        'data' => ['label' => 'Data do lançamento', 'type' => 'datetime-local'],
        'data_vencimento' => ['label' => 'Data de vencimento (contas a pagar/receber)', 'type' => 'date'],
        'data_pagamento' => ['label' => 'Data de pagamento (quando liquidado)', 'type' => 'date'],
        'valor' => ['label' => 'Valor', 'type' => 'number'],
        'moeda' => ['label' => 'Forma de pagamento', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Pix' => 'Pix', 'Depósito' => 'Deposito', 'Boleto' => 'Boleto', 'Dinheiro' => 'Dinheiro']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Pendente' => 'Pendente', 'Pago' => 'Pago', 'Cancelado' => 'Cancelado']],
        'paciente_id' => ['label' => 'Paciente (centro de custo — receitas)', 'type' => 'select', 'empty' => 'Sem paciente'],
        'responsavel_id' => ['label' => 'Responsavel', 'type' => 'select', 'empty' => 'Sem responsavel'],
        'cuidador_id' => ['label' => 'Cuidador (despesas ligadas a quem cuida)', 'type' => 'select', 'empty' => 'Sem cuidador'],
        'plano_id' => ['label' => 'Plano'],
        'observacoes' => ['label' => 'Observacoes', 'type' => 'textarea', 'span' => true],
    ];

    /** Camada 4 — hub com acesso às demais telas. */
    public function hub(): void
    {
        $model = new Financeiro();

        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        $mesRef = $meses[(int)date('n')] . ' ' . date('Y');

        $resumo = [
            'receitas' => 0,
            'despesas' => 0,
            'a_receber' => 0,
            'resultado' => 0,
        ];

        $counts = [
            'lancamentos' => 0,
            'contratos_ativos' => 0,
            'receber_vencidas' => 0,
            'pagar_pendentes' => 0,
        ];

        $alertas = [];

        // Se o model já tiver métodos reais, substitua aqui:
        if (method_exists($model, 'dashboardResumo')) {
            $resumo = $model->dashboardResumo();
        }

        if (method_exists($model, 'dashboardCounts')) {
            $counts = $model->dashboardCounts();
        }

        if (method_exists($model, 'dashboardAlertas')) {
            $alertas = $model->dashboardAlertas();
        }

        $resumo['resultado'] =
            ($resumo['receitas'] ?? 0)
            - ($resumo['despesas'] ?? 0);

        $this->view('financeiro/hub', [
            'pageTitle' => 'Financeiro — Homecare',
            'title' => 'Financeiro',
            'finSubnav' => 'hub',
            'resumo' => $resumo,
            'alertas' => $alertas,
            'counts' => $counts,
            'mes_ref' => $mesRef,
        ]);
    }

    /** Camada 2 — lançamentos (caixa) com filtros existentes. */
    public function lancamentos(): void
    {
        $page = (int) $this->input('page', 1);
        $search = trim((string) $this->input('busca', ''));
        $tipo = (string) $this->input('tipo', '');
        $tipo = in_array($tipo, ['entrada', 'saida'], true) ? $tipo : '';
        $result = (new Financeiro())->listByType($page, 15, $search, $tipo);

        $this->view('financeiro/lancamentos', [
            'pageTitle' => 'Lançamentos',
            'title' => 'Lançamentos (caixa)',
            'routeBase' => $this->routeBase,
            'columns' => $this->columns,
            'rows' => $result['data'],
            'pagination' => $result,
            'search' => $search,
            'tabs' => [
                '' => 'Todos',
                'entrada' => 'Entradas',
                'saida' => 'Saidas',
            ],
            'activeTab' => $tipo,
            'finSubnav' => 'lancamentos',
        ]);
    }

    public function contasReceber(): void
    {
        $page = (int) $this->input('page', 1);
        $result = (new Financeiro())->listContasReceber($page, 20);

        $this->view('financeiro/contas_receber', [
            'pageTitle' => 'Contas a receber',
            'title' => 'Contas a receber',
            'routeBase' => $this->routeBase,
            'columns' => $this->columns,
            'rows' => $result['data'],
            'pagination' => $result,
            'finSubnav' => 'receber',
        ]);
    }


    public function receber(string $id): void
    {
        $model = new Financeiro();
        $record = $model->findForShow((int)$id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Conta a receber não encontrada.'], 'layouts/blank');
            return;
        }

        if (($record['tipo_transacao'] ?? '') !== 'Entrada') {
            $this->flash('error', 'Este lançamento não é uma conta a receber.');
            $this->redirect('/financeiro/contas-receber');
            return;
        }

        $this->view('financeiro/receber', [
            'pageTitle' => 'Receber conta',
            'title' => 'Receber conta',
            'record' => $record,
            'errors' => [],
            'old' => [],
            'formasPagamento' => $model->formasPagamentoBaixa(),
            'finSubnav' => 'receber',
        ]);
    }

    public function registrarRecebimento(string $id): void
    {
        $model = new Financeiro();
        $record = $model->findForShow((int)$id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Conta a receber não encontrada.'], 'layouts/blank');
            return;
        }

        $old = [
            'data_pagamento' => trim((string)$this->input('data_pagamento', '')),
            'moeda' => trim((string)$this->input('moeda', '')),
            'valor_recebido' => trim((string)$this->input('valor_recebido', '')),
            'observacao_baixa' => trim((string)$this->input('observacao_baixa', '')),
        ];

        $resultado = $model->registrarRecebimento((int)$id, $old);

        if (!($resultado['ok'] ?? false)) {
            $this->view('financeiro/receber', [
                'pageTitle' => 'Receber conta',
                'title' => 'Receber conta',
                'record' => $record,
                'errors' => $resultado['errors'] ?? ['geral' => 'Não foi possível registrar o recebimento.'],
                'old' => $old,
                'formasPagamento' => $model->formasPagamentoBaixa(),
                'finSubnav' => 'receber',
            ]);
            return;
        }

        $this->flash('success', 'Recebimento registrado com sucesso.');
        $this->redirect('/financeiro/contas-receber');
    }

    public function contasPagar(): void
    {
        $page = (int) $this->input('page', 1);
        $result = (new Financeiro())->listContasPagar($page, 20);

        $this->view('financeiro/contas_pagar', [
            'pageTitle' => 'Contas a pagar',
            'title' => 'Contas a pagar',
            'routeBase' => $this->routeBase,
            'columns' => $this->columns,
            'rows' => $result['data'],
            'pagination' => $result,
            'finSubnav' => 'pagar',
        ]);
    }



    public function gerarContasPagar(): void
    {
        $dataInicio = trim((string)$this->input('data_inicio', date('Y-m-01')));
        $dataFim = trim((string)$this->input('data_fim', date('Y-m-t')));
        $dataVencimento = trim((string)$this->input('data_vencimento', date('Y-m-d')));

        $model = new Financeiro();
        $rows = $model->previewContasPagarPlantao($dataInicio, $dataFim);

        $this->view('financeiro/gerar_pagar', [
            'pageTitle' => 'Gerar contas a pagar',
            'title' => 'Gerar contas a pagar dos cuidadores',
            'rows' => $rows,
            'dataInicio' => $dataInicio,
            'dataFim' => $dataFim,
            'dataVencimento' => $dataVencimento,
            'finSubnav' => 'pagar',
        ]);
    }

    public function storeContasPagar(): void
    {
        $dataInicio = trim((string)$this->input('data_inicio', date('Y-m-01')));
        $dataFim = trim((string)$this->input('data_fim', date('Y-m-t')));
        $dataVencimento = trim((string)$this->input('data_vencimento', date('Y-m-d')));
        $observacao = trim((string)$this->input('observacao_fechamento', ''));

        $ocorrencias = $_POST['ocorrencias'] ?? [];
        $valores = $_POST['valores'] ?? [];

        if (!is_array($ocorrencias)) {
            $ocorrencias = [];
        }
        if (!is_array($valores)) {
            $valores = [];
        }

        $resultado = (new Financeiro())->gerarContasPagarPlantao(
            $dataInicio,
            $dataFim,
            $ocorrencias,
            $valores,
            $dataVencimento,
            $observacao
        );

        if (($resultado['errors'] ?? []) !== []) {
            $this->flash('error', implode(' ', $resultado['errors']));
            $this->redirect('/financeiro/contas-pagar/gerar?data_inicio=' . rawurlencode($dataInicio) . '&data_fim=' . rawurlencode($dataFim) . '&data_vencimento=' . rawurlencode($dataVencimento));
            return;
        }

        $this->flash('success', $resultado['mensagem'] ?? 'Contas a pagar geradas com sucesso.');
        $this->redirect('/financeiro/contas-pagar');
    }

    public function contratos(): void
    {
        $page = (int) $this->input('page', 1);
        $search = trim((string) $this->input('busca', ''));
        $result = (new ContratoPaciente())->listForIndex($page, 15, $search);

        $this->view('financeiro/contratos_index', [
            'pageTitle' => 'Contratos por paciente',
            'title' => 'Contratos (plano de atendimento)',
            'rows' => $result['data'],
            'pagination' => $result,
            'search' => $search,
            'finSubnav' => 'contratos',
        ]);
    }

    public function contratoNovo(): void
    {
        $this->view('financeiro/contrato_form', [
            'pageTitle' => 'Novo contrato',
            'title' => 'Novo contrato',
            'record' => [],
            'errors' => [],
            'options' => (new ContratoPaciente())->formOptions(),
            'finSubnav' => 'contratos',
        ]);
    }

    public function contratoStore(): void
    {
        $data = [
            'paciente_id' => (int) $this->input('paciente_id', 0),
            'tipo_servico' => trim((string) $this->input('tipo_servico', '')),
            'valor_mensal' => (float) str_replace(',', '.', (string) $this->input('valor_mensal', '0')),
            'dia_vencimento' => (int) $this->input('dia_vencimento', 10),
            'forma_pagamento' => trim((string) $this->input('forma_pagamento', '')),
            'vigencia_inicio' => trim((string) $this->input('vigencia_inicio', '')),
            'vigencia_fim' => trim((string) $this->input('vigencia_fim', '')),
            'status' => trim((string) $this->input('status', 'Ativo')),
            'observacoes' => trim((string) $this->input('observacoes', '')),
        ];

        $errors = [];
        if ($data['paciente_id'] <= 0) {
            $errors['paciente_id'] = 'Selecione o paciente.';
        }
        if ($data['tipo_servico'] === '') {
            $errors['tipo_servico'] = 'Informe o tipo de serviço.';
        }
        if ($data['valor_mensal'] <= 0) {
            $errors['valor_mensal'] = 'Informe o valor mensal.';
        }
        if ($data['vigencia_inicio'] === '') {
            $errors['vigencia_inicio'] = 'Informe o início da vigência.';
        }

        if ($errors !== []) {
            $this->view('financeiro/contrato_form', [
                'pageTitle' => 'Novo contrato',
                'title' => 'Novo contrato',
                'record' => $data,
                'errors' => $errors,
                'options' => (new ContratoPaciente())->formOptions(),
                'finSubnav' => 'contratos',
            ]);
            return;
        }

        if ($data['vigencia_fim'] === '') {
            $data['vigencia_fim'] = null;
        }

        $model = new ContratoPaciente();
        $model->createRecord($data);

        $this->flash('success', 'Contrato cadastrado. Geração automática de parcelas em contas a receber virá na próxima etapa.');
        $this->redirect('/financeiro/contratos');
    }

    public function relatorioExtrato(): void
    {
        $model = new Financeiro();
        $opts = $model->formOptions();
        $pacientes = $opts['paciente_id'] ?? [];

        $pacienteId = (int) $this->input('paciente_id', 0);
        $di = (string) $this->input('di', date('Y-m-01'));
        $df = (string) $this->input('df', date('Y-m-t'));

        $linhas = [];
        $totE = 0.0;
        $totS = 0.0;
        $nomePac = '';

        if ($pacienteId > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $di) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
            $raw = $model->extratoPorPaciente($pacienteId, $di, $df);
            foreach ($raw as $r) {
                $v = (float) ($r['valor'] ?? 0);
                if (($r['tipo_transacao'] ?? '') === 'Entrada') {
                    $totE += $v;
                } else {
                    $totS += $v;
                }
                $linhas[] = $r;
                if ($nomePac === '' && !empty($r['paciente_nome'])) {
                    $nomePac = (string) $r['paciente_nome'];
                }
            }
        }

        $this->view('financeiro/relatorio_extrato', [
            'pageTitle' => 'Extrato por paciente',
            'title' => 'Extrato por paciente',
            'pacientes' => $pacientes,
            'paciente_id' => $pacienteId,
            'di' => $di,
            'df' => $df,
            'linhas' => $linhas,
            'totEntradas' => $totE,
            'totSaidas' => $totS,
            'resultado' => $totE - $totS,
            'nomePaciente' => $nomePac,
            'finSubnav' => 'rextrato',
        ]);
    }

    public function relatorioFluxoCaixa(): void
    {
        $di = (string) $this->input('di', date('Y-m-01', strtotime('-5 months')));
        $df = (string) $this->input('df', date('Y-m-t'));

        $meses = [];
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $di) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
            $meses = (new Financeiro())->fluxoCaixaPorMes($di, $df);
        }

        $this->view('financeiro/relatorio_fluxo', [
            'pageTitle' => 'Fluxo de caixa',
            'title' => 'Fluxo de caixa por período',
            'di' => $di,
            'df' => $df,
            'meses' => $meses,
            'finSubnav' => 'rfluxo',
        ]);
    }

    public function relatorioInadimplencia(): void
    {
        $linhas = (new Financeiro())->listInadimplencia();
        $this->view('financeiro/relatorio_inadimplencia', [
            'pageTitle' => 'Inadimplência',
            'title' => 'Inadimplência (contas a receber vencidas)',
            'linhas' => $linhas,
            'finSubnav' => 'rinad',
        ]);
    }

    public function relatorioDre(): void
    {
        $di = (string) $this->input('di', date('Y-m-01'));
        $df = (string) $this->input('df', date('Y-m-t'));
        $dre = ['receita_bruta' => 0.0, 'custos_cuidadores' => 0.0, 'despesas_operacionais' => 0.0, 'resultado' => 0.0];

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $di) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $df)) {
            $dre = (new Financeiro())->dreSimplificado($di, $df);
        }

        $this->view('financeiro/relatorio_dre', [
            'pageTitle' => 'DRE simplificado',
            'title' => 'DRE simplificado (pagos no período)',
            'di' => $di,
            'df' => $df,
            'dre' => $dre,
            'finSubnav' => 'rdre',
        ]);
    }
}