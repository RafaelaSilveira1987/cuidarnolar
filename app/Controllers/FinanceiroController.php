<?php

namespace App\Controllers;

use App\Models\Financeiro;

class FinanceiroController extends ResourceController
{
    protected string $modelClass = Financeiro::class;
    protected string $routeBase = '/financeiro';
    protected string $viewTitle = 'Financeiro';
    protected string $singularTitle = 'Lancamento financeiro';
    protected array $columns = [
        'id' => '#',
        'data' => 'Data',
        'tipo_transacao' => 'Tipo',
        'valor_formatado' => 'Valor',
        'paciente_nome' => 'Paciente',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'data' => 'Data',
        'tipo_transacao' => 'Tipo',
        'moeda' => 'Forma',
        'valor_formatado' => 'Valor',
        'status' => 'Status',
        'paciente_nome' => 'Paciente',
        'responsavel_nome' => 'Responsavel',
        'cuidador_nome' => 'Cuidador',
        'observacoes' => 'Observacoes',
    ];
    protected array $requiredFields = ['data', 'tipo_transacao', 'status', 'observacoes'];
    protected array $formFields = [
        'tipo_transacao' => ['label' => 'Tipo', 'type' => 'select', 'options' => ['Entrada' => 'Entrada', 'Saída' => 'Saida']],
        'data' => ['label' => 'Data', 'type' => 'datetime-local'],
        'valor' => ['label' => 'Valor', 'type' => 'number'],
        'moeda' => ['label' => 'Forma de pagamento', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Pix' => 'Pix', 'Depósito' => 'Deposito', 'Boleto' => 'Boleto', 'Dinheiro' => 'Dinheiro']],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Pendente' => 'Pendente', 'Pago' => 'Pago', 'Transporte' => 'Transporte']],
        'paciente_id' => ['label' => 'Paciente', 'type' => 'select', 'empty' => 'Sem paciente'],
        'responsavel_id' => ['label' => 'Responsavel', 'type' => 'select', 'empty' => 'Sem responsavel'],
        'cuidador_id' => ['label' => 'Cuidador', 'type' => 'select', 'empty' => 'Sem cuidador'],
        'plano_id' => ['label' => 'Plano'],
        'observacoes' => ['label' => 'Observacoes', 'type' => 'textarea', 'span' => true],
    ];

    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $search = trim((string) $this->input('busca', ''));
        $tipo = (string) $this->input('tipo', '');
        $tipo = in_array($tipo, ['entrada', 'saida'], true) ? $tipo : '';
        $result = (new Financeiro())->listByType($page, 15, $search, $tipo);

        $this->view('resources/index', [
            'pageTitle' => $this->viewTitle,
            'title' => $this->viewTitle,
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
        ]);
    }
}
