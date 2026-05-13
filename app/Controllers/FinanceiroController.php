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
}
