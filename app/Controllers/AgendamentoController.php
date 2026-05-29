<?php

namespace App\Controllers;

use App\Models\Evento;

class AgendamentoController extends BaseController
{
    private Evento $eventoModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventoModel = new Evento();
    }

    public function index(): void
    {
        $dataSelecionada = $this->normalizarData((string)$this->input('data', date('Y-m-d')));
        $ts = strtotime($dataSelecionada) ?: time();
        $ano = (int)date('Y', $ts);
        $mes = (int)date('m', $ts);

        $this->view('agendamentos/index', [
            'pageTitle' => 'Agenda',
            'title' => 'Agenda',
            'routeBase' => '/agendamentos',
            'dataSelecionada' => $dataSelecionada,
            'eventosDia' => $this->eventoModel->eventosDoDia($dataSelecionada),
            'proximos' => $this->eventoModel->proximos(8),
            'pendentes' => $this->eventoModel->pendentes(8),
            'resumoStatus' => $this->eventoModel->resumoPorStatus(),
            'diasComEventos' => $this->eventoModel->diasComEventos($ano, $mes),
            'anoCalendario' => $ano,
            'mesCalendario' => $mes,
        ]);
    }

    public function show(string $uuid): void
    {
        $evento = $this->eventoModel->findForShowByUuid($uuid);

        if (!$evento) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Agendamento nao encontrado.'], 'layouts/blank');
            return;
        }

        $this->view('agendamentos/show', [
            'pageTitle' => 'Agendamento',
            'title' => 'Agendamento',
            'evento' => $evento,
            'routeBase' => '/agendamentos',
        ]);
    }

    public function create(): void
    {
        $record = [
            'data_inicio' => date('Y-m-d\TH:i'),
            'prioridade' => 'Normal',
            'status' => 'Pendente',
        ];

        $this->renderForm($record, [], 'Novo compromisso');
    }

    public function store(): void
    {
        $data = $this->formInput();
        $errors = $this->validateAgenda($data);

        if ($errors !== []) {
            $this->renderForm($data, $errors, 'Novo compromisso');
            return;
        }

        $id = $this->eventoModel->createRecord($data);
        $created = $this->eventoModel->findForShow($id);

        $this->flash('success', 'Compromisso cadastrado com sucesso.');
        $this->redirect('/agendamentos/' . rawurlencode((string)($created['uuid'] ?? $id)));
    }

    public function edit(string $uuid): void
    {
        $record = $this->eventoModel->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Agendamento nao encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderForm($record, [], 'Editar compromisso', $uuid);
    }

    public function update(string $uuid): void
    {
        $record = $this->eventoModel->findForShowByUuid($uuid);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Agendamento nao encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->formInput();
        $errors = $this->validateAgenda($data);

        if ($errors !== []) {
            $this->renderForm(array_merge($record, $data), $errors, 'Editar compromisso', $uuid);
            return;
        }

        $this->eventoModel->updateRecordByUuid($uuid, $data);

        $this->flash('success', 'Compromisso atualizado com sucesso.');
        $this->redirect('/agendamentos/' . rawurlencode($uuid));
    }

    private function renderForm(array $record, array $errors, string $title, ?string $uuid = null): void
    {
        $this->view('agendamentos/form', [
            'pageTitle' => $title,
            'title' => $title,
            'record' => $record,
            'errors' => $errors,
            'options' => $this->eventoModel->formOptions(),
            'action' => $uuid ? '/agendamentos/' . rawurlencode($uuid) : '/agendamentos',
            'isEdit' => $uuid !== null,
        ]);
    }

    private function formInput(): array
    {
        $dataInicio = $this->normalizarDateTime((string)$this->input('data_inicio', ''));
        $dataFim = $this->normalizarDateTime((string)$this->input('data_fim', ''));

        return [
            'paciente_id' => $this->nullableInt($this->input('paciente_id', null)),
            'cuidador_id' => $this->nullableInt($this->input('cuidador_id', null)),
            'tipo_evento' => trim((string)$this->input('tipo_evento', 'Outro')),
            'titulo' => trim((string)$this->input('titulo', '')),
            'descricao' => trim((string)$this->input('descricao', '')),
            'data_inicio' => $dataInicio,
            'data_evento' => $dataInicio,
            'data_fim' => $dataFim,
            'local' => trim((string)$this->input('local', '')),
            'prioridade' => trim((string)$this->input('prioridade', 'Normal')),
            'status' => trim((string)$this->input('status', 'Pendente')),
        ];
    }

    private function validateAgenda(array $data): array
    {
        $errors = [];

        if (($data['titulo'] ?? '') === '') {
            $errors['titulo'] = 'Informe o titulo do compromisso.';
        }

        if (($data['tipo_evento'] ?? '') === '') {
            $errors['tipo_evento'] = 'Informe o tipo do compromisso.';
        }

        if (($data['data_inicio'] ?? '') === '') {
            $errors['data_inicio'] = 'Informe a data e hora inicial.';
        }

        if (!empty($data['data_fim']) && !empty($data['data_inicio']) && strtotime($data['data_fim']) < strtotime($data['data_inicio'])) {
            $errors['data_fim'] = 'A data final nao pode ser menor que a inicial.';
        }

        return $errors;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $int = (int)$value;
        return $int > 0 ? $int : null;
    }

    private function normalizarDateTime(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (str_contains($value, 'T')) {
            $value = str_replace('T', ' ', $value);
        }

        return strlen($value) === 16 ? $value . ':00' : $value;
    }

    private function normalizarData(string $date): string
    {
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : date('Y-m-d');
    }
}
