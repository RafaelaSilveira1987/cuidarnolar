<?php

return [
    'Diário do Paciente' => [
        'label' => 'Diário do Paciente',
        'icon' => '📋',
        'route' => '/diario-paciente',
        'description' => 'Visualize os relatórios de plantão agrupados por paciente e turno',
        'roles' => ['enfermeiro', 'supervisor', 'admin'],
    ],
    'Relatório de Plantão' => [
        'label' => 'Relatório de Plantão',
        'icon' => '📝',
        'route' => '/relatorio-plantao',
        'description' => 'Gerencie todos os relatórios de plantão',
        'roles' => ['enfermeiro', 'supervisor', 'admin'],
    ],
];
