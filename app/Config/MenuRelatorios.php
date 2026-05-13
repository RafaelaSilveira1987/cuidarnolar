<?php

return [
    'Relatório de Plantão' => [
        'label' => 'Relatório de Plantão',
        'icon' => '📝',
        'route' => '/relatorio-plantao',
        'description' => 'Relatórios de plantão agrupados por paciente e turno (manhã, tarde e noite)',
        'roles' => ['enfermeiro', 'supervisor', 'admin'],
    ],
];
