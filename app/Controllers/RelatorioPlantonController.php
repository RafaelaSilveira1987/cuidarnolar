<?php

namespace App\Controllers;

class RelatorioPlantonController extends BaseController
{
    public function index(): void
    {
        require_once BASE_PATH . '/mock_plantao.php';

        $this->view('relatorio_plantao/index', [
            'pageTitle' => 'Relatório de Plantão',
            'title' => 'Relatório de Plantão',
            'grupos' => $mockPlantaoGrupos,
        ]);
    }

    public function diario(string $id): void
    {
        require_once BASE_PATH . '/mock_plantao.php';

        $pacienteId = (int) $id;
        $grupo = null;
        foreach ($mockPlantaoGrupos as $g) {
            if ((int) $g['paciente_id'] === $pacienteId) {
                $grupo = $g;
                break;
            }
        }

        if ($grupo === null) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado nos relatórios de plantão.'], 'layouts/blank');
            return;
        }

        $dataConsulta = (string) $this->input('data', $grupo['data_plantao']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataConsulta)) {
            $dataConsulta = $grupo['data_plantao'];
        }

        $turno = (string) $this->input('turno', 'manha');
        $ordemTurnos = ['manha', 'tarde', 'noite'];
        if (!in_array($turno, $ordemTurnos, true)) {
            $turno = 'manha';
        }

        $turnosDoDia = ($dataConsulta === $grupo['data_plantao'])
            ? $grupo['turnos']
            : ['manha' => null, 'tarde' => null, 'noite' => null];

        $turnoData = $turnosDoDia[$turno] ?? null;

        $dt = new \DateTimeImmutable($dataConsulta);
        $dataAnterior = $dt->modify('-1 day')->format('Y-m-d');
        $dataProxima = $dt->modify('+1 day')->format('Y-m-d');

        $this->view('relatorio_plantao/diario', [
            'pageTitle' => 'Relatório de plantão — ' . ($grupo['paciente']['nome_completo'] ?? ''),
            'title' => 'Relatório de plantão',
            'paciente' => $grupo['paciente'],
            'paciente_id' => $pacienteId,
            'dataConsulta' => $dataConsulta,
            'dataLabelPt' => $this->formatarDataPt($dt),
            'dataAnterior' => $dataAnterior,
            'dataProxima' => $dataProxima,
            'turnoAtual' => $turno,
            'turnoData' => $turnoData,
            'turnosDoDia' => $turnosDoDia,
            'temDadosNoDia' => $dataConsulta === $grupo['data_plantao'],
        ]);
    }

    private function formatarDataPt(\DateTimeImmutable $dt): string
    {
        $meses = [
            1 => 'janeiro', 2 => 'fevereiro', 3 => 'março', 4 => 'abril',
            5 => 'maio', 6 => 'junho', 7 => 'julho', 8 => 'agosto',
            9 => 'setembro', 10 => 'outubro', 11 => 'novembro', 12 => 'dezembro',
        ];
        $m = (int) $dt->format('n');

        return $dt->format('d') . ' de ' . ($meses[$m] ?? $dt->format('m')) . ' de ' . $dt->format('Y');
    }
}
