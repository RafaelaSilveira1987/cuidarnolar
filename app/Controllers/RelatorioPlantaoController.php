<?php

namespace App\Controllers;

use App\Models\Paciente;
use App\Models\Cuidador;
use App\Models\RelatorioPlantao;

class RelatorioPlantaoController extends BaseController
{
    public function index(): void
    {
        $pacienteModel = new Paciente();

        $pacientes = $pacienteModel->pacientesComRelatorio();

        $this->view('relatorio_plantao/index', [
            'pageTitle' => 'Relatórios de Plantão',
            'pacientes' => $pacientes,
        ]);
    }

    public function paciente(int $id): void
    {
        $pacienteModel = new Paciente();
        $relatorioModel = new RelatorioPlantao();

        $paciente = $pacienteModel->buscarPorId($id);

        $plantoes = $relatorioModel->buscarPorPaciente($id);

        $this->view('relatorio_plantao/paciente', [
            'pageTitle' => 'Relatório do Paciente',
            'paciente'  => $paciente,
            'plantoes'  => $plantoes
        ]);
    }

    public function create($pacienteId = null): void
    {
        $pacienteModel = new Paciente();
        $cuidadorModel = new Cuidador();

        $pacienteSelecionado = $pacienteId
            ? $pacienteModel->buscarPorId((int)$pacienteId)
            : null;

        $this->view('relatorio_plantao/create', [
            'pageTitle' => 'Novo Relatório',
            'pacienteSelecionado' => $pacienteSelecionado,
            'pacientes' => $pacienteModel->all(),
            'cuidadores' => $cuidadorModel->all()
        ]);
    }

    public function store(): void
    {
        $request = $_POST;

        $dataInicio = !empty($_POST['data_inicio'])
            ? str_replace('T', ' ', $_POST['data_inicio']) . ':00'
            : null;

        $dataFim = !empty($_POST['data_fim'])
            ? str_replace('T', ' ', $_POST['data_fim']) . ':00'
            : null;

        $pacienteId = (int)($request['paciente_id'] ?? 0);
        $cuidadorId = (int)($request['cuidador_id'] ?? 0);

        $evolucao = trim($request['evolucao'] ?? '');

        $model = new RelatorioPlantao();

        $model->criarCompleto([

            'paciente_id' => $pacienteId,
            'cuidador_id' => $cuidadorId,

            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,

            'evolucao' => $evolucao,

            'status' => 'finalizado',
            'assinado' => 1,

            'pa' => trim($request['sv_pa'] ?? ''),
            'fc' => trim($request['sv_fc'] ?? ''),
            'temperatura' => trim($request['sv_temp'] ?? ''),
            'spo2' => trim($request['sv_spo2'] ?? ''),
            'hgt' => trim($request['sv_hgt'] ?? ''),

            'observacao_sv' => trim($request['observacao_sv'] ?? ''),

            'intercorrencias' => $request['intercorrencias'] ?? [],
        ]);

        $_SESSION['success'] = 'Relatório criado com sucesso!';

        header('Location: ' . BASE_URL . '/relatorio-plantao');
        exit;
    }
}