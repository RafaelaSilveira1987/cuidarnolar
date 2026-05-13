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
        $this->view('resources/form-placeholder', [
            'pageTitle' => 'Novo ' . $this->singularTitle,
            'title' => 'Novo ' . $this->singularTitle,
            'routeBase' => $this->routeBase,
            'message' => 'Formulario de cadastro sera migrado na proxima etapa deste modulo.',
        ]);
    }

    public function edit(string $id): void
    {
        $record = $this->model()->findForShow((int) $id);

        $this->view('resources/form-placeholder', [
            'pageTitle' => 'Editar ' . $this->singularTitle,
            'title' => 'Editar ' . $this->singularTitle,
            'routeBase' => $this->routeBase,
            'record' => $record,
            'message' => 'Formulario de edicao sera migrado preservando as regras do legado.',
        ]);
    }

    protected function model(): BaseModuleModel
    {
        return new $this->modelClass();
    }
}
