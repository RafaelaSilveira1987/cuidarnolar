<?php

namespace App\Controllers;

use App\Models\DiarioIdoso;

class DiarioIdosoController extends ResourceController
{
    protected string $modelClass = DiarioIdoso::class;
    protected string $routeBase = '/diario-paciente';
    protected string $viewTitle = 'Diario do Paciente';
    protected string $singularTitle = 'Registro do diario do paciente';
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
    protected array $requiredFields = ['paciente_id'];
    protected array $formFields = [
        'paciente_id' => ['label' => 'Paciente', 'type' => 'select', 'empty' => 'Selecione'],
        'visita_mensal' => ['label' => 'Visita mensal', 'type' => 'datetime-local'],
        'oxigenio' => ['label' => 'Oxigenio', 'type' => 'number'],
        'frequencia_cardiaca' => ['label' => 'Frequencia cardiaca', 'type' => 'number'],
        'temperatura' => ['label' => 'Temperatura', 'type' => 'number'],
        'pressao_arterial' => ['label' => 'Pressao arterial'],
        'frequencia_respiratoria' => ['label' => 'Frequencia respiratoria', 'type' => 'number'],
        'hgt' => ['label' => 'HGT', 'type' => 'number'],
        'dor' => ['label' => 'Dor', 'type' => 'number'],
        'peso' => ['label' => 'Peso', 'type' => 'number'],
        'altura' => ['label' => 'Altura', 'type' => 'number'],
        'observacao' => ['label' => 'Observacao', 'type' => 'textarea', 'span' => true],
    ];
}
