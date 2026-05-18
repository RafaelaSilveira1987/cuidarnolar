<?php

namespace App\Controllers;

use App\Models\Paciente;
use App\Models\Cuidador;
use App\Models\RelatorioPlantao;
use Ramsey\Uuid\Uuid;

class RelatorioPlantaoController extends BaseController
{
    // ─────────────────────────────────────────────────────────────────────────
    // index — lista de pacientes com relatórios
    // ─────────────────────────────────────────────────────────────────────────
    public function index(): void
    {
        $pacienteModel = new Paciente();

        $this->view('relatorio_plantao/index', [
            'pageTitle' => 'Relatórios de Plantão',
            'pacientes' => $pacienteModel->pacientesComRelatorio(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // paciente — timeline de plantões de um paciente (recebe int $id da rota)
    // ─────────────────────────────────────────────────────────────────────────
    public function paciente(int $id): void
    {
        $pacienteModel  = new Paciente();
        $relatorioModel = new RelatorioPlantao();

        $paciente = $pacienteModel->buscarPorId($id);

        if (!$paciente) {
            $_SESSION['error'] = 'Paciente não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        $paciente['uuid'] = $paciente['uuid'] ?? $relatorioModel->buscarUuidPorId($id);

        $cuidadorModel = new Cuidador();
        $cuidadoresLista = $cuidadorModel->all();

        $cuidadores = [];

        foreach ($cuidadoresLista as $c) {
            $cuidadores[$c['id']] = [
                'nome' => $c['nome_completo'] ?? $c['nome'] ?? '',
                'registro' => $c['registro'] ?? $c['coren'] ?? '',
            ];
        }

        // Busca todos os plantões do paciente ordenados por data DESC
        $plantoes = $relatorioModel->buscarPorPaciente($id);

        // Datas únicas disponíveis para o seletor
        $datasDisponiveis = array_unique(array_map(
            fn($p) => date('Y-m-d', strtotime($p['data_inicio'])),
            $plantoes
        ));
        sort($datasDisponiveis);

        // Data selecionada: GET ?data= ou a mais recente
        $dataSelecionada = $_GET['data'] ?? end($datasDisponiveis) ?: date('Y-m-d');

        // Filtra plantões do dia selecionado
        $this->view('relatorio_plantao/paciente', [
            'pageTitle'        => 'Plantões — ' . ($paciente['nome_completo'] ?? ''),
            'paciente'         => $paciente,
            'cuidadores' => $cuidadores,
            'plantoes'         => $plantoes, // envia TODOS
            'datasDisponiveis' => $datasDisponiveis,
            'dataSelecionada'  => $dataSelecionada,
            'totalPlantoes'    => count($plantoes),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // create — formulário de novo relatório
    // ─────────────────────────────────────────────────────────────────────────
    public function create(int $pacienteId = 0): void
    {
        $pacienteModel = new Paciente();
        $cuidadorModel = new Cuidador();

        $pacSelecionado = $pacienteId > 0
            ? $pacienteModel->buscarPorId($pacienteId)
            : null;

        $paciente = $this->normalizarPaciente($pacSelecionado, $pacienteModel, $pacienteId);

        $this->view('relatorio_plantao/create', [
            'pageTitle'           => 'Novo Relatório de Plantão',
            'paciente'            => $paciente,
            'pacienteSelecionado' => $pacSelecionado,
            'pacientes'           => $pacienteModel->all(),
            'cuidadores'          => $cuidadorModel->all(),
            'medicacoes'          => [],
            'relatorio'           => null,
            'turno_atual'         => 'plantao_24h',
            'enfermeiro'          => [
                'nome'  => $_SESSION['user']['nome']  ?? ($this->user['nome']  ?? ''),
                'coren' => $_SESSION['user']['coren'] ?? ($this->user['coren'] ?? ''),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // store — persiste o relatório
    // ─────────────────────────────────────────────────────────────────────────
    public function store(): void
    {
        $req = $_POST;

        $dataInicio = !empty($req['data_inicio'])
            ? str_replace('T', ' ', $req['data_inicio']) . ':00'
            : null;

        $dataFim = !empty($req['data_fim'])
            ? str_replace('T', ' ', $req['data_fim']) . ':00'
            : null;

        $uuid = Uuid::uuid4()->toString();

        $model = new RelatorioPlantao();

        $model->criarCompleto([
            'uuid'               => $uuid,
            'paciente_id'        => (int)($req['paciente_id']    ?? 0),
            'cuidador_id'        => (int)($req['cuidador_id']    ?? 0),
            'data_inicio'        => $dataInicio,
            'data_fim'           => $dataFim,
            'evolucao'           => trim($req['evolucao']         ?? ''),
            'status'             => 'finalizado',
            'assinado'           => 1,
            'pa'                 => trim($req['sv_pa']            ?? ''),
            'fc'                 => trim($req['sv_fc']            ?? ''),
            'temperatura'        => trim($req['sv_temp']          ?? ''),
            'spo2'               => trim($req['sv_spo2']          ?? ''),
            'hgt'                => trim($req['sv_hgt']           ?? ''),
            'observacao_sv'      => trim($req['observacao_sv']    ?? ''),
            'intercorrencias'    => $req['intercorrencias']       ?? [],
            'estado_paciente'    => trim($req['estado_paciente']  ?? ''),
            'alimentacao'        => trim($req['alimentacao']      ?? ''),
            'eliminacoes'        => trim($req['eliminacoes']      ?? ''),
            'medicacoes'         => $req['medicacoes']            ?? [],
            'observacoes_gerais' => trim($req['observacoes_gerais'] ?? ''),
            'consciencia'        => trim($req['consciencia']      ?? ''),
            'nivel_dor'          => (int)($req['nivel_dor']       ?? 0),
            'hidratacao_ml'      => (int)($req['hidratacao_ml']   ?? 0),
            'higiene'            => trim($req['higiene']          ?? ''),
            'sono'               => trim($req['sono']             ?? ''),
            'decubito'           => trim($req['decubito']         ?? ''),
        ]);

        $_SESSION['success'] = 'Relatório criado com sucesso!';
        header('Location: ' . BASE_URL . '/relatorio-plantao');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // helpers privados
    // ─────────────────────────────────────────────────────────────────────────
    private function normalizarPaciente(?array $raw, Paciente $model, int $id): array
    {
        if (!$raw) {
            return [
                'id'           => 0,
                'nome'         => '',
                'iniciais'     => '',
                'prontuario'   => '',
                'idade'        => 0,
                'diagnostico'  => '',
                'tem_diabetes' => false,
                'acamado'      => false,
            ];
        }

        $anamnese = [];
        if ($id > 0 && method_exists($model, 'buscarAnamnese')) {
            $anamnese = $model->buscarAnamnese($id) ?? [];
        }

        $nome = $raw['nome_completo'] ?? $raw['nome'] ?? '';

        return [
            'id'           => (int)($raw['id'] ?? 0),
            'nome'         => $nome,
            'iniciais'     => $this->iniciais($nome),
            'prontuario'   => (string)($raw['id'] ?? ''),
            'idade'        => $this->calcularIdade($raw['data_nascimento'] ?? ''),
            'diagnostico'  => $raw['diagnostico'] ?? '',
            'tem_diabetes' => ($anamnese['diabetes'] ?? '') === 'Sim'
                || !empty($anamnese['tem_diabetes']),
            'acamado'      => ($anamnese['acamado'] ?? '')  === 'Sim'
                || !empty($anamnese['acamado']),
        ];
    }

    private function iniciais(string $nome): string
    {
        $partes = array_filter(explode(' ', trim($nome)));
        $ini    = '';
        foreach (array_slice(array_values($partes), 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini ?: '?';
    }

    private function calcularIdade(string $dataNasc): int
    {
        if (!$dataNasc) return 0;
        try {
            return (int)(new \DateTime($dataNasc))->diff(new \DateTime())->y;
        } catch (\Exception $e) {
            return 0;
        }
    }
}