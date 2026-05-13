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
    protected array $requiredFields = ['nome_completo', 'endereco', 'cidade', 'estado', 'cep', 'cpf'];
    protected array $formFields = [
        'nome_completo' => ['label' => 'Nome completo', 'span' => true, 'maxlength' => 100],
        'cpf' => ['label' => 'CPF', 'maxlength' => 14],
        'rg' => ['label' => 'RG', 'maxlength' => 20],
        'data_nascimento' => ['label' => 'Data de nascimento', 'type' => 'date'],
        'email' => ['label' => 'E-mail', 'type' => 'email'],
        'telefone' => ['label' => 'Telefone', 'maxlength' => 20],
        'pix' => ['label' => 'Pix', 'maxlength' => 100],
        'especialidade' => ['label' => 'Especialidade', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Cuidador' => 'Cuidador', 'Acompanhante' => 'Acompanhante', 'Enfermeira' => 'Enfermeira']],
        'contrato_horas' => ['label' => 'Contrato de horas', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['6h' => '6h', '8h' => '8h', '12h' => '12h', '24h' => '24h']],
        'endereco' => ['label' => 'Endereco', 'span' => true, 'maxlength' => 150],
        'numero' => ['label' => 'Numero', 'maxlength' => 10],
        'bairro' => ['label' => 'Bairro', 'maxlength' => 50],
        'cidade' => ['label' => 'Cidade', 'maxlength' => 50],
        'estado' => ['label' => 'UF', 'maxlength' => 2],
        'cep' => ['label' => 'CEP', 'maxlength' => 10],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Ativo' => 'Ativo', 'Inativo' => 'Inativo', 'Standby' => 'Standby'], 'default' => 'Ativo'],
        'motivo_inativacao' => ['label' => 'Motivo de inativacao', 'type' => 'textarea', 'span' => true],
    ];
}
