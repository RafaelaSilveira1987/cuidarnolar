<?php

namespace App\Controllers;

use App\Models\Paciente;

class PacienteController extends ResourceController
{
    protected string $modelClass = Paciente::class;
    protected string $routeBase = '/pacientes';
    protected string $viewTitle = 'Pacientes';
    protected string $singularTitle = 'Paciente';
    protected array $columns = [
        'id' => '#',
        'nome_completo' => 'Nome',
        'cpf' => 'CPF',
        'responsavel_nome' => 'Responsavel',
        'cuidador_nome' => 'Cuidador',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'nome_completo' => 'Nome completo',
        'data_nascimento' => 'Nascimento',
        'cpf' => 'CPF',
        'rg' => 'RG',
        'cartao_nac_sus' => 'Cartao SUS',
        'plano_saude' => 'Plano de saude',
        'responsavel_nome' => 'Responsavel',
        'cuidador_nome' => 'Cuidador',
        'status' => 'Status',
    ];

    public function create(): void
    {
        $this->renderPacienteForm([], [], 'Novo Paciente');
    }

    public function store(): void
    {
        $data = $this->pacienteInput();
        $errors = $this->validatePaciente($data);

        if ($errors !== []) {
            $this->renderPacienteForm($data, $errors, 'Novo Paciente');
            return;
        }

        $id = $this->pacienteModel()->createPaciente($data);

        $this->flash('success', 'Paciente cadastrado com sucesso.');
        $this->redirect('/pacientes/' . $id);
    }

    public function edit(string $id): void
    {
        $paciente = $this->pacienteModel()->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderPacienteForm($paciente, [], 'Editar Paciente', (int) $id);
    }

    public function update(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $model->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->pacienteInput();
        $errors = $this->validatePaciente($data);

        if ($errors !== []) {
            $this->renderPacienteForm(array_merge($paciente, $data), $errors, 'Editar Paciente', (int) $id);
            return;
        }

        $model->updatePaciente((int) $id, $data);

        $this->flash('success', 'Paciente atualizado com sucesso.');
        $this->redirect('/pacientes/' . $id);
    }

    public function inativar(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $model->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $model->inativar((int) $id, (string) $this->input('motivo_inativacao', ''));

        $this->flash('success', 'Paciente inativado com sucesso.');
        $this->redirect('/pacientes');
    }

    /** Evita sobrescrever {@see ResourceController::renderForm()} (conflito de visibilidade na herança). */
    protected function renderPacienteForm(array $paciente, array $errors, string $title, ?int $id = null): void
    {
        $model = $this->pacienteModel();

        $this->view('pacientes/form', [
            'pageTitle' => $title,
            'title' => $title,
            'paciente' => $paciente,
            'errors' => $errors,
            'responsaveis' => $model->responsaveisOptions(),
            'cuidadores' => $model->cuidadoresOptions(),
            'action' => $id ? "/pacientes/{$id}" : '/pacientes',
            'isEdit' => $id !== null,
        ]);
    }

    private function validatePaciente(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['nome_completo'] ?? '')) === '') {
            $errors['nome_completo'] = 'Informe o nome completo.';
        }

        if (!empty($data['data_nascimento']) && strtotime((string) $data['data_nascimento']) === false) {
            $errors['data_nascimento'] = 'Informe uma data de nascimento valida.';
        }

        if (!in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)) {
            $errors['status'] = 'Status invalido.';
        }

        return $errors;
    }

    private function pacienteInput(): array
    {
        return [
            'nome_completo' => $this->input('nome_completo', ''),
            'data_nascimento' => $this->input('data_nascimento', ''),
            'cpf' => $this->input('cpf', ''),
            'rg' => $this->input('rg', ''),
            'cartao_nac_sus' => $this->input('cartao_nac_sus', ''),
            'plano_saude' => $this->input('plano_saude', ''),
            'responsavel_id' => $this->input('responsavel_id', ''),
            'cuidador_id' => $this->input('cuidador_id', ''),
            'anamnese_id' => $this->input('anamnese_id', ''),
            'status' => $this->input('status', 'Ativo'),
            'motivo_inativacao' => $this->input('motivo_inativacao', ''),
        ];
    }

    private function pacienteModel(): Paciente
    {
        return new Paciente();
    }
}