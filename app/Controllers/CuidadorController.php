<?php

namespace App\Controllers;

use App\Models\BaseModuleModel;
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
        'rg' => 'RG',
        'data_nascimento' => 'Data de nascimento',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'pix' => 'Pix',
        'especialidade' => 'Especialidade',
        'contrato_horas' => 'Contrato de horas',
        'endereco_completo' => 'Endereço',
        'status' => 'Status',
        'motivo_inativacao' => 'Motivo de inativação',
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
        'especialidade' => ['label' => 'Especialidade', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Cuidador' => 'Cuidador', 'Acompanhante' => 'Acompanhante', 'Enfermeira' => 'Enfermeira', 'Técnico de Enfermagem' => 'Técnico de Enfermagem']],
        'contrato_horas' => ['label' => 'Contrato de horas', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['6h' => '6h', '8h' => '8h', '12h' => '12h', '24h' => '24h']],
        'endereco' => ['label' => 'Endereço', 'span' => true, 'maxlength' => 150],
        'numero' => ['label' => 'Número', 'maxlength' => 10],
        'bairro' => ['label' => 'Bairro', 'maxlength' => 50],
        'cidade' => ['label' => 'Cidade', 'maxlength' => 50],
        'estado' => ['label' => 'UF', 'maxlength' => 2],
        'cep' => ['label' => 'CEP', 'maxlength' => 10],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Ativo' => 'Ativo', 'Inativo' => 'Inativo', 'Standby' => 'Standby'], 'default' => 'Ativo'],
        'motivo_inativacao' => ['label' => 'Motivo de inativação', 'type' => 'textarea', 'span' => true],
    ];

    public function show(string $uuid): void
    {
        $record = $this->cuidadorModel()->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Cuidador não encontrado.'], 'layouts/blank');
            return;
        }

        $this->view('cuidadores/show', [
            'pageTitle' => 'Cuidador',
            'title' => 'Cuidador',
            'routeBase' => $this->routeBase,
            'record' => $record,
            'fields' => $this->detailFields,
            'resourceKey' => $record['uuid'] ?? $uuid,
        ]);
    }

    public function store(): void
    {
        $data = $this->formInput();
        $errors = $this->validateResource($data);

        if ($errors !== []) {
            $this->renderForm($data, $errors, 'Novo Cuidador');
            return;
        }

        $id = $this->cuidadorModel()->createRecord($data);
        $record = $this->cuidadorModel()->findForShow((int) $id);
        $key = $record['uuid'] ?? $id;

        $this->flash('success', 'Cuidador cadastrado com sucesso.');
        $this->redirect($this->routeBase . '/' . rawurlencode((string) $key));
    }

    public function edit(string $uuid): void
    {
        $record = $this->cuidadorModel()->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Cuidador não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderForm($record, [], 'Editar Cuidador', (int) ($record['id'] ?? 0));
    }

    public function update(string $uuid): void
    {
        $model = $this->cuidadorModel();
        $record = $model->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Cuidador não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->formInput();
        $errors = $this->validateResource($data);

        if ($errors !== []) {
            $merged = array_merge($record, $data);
            $merged['uuid'] = $record['uuid'] ?? $uuid;
            $this->renderForm($merged, $errors, 'Editar Cuidador', (int) ($record['id'] ?? 0));
            return;
        }

        $model->updateRecord((int) $record['id'], $data);

        $this->flash('success', 'Cuidador atualizado com sucesso.');
        $this->redirect($this->routeBase . '/' . rawurlencode((string)($record['uuid'] ?? $uuid)));
    }

    public function inativar(string $uuid): void
    {
        $model = $this->cuidadorModel();
        $record = $model->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Cuidador não encontrado.'], 'layouts/blank');
            return;
        }

        $model->inativar((int) $record['id'], (string) $this->input('motivo_inativacao', ''));
        $this->flash('success', 'Cuidador inativado com sucesso.');
        $this->redirect($this->routeBase);
    }

    protected function renderForm(array $record, array $errors, string $title, string|int|null $id = null): void
    
    {
        $resourceKey = $record['uuid'] ?? null;
        $action = $id && $resourceKey
            ? $this->routeBase . '/' . rawurlencode((string)$resourceKey)
            : $this->routeBase;

        $this->view('resources/form', [
            'pageTitle' => $title,
            'title' => $title,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'errors' => $errors,
            'fields' => $this->formFields,
            'options' => $this->model()->formOptions(),
            'action' => $action,
            'isEdit' => $id !== null,
        ]);
    }

    protected function model(): BaseModuleModel
    {
        return $this->cuidadorModel();
    }

    private function cuidadorModel(): Cuidador
    {
        return new Cuidador();
    }
}