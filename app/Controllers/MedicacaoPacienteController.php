<?php

namespace App\Controllers;

use App\Models\MedicacaoPaciente;
use App\Models\Paciente;

class MedicacaoPacienteController extends ResourceController
{
    /**
     * Lista as medicações de um paciente.
     * URL:
     * /pacientes/{pacienteId}/medicacoes
     */
    public function index(string $pacienteId): void
    {
        $pacienteModel = new Paciente();
        $medicacaoModel = new MedicacaoPaciente();

        $paciente = $this->pacientePorUuid($pacienteId);

        if (!$paciente) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Paciente não encontrado.'],
                'layouts/blank'
            );
            return;
        }

        $medicacoes = $medicacaoModel->listByPacienteId(
            (int)$paciente['id']
        );

        $this->view('pacientes/medicacoes/index', [
            'pageTitle'  => 'Medicações',
            'title'      => 'Medicações',
            'paciente'   => $paciente,
            'medicacoes' => $medicacoes,
        ]);
    }

    /**
     * Formulário de cadastro.
     * URL:
     * /pacientes/{pacienteId}/medicacoes/novo
     */
    public function create(string $pacienteId): void
    {
        $paciente = (new Paciente())->buscarPorId((int) $pacienteId);

        if (!$paciente) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Paciente não encontrado.'],
                'layouts/blank'
            );
            return;
        }

        $this->renderForm($paciente, [], [], null);
    }

    /**
     * Salva nova medicação.
     */
    public function store(string $pacienteId): void
    {
        $paciente = (new Paciente())->buscarPorId((int) $pacienteId);

        if (!$paciente) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Paciente não encontrado.'],
                'layouts/blank'
            );
            return;
        }

        $data = $this->inputData((int) $pacienteId);
        $errors = $this->validateData($data);

        if ($errors !== []) {
            $this->renderForm($paciente, $data, $errors, null);
            return;
        }

        (new MedicacaoPaciente())->createMedicacao($data);

        $this->flash('success', 'Medicação cadastrada com sucesso.');
        $this->redirect("/pacientes/{$pacienteId}?aba=medicacoes");
    }

    /**
     * Formulário de edição.
     */
    public function edit(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int) $id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Medicação não encontrada.'],
                'layouts/blank'
            );
            return;
        }

        $paciente = (new Paciente())->buscarPorId(
            (int) $medicacao['paciente_id']
        );

        $this->renderForm(
            $paciente,
            $medicacao,
            [],
            (int) $id
        );
    }

    /**
     * Atualiza medicação.
     */
    public function update(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int) $id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Medicação não encontrada.'],
                'layouts/blank'
            );
            return;
        }

        $pacienteId = (int) $medicacao['paciente_id'];
        $paciente = (new Paciente())->buscarPorId($pacienteId);

        $data = $this->inputData($pacienteId);
        $errors = $this->validateData($data);

        if ($errors !== []) {
            $this->renderForm(
                $paciente,
                array_merge($medicacao, $data),
                $errors,
                (int) $id
            );
            return;
        }

        $model->updateMedicacao((int) $id, $data);

        $this->flash('success', 'Medicação atualizada com sucesso.');
        $this->redirect("/pacientes/{$pacienteId}?aba=medicacoes");
    }

    /**
     * Inativação lógica.
     */
    public function inativar(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int) $id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Medicação não encontrada.'],
                'layouts/blank'
            );
            return;
        }

        $model->inativarMedicacao((int) $id);

        $this->flash('success', 'Medicação inativada com sucesso.');
        $this->redirect(
            '/pacientes/' . $medicacao['paciente_id'] . '?aba=medicacoes'
        );
    }

    /**
     * Exclusão definitiva.
     */
    public function destroy(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int) $id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view(
                'errors/404',
                ['message' => 'Medicação não encontrada.'],
                'layouts/blank'
            );
            return;
        }

        $pacienteId = (int) $medicacao['paciente_id'];

        $model->deleteMedicacao((int) $id);

        $this->flash('success', 'Medicação removida com sucesso.');
        $this->redirect("/pacientes/{$pacienteId}?aba=medicacoes");
    }

    /**
     * Renderiza o formulário.
     */
    private function renderForm(
        array $paciente,
        array $medicacao,
        array $errors,
        ?int $id
    ): void {
        $model = new MedicacaoPaciente();

        $this->view('pacientes/medicacoes/form', [
            'pageTitle' => $id ? 'Editar Medicação' : 'Nova Medicação',
            'title'     => $id ? 'Editar Medicação' : 'Nova Medicação',
            'paciente'  => $paciente,
            'medicacao' => $medicacao,
            'errors'    => $errors,
            'options'   => $model->formOptions(),
            'action'    => $id
                ? '/medicacoes/' . $id
                : '/pacientes/' . $paciente['id'] . '/medicacoes',
            'isEdit'    => $id !== null,
        ]);
    }

    /**
     * Captura dados do formulário.
     */
    private function inputData(int $pacienteId): array
    {
        return [
            'paciente_id'      => $pacienteId,
            'nome_medicamento' => $this->input('nome_medicamento', ''),
            'apresentacao'     => $this->input('apresentacao', ''),
            'dosagem'          => $this->input('dosagem', ''),
            'via'              => $this->input('via', ''),
            'horarios'         => $this->input('horarios', ''),
            'frequencia'       => $this->input('frequencia', ''),
            'data_inicio'      => $this->input('data_inicio', ''),
            'data_fim'         => $this->input('data_fim', ''),
            'status'           => $this->input('status', 'Ativo'),
            'observacoes'      => $this->input('observacoes', ''),
            'created_by' => $_SESSION['user']['id'] ?? null,
        ];
    }

    /**
     * Validação.
     */
    private function validateData(array $data): array
    {
        $errors = [];

        if (trim((string) $data['nome_medicamento']) === '') {
            $errors['nome_medicamento'] =
                'Informe o nome do medicamento.';
        }

        if (
            !in_array(
                $data['status'],
                ['Ativo', 'Inativo'],
                true
            )
        ) {
            $errors['status'] = 'Status inválido.';
        }

        return $errors;
    }

    private function pacientePorUuid(string $uuid): ?array
    {
        return (new Paciente())->findByUuid($uuid);
    }
}