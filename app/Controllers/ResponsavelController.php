<?php

namespace App\Controllers;

use App\Models\Responsavel;

class ResponsavelController extends ResourceController
{
    protected string $modelClass = Responsavel::class;
    protected string $routeBase = '/responsaveis';
    protected string $viewTitle = 'Responsaveis';
    protected string $singularTitle = 'Responsavel';
    protected array $columns = [
        'id' => '#',
        'nome_completo' => 'Nome',
        'cpf' => 'CPF',
        'telefone' => 'Telefone',
        'cidade' => 'Cidade',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'nome_completo' => 'Nome completo',
        'cpf' => 'CPF',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'grau_parentesco' => 'Parentesco',
        'endereco_completo' => 'Endereco',
        'status' => 'Status',
    ];
}
