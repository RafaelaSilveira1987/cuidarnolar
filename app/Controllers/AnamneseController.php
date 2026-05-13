<?php

namespace App\Controllers;

use App\Models\Anamnese;

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
}
