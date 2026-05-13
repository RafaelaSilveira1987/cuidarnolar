<?php

namespace App\Controllers;

use App\Models\Historico;

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
}
