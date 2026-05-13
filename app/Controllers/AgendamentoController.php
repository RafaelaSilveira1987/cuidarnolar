<?php

namespace App\Controllers;

use App\Models\Evento;

class AgendamentoController extends ResourceController
{
    protected string $modelClass = Evento::class;
    protected string $routeBase = '/agendamentos';
    protected string $viewTitle = 'Agendamentos';
    protected string $singularTitle = 'Agendamento';
    protected array $columns = [
        'id' => '#',
        'titulo' => 'Titulo',
        'data_evento' => 'Data',
        'paciente_nome' => 'Paciente',
        'cuidador_nome' => 'Cuidador',
        'status' => 'Status',
    ];
    protected array $detailFields = [
        'id' => '#',
        'titulo' => 'Titulo',
        'data_evento' => 'Data',
        'paciente_nome' => 'Paciente',
        'cuidador_nome' => 'Cuidador',
        'descricao' => 'Descricao',
        'status' => 'Status',
    ];
    protected array $requiredFields = ['paciente_id', 'titulo', 'data_evento'];
    protected array $formFields = [
        'paciente_id' => ['label' => 'Paciente', 'type' => 'select', 'empty' => 'Selecione'],
        'cuidador_id' => ['label' => 'Cuidador', 'type' => 'select', 'empty' => 'Sem cuidador'],
        'titulo' => ['label' => 'Titulo', 'span' => true, 'maxlength' => 255],
        'data_evento' => ['label' => 'Data e hora', 'type' => 'datetime-local'],
        'status' => ['label' => 'Status', 'type' => 'select', 'empty' => 'Selecione', 'options' => ['Pendente' => 'Pendente', 'Concluído' => 'Concluido']],
        'descricao' => ['label' => 'Descricao', 'type' => 'textarea', 'span' => true],
    ];
}
