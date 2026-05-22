<?php

namespace App\Services;

use App\Models\Escala;
use App\Models\EscalaOcorrencia;
use App\Models\EscalaProfissional;
use DateInterval;
use DatePeriod;
use DateTime;

class EscalaGenerator
{
    private Escala $escalaModel;
    private EscalaOcorrencia $ocorrenciaModel;
    private EscalaProfissional $profissionalModel;

    public function __construct()
    {
        $this->escalaModel = new Escala();
        $this->ocorrenciaModel = new EscalaOcorrencia();
        $this->profissionalModel = new EscalaProfissional();
    }

    public function gerarSemana(
        int $escalaBaseId,
        string $dataInicio,
        string $dataFim
    ): array {

        $escala = 
            $this->escalaModel->find($escalaBaseId);

        $tipoPlantao = $escala['tipo_plantao'] ?? '12h';
        $horaInicio  = $escala['hora_inicio'] ?? '00:00:00';
        $horaFim     = $escala['hora_fim'] ?? '00:00:00';

        if (!$escala) {
            throw new \Exception('Escala não encontrada.');
        }

        $profissionais =
            $this->escalaModel->profissionais($escalaBaseId);

        if (!$profissionais) {
            throw new \Exception('Nenhum profissional vinculado.');
        }

        /*
    |-----------------------------------
    | LISTA DE CUIDADORES
    |-----------------------------------
    */

        $cuidadores = array_column(
            $profissionais,
            'cuidador_id'
        );

        /*
    |-----------------------------------
    | PERÍODO
    |-----------------------------------
    */

        $periodo = new DatePeriod(
            new DateTime($dataInicio),
            new DateInterval('P1D'),
            (new DateTime($dataFim))->modify('+1 day')
        );

        $gerados = [];
        $conflitos = [];

        /*
    |-----------------------------------
    | LOOP DOS DIAS
    |-----------------------------------
    */

        foreach ($periodo as $index => $data) {

            $cuidadorId =
                $cuidadores[$index % count($cuidadores)];

            /*
        |-----------------------------------
        | DATAS
        |-----------------------------------
        */

            $inicio =
                $data->format('Y-m-d') .
                ' ' .
                $escala['hora_inicio'];

            $fimData = clone $data;

            if ($tipoPlantao === '24h') {
                $fimData->modify('+1 day');
            }

            $fim =
                $fimData->format('Y-m-d') .
                ' ' .
                $escala['hora_fim'];

            /*
        |-----------------------------------
        | CONFLITO
        |-----------------------------------
        */

            $conflito =
                $this->ocorrenciaModel->conflito(
                    $cuidadorId,
                    $inicio,
                    $fim
                );

            if ($conflito) {

                $conflitos[] = [
                    'data' => $data->format('Y-m-d'),
                    'cuidador_id' => $cuidadorId,
                ];

                continue;
            }

            /*
        |-----------------------------------
        | INSERT
        |-----------------------------------
        */

            $ocorrenciaId =
                $this->ocorrenciaModel->createRecord([
                    'escala_base_id' => $escala['id'],
                    'paciente_id'    => $escala['paciente_id'],
                    'cuidador_id'    => $cuidadorId,

                    'data_plantao'   => $data->format('Y-m-d'),

                    'inicio'         => $inicio,
                    'fim'            => $fim,

                    'status'         => 'previsto',
                    'observacoes'    => null,
                ]);

            /*
        |-----------------------------------
        | RETORNO
        |-----------------------------------
        */

            $gerados[] = [
                'ocorrencia_id' => $ocorrenciaId,
                'data'          => $data->format('Y-m-d'),
                'cuidador_id'   => $cuidadorId,
            ];
        }

        /*
    |-----------------------------------
    | FINAL
    |-----------------------------------
    */

        return [
            'success' => true,
            'gerados' => $gerados,
            'conflitos' => $conflitos,
        ];
    }


    private function montarDataHora(
        DateTime $dia,
        string $hora
    ): string {

        return $dia->format('Y-m-d') . ' ' . $hora;
    }

    private function montarFimPlantao(
        string $inicio,
        string $horaFim
    ): string {

        $inicioDate =
            new DateTime($inicio);

        $fimDate =
            new DateTime(
                $inicioDate->format('Y-m-d') . ' ' . $horaFim
            );

        if ($fimDate <= $inicioDate) {
            $fimDate->modify('+1 day');
        }

        return $fimDate->format('Y-m-d H:i:s');
    }

    private function diaAtivo(
        array $escala,
        string $diaSemana
    ): bool {

        $map = [
            'sunday' => 'domingo',
            'monday' => 'segunda',
            'tuesday' => 'terca',
            'wednesday' => 'quarta',
            'thursday' => 'quinta',
            'friday' => 'sexta',
            'saturday' => 'sabado',
        ];

        $campo =
            $map[$diaSemana] ?? null;

        if (!$campo) {
            return false;
        }

        return (bool) ($escala[$campo] ?? false);
    }
}