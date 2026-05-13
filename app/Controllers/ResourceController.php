<?php

namespace App\Controllers;

use App\Models\BaseModuleModel;

abstract class ResourceController extends BaseController
{
    protected string $modelClass;
    protected string $routeBase;
    protected string $viewTitle;
    protected string $singularTitle;
    protected array $columns = [];
    protected array $detailFields = [];
    protected array $formFields = [];
    protected array $requiredFields = [];

    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $search = trim((string) $this->input('busca', ''));
        $result = $this->model()->listForIndex($page, 15, $search);

        $this->view('resources/index', [
            'pageTitle' => $this->viewTitle,
            'title' => $this->viewTitle,
            'routeBase' => $this->routeBase,
            'columns' => $this->columns,
            'rows' => $result['data'],
            'pagination' => $result,
            'search' => $search,
        ]);
    }

    public function show(string $id): void
    {
        $record = $this->model()->findForShow((int) $id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => "{$this->singularTitle} nao encontrado."], 'layouts/blank');
            return;
        }

        $this->view('resources/show', [
            'pageTitle' => $this->singularTitle,
            'title' => $this->singularTitle,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'fields' => $this->detailFields,
        ]);
    }

    public function create(): void
    {
        $this->renderForm([], [], 'Novo ' . $this->singularTitle);
    }

    public function store(): void
    {
        $data = $this->formInput();
        $errors = $this->validateResource($data);

        if ($errors !== []) {
            $this->renderForm($data, $errors, 'Novo ' . $this->singularTitle);
            return;
        }

        $id = $this->model()->createRecord($data);

        $this->flash('success', "{$this->singularTitle} cadastrado com sucesso.");
        $this->redirect($this->routeBase . '/' . $id);
    }

    public function edit(string $id): void
    {
        $record = $this->model()->findForShow((int) $id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => "{$this->singularTitle} nao encontrado."], 'layouts/blank');
            return;
        }

        $this->renderForm($record, [], 'Editar ' . $this->singularTitle, (int) $id);
    }

    public function update(string $id): void
    {
        $model = $this->model();
        $record = $model->findForShow((int) $id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => "{$this->singularTitle} nao encontrado."], 'layouts/blank');
            return;
        }

        $data = $this->formInput();
        $errors = $this->validateResource($data);

        if ($errors !== []) {
            $this->renderForm(array_merge($record, $data), $errors, 'Editar ' . $this->singularTitle, (int) $id);
            return;
        }

        $model->updateRecord((int) $id, $data);

        $this->flash('success', "{$this->singularTitle} atualizado com sucesso.");
        $this->redirect($this->routeBase . '/' . $id);
    }

    public function inativar(string $id): void
    {
        $this->model()->inativar((int) $id, (string) $this->input('motivo_inativacao', ''));
        $this->flash('success', "{$this->singularTitle} inativado com sucesso.");
        $this->redirect($this->routeBase);
    }

    protected function renderForm(array $record, array $errors, string $title, ?int $id = null): void
    {
        $this->view('resources/form', [
            'pageTitle' => $title,
            'title' => $title,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'errors' => $errors,
            'fields' => $this->formFields,
            'options' => $this->model()->formOptions(),
            'action' => $id ? "{$this->routeBase}/{$id}" : $this->routeBase,
            'isEdit' => $id !== null,
        ]);
    }

    protected function formInput(): array
    {
        $data = [];
        foreach ($this->formFields as $name => $field) {
            $data[$name] = $this->input($name, '');
        }

        return $data;
    }

    protected function validateResource(array $data): array
    {
        $errors = [];
        foreach ($this->requiredFields as $field) {
            if (trim((string) ($data[$field] ?? '')) === '') {
                $errors[$field] = 'Campo obrigatorio.';
            }
        }

        return $errors;
    }

    protected function model(): BaseModuleModel
    {
        return new $this->modelClass();
    }
}
