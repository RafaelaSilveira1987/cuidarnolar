<?php

return [
    'Relatório de Plantão' => [
        'label' => 'Relatório de Plantão',
        'icon' => '',
        'route' => '/relatorio-plantao',
        'description' => 'Registros por paciente e data; acesso rápido para quem está nos cuidados (vários relatórios no mesmo dia em evolução).',
        'roles' => ['enfermeiro', 'supervisor', 'admin'],
    ],
];