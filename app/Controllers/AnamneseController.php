<?php

namespace App\Controllers;

use App\Models\Anamnese;
use App\Models\Paciente;

class AnamneseController extends ResourceController
{
    protected string $modelClass = Anamnese::class;
    protected string $routeBase = '/anamneses';
    protected string $viewTitle = 'Anamneses';
    protected string $singularTitle = 'Anamnese';
    protected array $columns = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'data_anamnese' => 'Data',
        'patologia' => 'Patologia',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'data_anamnese' => 'Data',
        'patologia' => 'Patologia',
        'sintomas' => 'Sintomas',
        'historia_medica' => 'Historia medica',
        'medicacao_continua' => 'Medicacao continua',
        'medico' => 'Medico',
        'status' => 'Status',
    ];
    protected array $requiredFields = ['paciente_id', 'data_anamnese'];
    protected array $formFields = [
        'paciente_id' => ['label' => 'Paciente', 'type' => 'select', 'empty' => 'Selecione'],
        'data_anamnese' => ['label' => 'Data da anamnese', 'type' => 'date'],
        'patologia' => ['label' => 'Patologia'],
        'sintomas' => ['label' => 'Sintomas'],
        'sequelas' => ['label' => 'Sequelas'],
        'historia_medica' => ['label' => 'Historia medica'],
        'cirurgia' => ['label' => 'Cirurgia'],
        'protese' => ['label' => 'Protese', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'acamado' => ['label' => 'Acamado', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'hipertensao' => ['label' => 'Hipertensao', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'diabetes' => ['label' => 'Diabetes', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'alergia' => ['label' => 'Alergia'],
        'estilo_de_vida' => ['label' => 'Estilo de vida'],
        'dieta' => ['label' => 'Dieta'],
        'medicacao_continua' => ['label' => 'Medicacao continua', 'span' => true, 'maxlength' => 100],
        'sono' => ['label' => 'Sono'],
        'visao' => ['label' => 'Visao'],
        'audicao' => ['label' => 'Audicao'],
        'incontinencia' => ['label' => 'Incontinencia', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'demencia' => ['label' => 'Demencia', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Sim' => 'Sim', 'Não' => 'Nao']],
        'cognicao' => ['label' => 'Cognicao'],
        'coordenacao_motora' => ['label' => 'Coordenacao motora'],
        'humor' => ['label' => 'Humor'],
        'problemas_locomocao' => ['label' => 'Problemas de locomocao'],
        'medico' => ['label' => 'Medico'],
        'status' => ['label' => 'Status', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['completa' => 'Completa', 'pendente' => 'Pendente', 'em revisão' => 'Em revisao']],
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
