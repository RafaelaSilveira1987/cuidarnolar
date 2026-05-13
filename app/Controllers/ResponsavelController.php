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
    protected array $requiredFields = ['nome_completo', 'endereco', 'cidade', 'estado', 'cpf'];
    protected array $formFields = [
        'nome_completo' => ['label' => 'Nome completo', 'span' => true, 'maxlength' => 100],
        'cpf' => ['label' => 'CPF', 'maxlength' => 14],
        'data_nascimento' => ['label' => 'Data de nascimento', 'type' => 'date'],
        'email' => ['label' => 'E-mail', 'type' => 'email'],
        'telefone' => ['label' => 'Telefone', 'maxlength' => 20],
        'grau_parentesco' => ['label' => 'Grau de parentesco', 'maxlength' => 50],
        'endereco' => ['label' => 'Endereco', 'span' => true, 'maxlength' => 255],
        'numero' => ['label' => 'Numero', 'maxlength' => 10],
        'bairro' => ['label' => 'Bairro', 'maxlength' => 50],
        'cidade' => ['label' => 'Cidade', 'maxlength' => 50],
        'estado' => ['label' => 'UF', 'maxlength' => 2],
        'cep' => ['label' => 'CEP', 'maxlength' => 10],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Ativo' => 'Ativo', 'Inativo' => 'Inativo'], 'default' => 'Ativo'],
        'motivo_inativacao' => ['label' => 'Motivo de inativacao', 'type' => 'textarea', 'span' => true],
    ];
}
