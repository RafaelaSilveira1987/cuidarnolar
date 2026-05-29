<?php

namespace App\Controllers;

use App\Models\Responsavel;

class ResponsavelController extends ResourceController
{
    protected string $modelClass = Responsavel::class;
    protected string $routeBase = '/responsaveis';
    protected string $viewTitle = 'Responsáveis';
    protected string $singularTitle = 'Responsável';

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
        'endereco_completo' => 'Endereço',
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
        'endereco' => ['label' => 'Endereço', 'span' => true, 'maxlength' => 255],
        'numero' => ['label' => 'Número', 'maxlength' => 10],
        'bairro' => ['label' => 'Bairro', 'maxlength' => 50],
        'cidade' => ['label' => 'Cidade', 'maxlength' => 50],
        'estado' => ['label' => 'UF', 'maxlength' => 2],
        'cep' => ['label' => 'CEP', 'maxlength' => 10],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Ativo' => 'Ativo', 'Inativo' => 'Inativo'], 'default' => 'Ativo'],
        'motivo_inativacao' => ['label' => 'Motivo de inativação', 'type' => 'textarea', 'span' => true],
    ];

    public function show(string $id): void
    {
        $record = $this->resolveResponsavel($id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Responsável não encontrado.'], 'layouts/blank');
            return;
        }

        $this->view('resources/show', [
            'pageTitle' => $this->singularTitle,
            'title' => $this->singularTitle,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'fields' => $this->detailFields,
            'resourceKey' => $record['uuid'] ?? $record['id'],
            'pacientesVinculados' => $this->responsavelModel()->pacientesVinculados((int)$record['id']),
        ]);
    }

    public function edit(string $id): void
    {
        $record = $this->resolveResponsavel($id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Responsável não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderForm($record, [], 'Editar ' . $this->singularTitle, (int)$record['id']);
    }

    public function update(string $id): void
    {
        $record = $this->resolveResponsavel($id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Responsável não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->formInput();
        $errors = $this->validateResource($data);

        if ($errors !== []) {
            $this->renderForm(array_merge($record, $data), $errors, 'Editar ' . $this->singularTitle, (int)$record['id']);
            return;
        }

        $this->responsavelModel()->updateRecord((int)$record['id'], $data);
        $this->flash('success', 'Responsável atualizado com sucesso.');
        $this->redirect('/responsaveis/' . rawurlencode((string)($record['uuid'] ?? $record['id'])));
    }

    protected function renderForm(array $record, array $errors, string $title, ?int $id = null): void
    {
        $resourceKey = $record['uuid'] ?? $id;

        $this->view('resources/form', [
            'pageTitle' => $title,
            'title' => $title,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'errors' => $errors,
            'fields' => $this->formFields,
            'options' => $this->model()->formOptions(),
            'action' => $id ? $this->routeBase . '/' . rawurlencode((string)$resourceKey) : $this->routeBase,
            'isEdit' => $id !== null,
        ]);
    }

    private function resolveResponsavel(string $id): array|false
    {
        $model = $this->responsavelModel();

        if (ctype_digit($id)) {
            return $model->findForShow((int)$id);
        }

        return $model->findForShowByUuid($id);
    }

    private function responsavelModel(): Responsavel
    {
        return new Responsavel();
    }
}
