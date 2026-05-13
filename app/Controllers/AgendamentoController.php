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
}
