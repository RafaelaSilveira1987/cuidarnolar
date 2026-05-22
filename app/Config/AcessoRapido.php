<?php

return [
    'Relatório de Plantão' => [
        'label'       => 'Relatório de Plantão',
        'icon'        => '🗒️',
        'route'       => '/relatorio-plantao',
        'description' => 'Registros por paciente e data; acesso rápido para quem está nos cuidados.',
        'roles'       => ['enfermeiro', 'supervisor', 'admin'],
    ],

    'Gestão de Escalas' => [
        'label'       => 'Gestão de Escalas',
        'icon'        => '🗓️',
        'route'       => '/escala',
        'description' => 'Central de cobertura semanal — aloque, substitua e acompanhe plantões.',
        'roles'       => ['supervisor', 'admin'],
    ],
];