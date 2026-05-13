<?php

namespace App\Controllers;

use App\Models\DiarioIdoso;

class DiarioIdosoController extends ResourceController
{
    protected string $modelClass = DiarioIdoso::class;
    protected string $routeBase = '/diario-idoso';
    protected string $viewTitle = 'Diario do Idoso';
    protected string $singularTitle = 'Registro do diario';
    protected array $columns = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'visita_mensal' => 'Visita',
        'pressao_arterial' => 'PA',
        'temperatura' => 'Temperatura',
        'dor' => 'Dor',
    ];
    protected array $detailFields = [
        'id' => '#',
        'paciente_nome' => 'Paciente',
        'visita_mensal' => 'Visita',
        'oxigenio' => 'Oxigenio',
        'frequencia_cardiaca' => 'Frequencia cardiaca',
        'temperatura' => 'Temperatura',
        'pressao_arterial' => 'Pressao arterial',
        'frequencia_respiratoria' => 'Frequencia respiratoria',
        'hgt' => 'HGT',
        'dor' => 'Dor',
        'observacao' => 'Observacao',
    ];
}
