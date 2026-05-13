<?php

namespace App\Controllers;

class RelatorioPlantonController extends BaseController
{
    public function index(): void
    {
        require_once ROOT . '/mock_plantao.php';

        $this->view('relatorios/plantao/index', [
            'pageTitle' => 'Relatório de Plantão',
            'title' => 'Relatório de Plantão',
            'paciente' => $mockPaciente,
            'relatorios' => $mockRelatorios,
        ]);
    }

    public function show(string $turno): void
    {
        require_once ROOT . '/mock_plantao.php';

        if (!isset($mockRelatorios[$turno])) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Turno não encontrado.'], 'layouts/blank');
            return;
        }

        $this->view('relatorios/plantao/show', [
            'pageTitle' => 'Relatório - ' . $mockRelatorios[$turno]['label'],
            'title' => 'Relatório - ' . $mockRelatorios[$turno]['label'],
            'paciente' => $mockPaciente,
            'relatorios' => $mockRelatorios,
            'turnoAtual' => $turno,
            'relatorio' => $mockRelatorios[$turno],
        ]);
    }
}
