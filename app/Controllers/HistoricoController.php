<?php

namespace App\Controllers;

use App\Models\Historico;
use App\Models\Paciente;

class HistoricoController extends ResourceController
{
    protected string $modelClass = Historico::class;
    protected string $routeBase = '/historicos';
    protected string $viewTitle = 'Historicos';
    protected string $singularTitle = 'Historico';
    protected array $columns = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'necessidades' => 'Necessidades',
        'limitacoes' => 'Limitacoes',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'historico_familiar' => 'Historico familiar',
        'historico_profissional' => 'Historico profissional',
        'historico_medico' => 'Historico medico',
        'internacoes' => 'Internacoes',
        'necessidades' => 'Necessidades',
        'limitacoes' => 'Limitacoes',
        'status' => 'Status',
    ];
    protected array $requiredFields = ['paciente_id'];
    protected array $formFields = [
        'paciente_id' => ['label' => 'Paciente', 'type' => 'select', 'empty' => 'Selecione'],
        'historico_familiar' => ['label' => 'Historico familiar', 'type' => 'textarea', 'span' => true],
        'historico_profissional' => ['label' => 'Historico profissional', 'type' => 'textarea', 'span' => true],
        'historico_medico' => ['label' => 'Historico medico', 'type' => 'textarea', 'span' => true],
        'internacoes' => ['label' => 'Internacoes', 'type' => 'textarea', 'span' => true],
        'necessidades' => ['label' => 'Necessidades'],
        'limitacoes' => ['label' => 'Limitacoes'],
        'status' => ['label' => 'Status', 'type' => 'select', 'options' => ['Pendente' => 'Pendente', 'Finalizado' => 'Finalizado'], 'default' => 'Pendente'],
    ];

    public function create(): void
    {
        $prefill = [];
        $pacienteUuid = trim((string)$this->input('paciente_uuid', ''));

        if ($pacienteUuid !== '') {
            $paciente = (new Paciente())->buscarPorUuid($pacienteUuid);
            if ($paciente) {
                $prefill['paciente_id'] = (string)($paciente['id'] ?? '');
            }
        }

        $this->renderForm($prefill, [], 'Novo ' . $this->singularTitle);
    }
}
