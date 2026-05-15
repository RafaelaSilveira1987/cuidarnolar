<?php

namespace App\Controllers;

use App\Models\PlantaoModel;

class RelatorioPlantaoController extends ResourceController
{
    private $plantaoModel;

    public function __construct()
    {
        $this->plantaoModel = new PlantaoModel();
    }

    public function index(): void
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $pacienteId = $_GET['paciente'] ?? 1;
        $pacientes = $this->plantaoModel->listarPacientes();
        $pacienteSelecionado = null;

        if (count($pacientes) > 1) {
            foreach ($pacientes as $paciente) {
                if ($paciente['id'] == $pacienteId) {
                    $pacienteSelecionado = $paciente;
                    break;
                }
            }
        } else {
            $pacienteSelecionado = $pacientes[0] ?? null;
        }

        if (!$pacienteSelecionado && !empty($pacientes)) {
            $pacienteSelecionado = $pacientes[0];
        }

        if (!$pacienteSelecionado) {
            if ($isAjax) {
                $this->json(['success' => false, 'message' => 'Paciente não encontrado'], 404);
                return;
            }
            $this->view('relatorio_plantao/index', [
                'pacientes'           => $pacientes,
                'temDados'            => false,
                'pacienteSelecionado' => null,
            ]);
            return;
        }

        $plantoes = $this->plantaoModel->listarPorPaciente($pacienteSelecionado['id']);

        foreach ($plantoes as &$plantao) {
            $dadosPlantao = $this->plantaoModel->buscarPlantaoCompleto($plantao['id']);
            $plantao = array_merge($plantao, $dadosPlantao);

            $plantao['sinais_vitais'] = !empty($plantao['sinais_vitais_json'])
                ? json_decode($plantao['sinais_vitais_json'], true) : [];

            $plantao['medicacoes'] = !empty($plantao['medicacoes_json'])
                ? json_decode($plantao['medicacoes_json'], true) : [];

            $plantao['evolucao'] = $plantao['evolucao_texto'] ?? '';

            $plantao['intercorrencias'] = !empty($plantao['intercorrencias_json'])
                ? json_decode($plantao['intercorrencias_json'], true) : [];

            $partes = explode(' ', $plantao['enfermeiro'] ?? 'N/A');
            $plantao['iniciais'] = strtoupper(($partes[0][0] ?? '') . ($partes[1][0] ?? ''));
        }

        if ($isAjax) {
            $this->json([
                'success'             => true,
                'pacientes'           => $pacientes,
                'temDados'            => !empty($pacientes[0]['plantoes'] ?? []),
                'pacienteSelecionado' => $pacienteSelecionado,
            ]);
            return;
        }

        $this->view('relatorio_plantao/index', [
            'pacientes'           => $pacientes,
            'temDados'            => !empty($pacientes[0]['plantoes'] ?? []),
            'pacienteSelecionado' => $pacienteSelecionado,
        ]);
    }

    public function assinar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $plantaoId = $data['plantao_id'] ?? 0;

        if (!$plantaoId) {
            $this->json(['success' => false, 'message' => 'ID do plantão não informado'], 400);
            return;
        }

        $enfermeiroId = $_SESSION['user_id'] ?? 1;
        $success = $this->plantaoModel->assinarPlantao($plantaoId, $enfermeiroId);

        if ($success) {
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false, 'message' => 'Erro ao assinar plantão'], 500);
        }
    }

    
}