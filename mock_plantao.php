<?php

/**
 * Mock data for Relatório de Plantão
 * In production, replace with database queries via RelatorioPlantion model
 */

$mockPaciente = [
    'nome'       => 'José da Silva Santos',
    'prontuario' => '4821',
    'idade'      => 74,
    'diagnostico'=> 'Pós-AVC Isquêmico',
    'iniciais'   => 'JS',
];

$mockRelatorios = [
    'manha' => [
        'turno'          => 'manha',
        'label'          => 'Manhã',
        'horario'        => '07:00 – 13:00',
        'icone'          => '☀️',
        'enfermeiro'     => 'Ana Paula Costa',
        'coren'          => 'COREN-SP 512890',
        'status'         => 'concluido',
        'status_label'   => 'Concluído',
        'assinado'       => true,
        'sinais_vitais'  => [
            ['label' => 'PA',     'valor' => '132/84',  'unidade' => 'mmHg',  'status' => 'atencao',  'texto' => 'Limítrofe'],
            ['label' => 'FC',     'valor' => '78',      'unidade' => 'bpm',   'status' => 'normal',   'texto' => 'Normal'],
            ['label' => 'Temp',   'valor' => '36.8',    'unidade' => '°C',    'status' => 'normal',   'texto' => 'Afebril'],
            ['label' => 'SpO₂',   'valor' => '96',      'unidade' => '%',     'status' => 'normal',   'texto' => 'Normal'],
            ['label' => 'HGT',    'valor' => '142',     'unidade' => 'mg/dL', 'status' => 'atencao',  'texto' => 'Atenção'],
        ],
        'medicacoes'     => [
            ['nome' => 'Anlodipino 5mg',  'via' => 'VO', 'horario' => '08:00', 'status' => 'administrado'],
            ['nome' => 'AAS 100mg',       'via' => 'VO', 'horario' => '08:00', 'status' => 'administrado'],
            ['nome' => 'Atorvastatina',   'via' => 'VO', 'horario' => '12:00', 'status' => 'administrado'],
        ],
        'evolucao'       => 'Paciente encontrado em bom estado geral, consciente e orientado em tempo e espaço. Dieta oral aceita sem intercorrências. Sinais vitais estáveis no período, PA ligeiramente elevada às 08h, monitorada com nova aferição às 10h com melhora (128/82 mmHg). Higiene e conforto realizados. Deambulação assistida no quarto sem queixas. Medicações administradas conforme prescrição médica. Sem intercorrências no turno.',
        'intercorrencias'=> [],
    ],

    'tarde' => [
        'turno'          => 'tarde',
        'label'          => 'Tarde',
        'horario'        => '13:00 – 19:00',
        'icone'          => '🌤️',
        'enfermeiro'     => 'Carlos Mendes',
        'coren'          => 'COREN-SP 543210',
        'status'         => 'intercorrencia',
        'status_label'   => 'Intercorrência',
        'assinado'       => true,
        'sinais_vitais'  => [
            ['label' => 'PA',     'valor' => '148/96',  'unidade' => 'mmHg',  'status' => 'critico',  'texto' => 'Elevada'],
            ['label' => 'FC',     'valor' => '92',      'unidade' => 'bpm',   'status' => 'atencao',  'texto' => 'Taquicardia leve'],
            ['label' => 'Temp',   'valor' => '37.9',    'unidade' => '°C',    'status' => 'atencao',  'texto' => 'Febrícula'],
            ['label' => 'SpO₂',   'valor' => '94',      'unidade' => '%',     'status' => 'critico',  'texto' => 'Atenção'],
            ['label' => 'HGT',    'valor' => '189',     'unidade' => 'mg/dL', 'status' => 'critico',  'texto' => 'Elevado'],
        ],
        'medicacoes'     => [
            ['nome' => 'Anlodipino 5mg',     'via' => 'VO', 'horario' => '14:00', 'status' => 'administrado'],
            ['nome' => 'Insulina Regular',   'via' => 'SC', 'horario' => '16:00', 'status' => 'administrado'],
            ['nome' => 'Dipirona 500mg',     'via' => 'VO', 'horario' => '15:30', 'status' => 'administrado'],
            ['nome' => 'Captopril 25mg SL',  'via' => 'SL', 'horario' => '14:25', 'status' => 'administrado'],
        ],
        'evolucao'       => 'Paciente apresentou pico hipertensivo às 14h20 (148/96). Médico responsável acionado, conduta: Captopril 25mg SL. Reavaliação em 30 minutos com PA 135x88. Glicemia elevada às 15h — insulina regular aplicada conforme protocolo. Febre baixa às 16h, dipirona administrada com resolução. Familiar notificado sobre intercorrências.',
        'intercorrencias'=> [
            ['descricao' => 'Pico hipertensivo — PA 148/96 mmHg. Médico acionado, conduta: Captopril 25mg SL. Monitoramento reforçado.', 'horario' => '14:20'],
            ['descricao' => 'Glicemia capilar 189 mg/dL. Insulina regular aplicada conforme protocolo. Reavaliação em 2h.', 'horario' => '15:00'],
        ],
    ],

    'noite' => [
        'turno'          => 'noite',
        'label'          => 'Noite',
        'horario'        => '19:00 – 07:00',
        'icone'          => '🌙',
        'enfermeiro'     => 'Fernanda Lima',
        'coren'          => 'COREN-SP 498321',
        'status'         => 'andamento',
        'status_label'   => 'Em andamento',
        'assinado'       => false,
        'sinais_vitais'  => [
            ['label' => 'PA',     'valor' => '136/88',  'unidade' => 'mmHg',  'status' => 'atencao',  'texto' => 'Limítrofe'],
            ['label' => 'FC',     'valor' => '80',      'unidade' => 'bpm',   'status' => 'normal',   'texto' => 'Normal'],
            ['label' => 'Temp',   'valor' => '37.1',    'unidade' => '°C',    'status' => 'normal',   'texto' => 'Afebril'],
            ['label' => 'SpO₂',   'valor' => '95',      'unidade' => '%',     'status' => 'normal',   'texto' => 'Normal'],
            ['label' => 'HGT',    'valor' => '158',     'unidade' => 'mg/dL', 'status' => 'atencao',  'texto' => 'Atenção'],
        ],
        'medicacoes'     => [
            ['nome' => 'Anlodipino 5mg',  'via' => 'VO', 'horario' => '20:00', 'status' => 'administrado'],
            ['nome' => 'Omeprazol 20mg',  'via' => 'VO', 'horario' => '22:00', 'status' => 'administrado'],
            ['nome' => 'Insulina NPH',    'via' => 'SC', 'horario' => '22:00', 'status' => 'pendente'],
            ['nome' => 'AAS 100mg',       'via' => 'VO', 'horario' => '06:00', 'status' => 'pendente'],
        ],
        'evolucao'       => 'Paciente recebido do turno da tarde em repouso no leito. Consciente, orientado, queixando-se de leve cefaleia. PA controlada após intercorrências do turno anterior. Hidratação oral mantida. Paciente dormiu às 22h30, sono tranquilo. Monitoramento de glicemia e PA a cada 2h conforme orientação médica.',
        'intercorrencias'=> [],
    ],
];
