<?php

/**
 * mock_plantao.php
 *
 * Dados de exemplo para Relatório de Plantão.
 * Períodos livres — sem trava de manhã/tarde/noite.
 * Substituir pelas queries reais ao integrar com o banco.
 *
 * Modelos suportados:
 *   24h     → 1 período
 *   12/12   → 2 períodos
 *   8/8/8   → 3 períodos  ← exemplo abaixo
 *   6/6/6/6 → 4 períodos
 */

function getMockPlantaoData(): array
{
    // Helper: calcula duração entre dois horários (suporta cruzar meia-noite)
    $dur = function (string $inicio, string $fim): string {
        [$hi, $mi] = array_map('intval', explode(':', $inicio));
        [$hf, $mf] = array_map('intval', explode(':', $fim));
        $totalMin  = ($hf * 60 + $mf) - ($hi * 60 + $mi);
        if ($totalMin <= 0) {
            $totalMin += 24 * 60;
        }
        $h = intdiv($totalMin, 60);
        $m = $totalMin % 60;
        return $m > 0 ? "{$h}h{$m}min" : "{$h}h";
    };

    $paciente = [
        'nome_completo'   => 'José da Silva Santos',
        'prontuario'      => '4821',
        'data_nascimento' => '1951-08-15',
        'idade'           => 74,
        'diagnostico'     => 'Pós-AVC Isquêmico',
        'iniciais'        => 'JS',
    ];

    $periodos = [

        // ── Período 1: 07h–15h (8h) ───────────────────────────────────
        [
            'hora_inicio'    => '07:00',
            'hora_fim'       => '15:00',
            'duracao_label'  => $dur('07:00', '15:00'),
            'enfermeiro'     => 'Ana Paula Costa',
            'coren'          => 'COREN-SP 512890',
            'status'         => 'concluido',
            'status_label'   => 'Concluído',
            'assinado'       => true,
            'sinais_vitais'  => [
                ['label' => 'PA',   'valor' => '132/84', 'unidade' => 'mmHg',  'status' => 'atencao', 'texto' => 'Limítrofe'],
                ['label' => 'FC',   'valor' => '78',     'unidade' => 'bpm',   'status' => 'normal',  'texto' => 'Normal'],
                ['label' => 'Temp', 'valor' => '36.8',   'unidade' => '°C',    'status' => 'normal',  'texto' => 'Afebril'],
                ['label' => 'SpO₂', 'valor' => '96',     'unidade' => '%',     'status' => 'normal',  'texto' => 'Normal'],
                ['label' => 'HGT',  'valor' => '142',    'unidade' => 'mg/dL', 'status' => 'atencao', 'texto' => 'Atenção'],
            ],
            'medicacoes'     => [
                ['nome' => 'Anlodipino 5mg', 'via' => 'VO', 'horario' => '08:00', 'status' => 'administrado'],
                ['nome' => 'AAS 100mg',      'via' => 'VO', 'horario' => '08:00', 'status' => 'administrado'],
                ['nome' => 'Atorvastatina',  'via' => 'VO', 'horario' => '12:00', 'status' => 'administrado'],
            ],
            'evolucao'       => "Paciente encontrado em bom estado geral, consciente e orientado em tempo e espaço.\nDieta oral aceita sem intercorrências. Sinais vitais estáveis — PA ligeiramente elevada às 08h, reavaliada às 10h com melhora (128/82 mmHg).\nHigiene e conforto realizados. Deambulação assistida no quarto sem queixas. Medicações administradas conforme prescrição médica.",
            'intercorrencias'=> [],
        ],

        // ── Período 2: 15h–23h (8h) ───────────────────────────────────
        [
            'hora_inicio'    => '15:00',
            'hora_fim'       => '23:00',
            'duracao_label'  => $dur('15:00', '23:00'),
            'enfermeiro'     => 'Carlos Mendes',
            'coren'          => 'COREN-SP 543210',
            'status'         => 'intercorrencia',
            'status_label'   => 'Intercorrência',
            'assinado'       => true,
            'sinais_vitais'  => [
                ['label' => 'PA',   'valor' => '148/96', 'unidade' => 'mmHg',  'status' => 'critico', 'texto' => 'Elevada'],
                ['label' => 'FC',   'valor' => '92',     'unidade' => 'bpm',   'status' => 'atencao', 'texto' => 'Taquicardia leve'],
                ['label' => 'Temp', 'valor' => '37.9',   'unidade' => '°C',    'status' => 'atencao', 'texto' => 'Febrícula'],
                ['label' => 'SpO₂', 'valor' => '94',     'unidade' => '%',     'status' => 'critico', 'texto' => 'Atenção'],
                ['label' => 'HGT',  'valor' => '189',    'unidade' => 'mg/dL', 'status' => 'critico', 'texto' => 'Elevado'],
            ],
            'medicacoes'     => [
                ['nome' => 'Captopril 25mg SL', 'via' => 'SL', 'horario' => '15:25', 'status' => 'administrado'],
                ['nome' => 'Anlodipino 5mg',    'via' => 'VO', 'horario' => '16:00', 'status' => 'administrado'],
                ['nome' => 'Insulina Regular',  'via' => 'SC', 'horario' => '17:00', 'status' => 'administrado'],
                ['nome' => 'Dipirona 500mg',    'via' => 'VO', 'horario' => '18:00', 'status' => 'administrado'],
            ],
            'evolucao'       => "Paciente apresentou pico hipertensivo às 15h20 (148/96). Médico responsável acionado — conduta: Captopril 25mg SL.\nReavaliação em 30 min com PA 135/88. Glicemia elevada às 17h, insulina regular aplicada conforme protocolo.\nFebre baixa às 18h, dipirona administrada com resolução. Familiar notificado sobre as intercorrências.",
            'intercorrencias'=> [
                ['descricao' => 'Pico hipertensivo — PA 148/96 mmHg. Médico acionado. Conduta: Captopril 25mg SL. Monitoramento reforçado a cada 30 min.', 'horario' => '15:20'],
                ['descricao' => 'Glicemia capilar 189 mg/dL. Insulina regular aplicada conforme protocolo. Reavaliação em 2h: 154 mg/dL.', 'horario' => '17:00'],
            ],
        ],

        // ── Período 3: 23h–07h (8h, cruza meia-noite) ────────────────
        [
            'hora_inicio'    => '23:00',
            'hora_fim'       => '07:00',
            'duracao_label'  => $dur('23:00', '07:00'),
            'enfermeiro'     => 'Fernanda Lima',
            'coren'          => 'COREN-SP 498321',
            'status'         => 'andamento',
            'status_label'   => 'Em andamento',
            'assinado'       => false,
            'sinais_vitais'  => [
                ['label' => 'PA',   'valor' => '136/88', 'unidade' => 'mmHg',  'status' => 'atencao', 'texto' => 'Limítrofe'],
                ['label' => 'FC',   'valor' => '76',     'unidade' => 'bpm',   'status' => 'normal',  'texto' => 'Normal'],
                ['label' => 'Temp', 'valor' => '37.1',   'unidade' => '°C',    'status' => 'normal',  'texto' => 'Afebril'],
                ['label' => 'SpO₂', 'valor' => '95',     'unidade' => '%',     'status' => 'normal',  'texto' => 'Normal'],
                ['label' => 'HGT',  'valor' => '158',    'unidade' => 'mg/dL', 'status' => 'atencao', 'texto' => 'Atenção'],
            ],
            'medicacoes'     => [
                ['nome' => 'Omeprazol 20mg', 'via' => 'VO', 'horario' => '23:30', 'status' => 'administrado'],
                ['nome' => 'Insulina NPH',   'via' => 'SC', 'horario' => '00:00', 'status' => 'administrado'],
                ['nome' => 'AAS 100mg',      'via' => 'VO', 'horario' => '06:00', 'status' => 'pendente'],
                ['nome' => 'Anlodipino 5mg', 'via' => 'VO', 'horario' => '06:00', 'status' => 'pendente'],
            ],
            'evolucao'       => "Paciente recebido em repouso no leito, consciente e orientado, com queixa de leve cefaleia residual.\nPA controlada após intercorrências do período anterior. Hidratação oral mantida.\nPaciente dormiu às 00h15, sono tranquilo. Monitoramento de glicemia e PA a cada 2h conforme orientação médica.\nPendências para o próximo período: AAS e Anlodipino às 06h.",
            'intercorrencias'=> [],
        ],

    ];

    return [
        [
            'paciente_id'  => 1,
            'data_plantao' => '2026-05-13',
            'paciente'     => $paciente,
            'periodos'     => $periodos,
        ],
    ];
}