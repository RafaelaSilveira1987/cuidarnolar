<?php

namespace App\Controllers;

use App\Models\Cuidador;

class CuidadorController extends ResourceController
{
    protected string $modelClass = Cuidador::class;
    protected string $routeBase = '/cuidadores';
    protected string $viewTitle = 'Cuidadores';
    protected string $singularTitle = 'Cuidador';
    protected array $columns = [
        'id' => '#',
        'nome_completo' => 'Nome',
        'cpf' => 'CPF',
        'telefone' => 'Telefone',
        'especialidade' => 'Especialidade',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'nome_completo' => 'Nome completo',
        'cpf' => 'CPF',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'pix' => 'Pix',
        'especialidade' => 'Especialidade',
        'contrato_horas' => 'Contrato',
        'endereco_completo' => 'Endereco',
        'status' => 'Status',
    ];
}
