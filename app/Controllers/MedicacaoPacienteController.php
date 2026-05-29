<?php

namespace App\Controllers;

use App\Models\MedicacaoPaciente;
use App\Models\Paciente;

class MedicacaoPacienteController extends ResourceController
{
    /**
     * Lista as medicações de um paciente.
     * Rota: /pacientes/{uuid}/medicacoes
     *
     * A assinatura precisa ser index(): void para ser compatível com ResourceController::index().
     */
    public function index(): void
    {
        $args = func_get_args();
        $pacienteUuid = (string)($args[0] ?? '');

        $paciente = $this->pacientePorUuid($pacienteUuid);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $medicacoes = (new MedicacaoPaciente())->listByPacienteId((int)$paciente['id']);

        $this->view('pacientes/medicacoes/index', [
            'pageTitle'  => 'Medicações',
            'title'      => 'Medicações',
            'paciente'   => $paciente,
            'medicacoes' => $medicacoes,
        ]);
    }

    /**
     * Formulário de cadastro.
     * Rota: /pacientes/{uuid}/medicacoes/novo
     *
     * A assinatura precisa ser create(): void para ser compatível com ResourceController::create().
     */
    public function create(): void
    {
        $args = func_get_args();
        $pacienteUuid = (string)($args[0] ?? '');

        $paciente = $this->pacientePorUuid($pacienteUuid);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderMedicacaoForm($paciente, [], [], null);
    }

    /**
     * Salva nova medicação.
     * Rota: POST /pacientes/{uuid}/medicacoes
     *
     * A assinatura precisa ser store(): void para ser compatível com ResourceController::store().
     */
    public function store(): void
    {
        $args = func_get_args();
        $pacienteUuid = (string)($args[0] ?? '');

        $paciente = $this->pacientePorUuid($pacienteUuid);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $pacienteId = (int)$paciente['id'];
        $data = $this->inputData($pacienteId);
        $errors = $this->validateData($data);

        if ($errors !== []) {
            $this->renderMedicacaoForm($paciente, $data, $errors, null);
            return;
        }

        (new MedicacaoPaciente())->createMedicacao($data);

        $this->flash('success', 'Medicação cadastrada com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)$paciente['uuid']) . '?aba=medicacoes');
    }

    /**
     * Formulário de edição.
     * Rota: /medicacoes/{id}/editar
     */
    public function edit(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int)$id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Medicação não encontrada.'], 'layouts/blank');
            return;
        }

        $paciente = (new Paciente())->buscarPorId((int)$medicacao['paciente_id']);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderMedicacaoForm($paciente, $medicacao, [], (int)$id);
    }

    /**
     * Atualiza medicação.
     * Rota: POST /medicacoes/{id}
     */
    public function update(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int)$id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Medicação não encontrada.'], 'layouts/blank');
            return;
        }

        $pacienteId = (int)$medicacao['paciente_id'];
        $paciente = (new Paciente())->buscarPorId($pacienteId);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->inputData($pacienteId);
        $errors = $this->validateData($data);

        if ($errors !== []) {
            $this->renderMedicacaoForm($paciente, array_merge($medicacao, $data), $errors, (int)$id);
            return;
        }

        $model->updateMedicacao((int)$id, $data);

        $this->flash('success', 'Medicação atualizada com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)$paciente['uuid']) . '?aba=medicacoes');
    }

    /**
     * Inativação lógica.
     * Rota: POST /medicacoes/{id}/inativar
     */
    public function inativar(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int)$id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Medicação não encontrada.'], 'layouts/blank');
            return;
        }

        $paciente = (new Paciente())->buscarPorId((int)$medicacao['paciente_id']);
        $model->inativarMedicacao((int)$id);

        $this->flash('success', 'Medicação inativada com sucesso.');

        $destino = $paciente && !empty($paciente['uuid'])
            ? '/pacientes/' . rawurlencode((string)$paciente['uuid']) . '?aba=medicacoes'
            : '/pacientes';

        $this->redirect($destino);
    }

    /**
     * Exclusão definitiva/inativação.
     * Rota: POST /medicacoes/{id}/delete
     */
    public function destroy(string $id): void
    {
        $model = new MedicacaoPaciente();
        $medicacao = $model->buscarPorId((int)$id);

        if (!$medicacao) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Medicação não encontrada.'], 'layouts/blank');
            return;
        }

        $paciente = (new Paciente())->buscarPorId((int)$medicacao['paciente_id']);
        $model->deleteMedicacao((int)$id);

        $this->flash('success', 'Medicação removida com sucesso.');

        $destino = $paciente && !empty($paciente['uuid'])
            ? '/pacientes/' . rawurlencode((string)$paciente['uuid']) . '?aba=medicacoes'
            : '/pacientes';

        $this->redirect($destino);
    }

    private function renderMedicacaoForm(array $paciente, array $medicacao, array $errors, ?int $id): void
    {
        $model = new MedicacaoPaciente();
        $pacienteUuid = (string)($paciente['uuid'] ?? '');

        $this->view('pacientes/medicacoes/form', [
            'pageTitle' => $id ? 'Editar Medicação' : 'Nova Medicação',
            'title'     => $id ? 'Editar Medicação' : 'Nova Medicação',
            'paciente'  => $paciente,
            'medicacao' => $medicacao,
            'errors'    => $errors,
            'options'   => $model->formOptions(),
            'action'    => $id
                ? '/medicacoes/' . $id
                : '/pacientes/' . rawurlencode($pacienteUuid) . '/medicacoes',
            'isEdit'    => $id !== null,
        ]);
    }

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
            'created_by'       => $_SESSION['user']['id'] ?? null,
        ];
    }

    private function validateData(array $data): array
    {
        $errors = [];

        if (trim((string)$data['nome_medicamento']) === '') {
            $errors['nome_medicamento'] = 'Informe o nome do medicamento.';
        }

        if (!in_array($data['status'], ['Ativo', 'Inativo'], true)) {
            $errors['status'] = 'Status inválido.';
        }

        return $errors;
    }

    private function pacientePorUuid(string $uuid): ?array
    {
        if ($uuid === '') {
            return null;
        }

        return (new Paciente())->buscarPorUuid($uuid);
    }
}