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

        $this->view('relatorio_plantao/index', [
            'pageTitle' => 'Relatórios de Plantão',
            'pacientes' => $pacienteModel->pacientesComRelatorio(),
        ]);
    }

    public function paciente(string $uuid): void
    {
        $pacienteModel  = new Paciente();
        $relatorioModel = new RelatorioPlantao();

        // Busca pelo UUID em vez do ID numérico
        $paciente = $pacienteModel->buscarPorUuid($uuid);

        if (!$paciente) {
            $_SESSION['error'] = 'Paciente não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        $id = (int)$paciente['id'];

        $cuidadorModel   = new Cuidador();
        $cuidadoresLista = $cuidadorModel->all();

        $cuidadores = [];
        foreach ($cuidadoresLista as $c) {
            $cuidadores[$c['id']] = [
                'nome'     => $c['nome_completo'] ?? $c['nome'] ?? '',
                'registro' => $c['registro'] ?? $c['coren'] ?? '',
            ];
        }

        $plantoes = $relatorioModel->buscarPorPaciente($id);

        $this->view('relatorio_plantao/paciente', [
            'pageTitle'     => 'Plantões — ' . ($paciente['nome_completo'] ?? $paciente['nome'] ?? ''),
            'paciente'      => $paciente,
            'cuidadores'    => $cuidadores,
            'plantoes'      => $plantoes,
            'totalPlantoes' => count($plantoes),
        ]);
    }

    public function create(string $pacienteUuid = ''): void
    {
        $pacienteModel = new Paciente();
        $cuidadorModel = new Cuidador();
        $relatorioModel = new RelatorioPlantao();

        $pacSelecionado = $pacienteUuid !== ''
            ? $pacienteModel->buscarPorUuid($pacienteUuid)
            : null;

        $pacienteId = (int)($pacSelecionado['id'] ?? 0);
        $paciente   = $this->normalizarPaciente($pacSelecionado, $pacienteModel, $pacienteId);

        $contexto = [];
        if ($pacienteId > 0) {
            $contexto = $relatorioModel->buscarContextoPaciente($pacienteId);
        }

        $this->view('relatorio_plantao/create', [
            'pageTitle'         => 'Novo Relatório de Plantão',
            'paciente'          => $paciente,
            'pacienteSelecionado' => $pacSelecionado,
            'pacientes'         => $pacienteModel->all(),
            'cuidadores'        => $cuidadorModel->all(),
            'medicacoes'        => $contexto['medicamentos'] ?? [],
            'relatorio'         => null,
            'turno_atual'       => 'plantao_24h',
            'enfermeiro'        => [
                'nome'  => $_SESSION['user']['nome']  ?? ($this->user['nome']  ?? ''),
                'coren' => $_SESSION['user']['coren'] ?? ($this->user['coren'] ?? ''),
            ],
            'dispositivos_paciente' => $contexto['dispositivos'] ?? [],
            'anamnese'              => $contexto['anamnese']     ?? [],
        ]);
    }

    public function store(string $pacienteUuid): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        $pacienteModel = new Paciente();
        $relatorioModel = new RelatorioPlantao();

        $paciente = $pacienteModel->buscarPorUuid($pacienteUuid);

        if (!$paciente) {
            throw new \RuntimeException('Paciente não encontrado.');
        }

        $dados = $this->normalizarPayload($_POST);
        $dados['paciente_id'] = (int)$paciente['id'];
        $dados['status'] = ($_POST['acao'] ?? '') === 'assinar' ? 'finalizado' : 'rascunho';
        $dados['assinado'] = ($_POST['acao'] ?? '') === 'assinar' ? 1 : 0;

        try {
            $relatorioId = $relatorioModel->criarCompleto($dados);

            $novoRelatorio = $relatorioModel->buscarPorIdCompleto($relatorioId);

            if ($novoRelatorio && !empty($novoRelatorio['uuid'])) {
                $this->redirect(
                    BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($novoRelatorio['uuid'])
                );
            }

            $this->redirect(
                BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid)
            );
        } catch (\Throwable $e) {
            error_log($e->getMessage());

            $this->flash('error', 'Erro ao salvar relatório.');

            $this->redirect(
                BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid) . '/novo'
            );
        }

        // Após criar o relatório, salva medicações:
        $medicacoes = $this->extrairMedicacoesPost($_POST['medicacoes'] ?? []);
        $relatorioModel->salvarMedicacoesPlantao($relatorioId, $medicacoes);
    }

    private function extrairMedicacoesPost(array $raw): array
    {
        $resultado = [];
        foreach ($raw as $item) {
            if (empty($item['medicamento'])) continue;
            $resultado[] = [
                'medicacao_paciente_id' => $item['medicacao_paciente_id'] ?? null,
                'medicamento'           => trim($item['medicamento']),
                'via'                   => trim($item['via']     ?? ''),
                'horario'               => trim($item['horario'] ?? ''),
                'status'                => $item['status']    ?? 'pendente',
                'observacao'            => trim($item['observacao'] ?? ''),
            ];
        }
        return $resultado;
    }

    public function edit(string $uuid): void
    {
        $model         = new RelatorioPlantao();
        $pacienteModel = new Paciente();
        $cuidadorModel = new Cuidador();

        // Buscar relatório pelo UUID
        $relatorio = $model->buscarPorUuid($uuid);

        if (!$relatorio) {
            $_SESSION['error'] = 'Relatório não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        // Buscar paciente vinculado
        $pacienteId     = (int)($relatorio['paciente_id'] ?? 0);
        $pacSelecionado = $pacienteId > 0
            ? $pacienteModel->buscarPorId($pacienteId)
            : null;

        $paciente = $this->normalizarPaciente(
            $pacSelecionado,
            $pacienteModel,
            $pacienteId
        );
        $contexto = $pacienteId > 0
            ? $model->buscarContextoPaciente($pacienteId)
            : [];

        // Renderizar o formulário profissional (novo layout ERP)
        $this->view('relatorio_plantao/edit', [
            'pageTitle'           => 'Editar Relatório de Plantão',
            'paciente'            => $paciente,
            'pacienteSelecionado' => $pacSelecionado,
            'pacientes'           => $pacienteModel->all(),
            'cuidadores'          => $cuidadorModel->all(),
            'medicacoes'          => $contexto['medicamentos'] ?? [],
            'relatorio'           => $relatorio,
            'turno_atual'         => 'plantao_24h',
            'enfermeiro'          => [
                'nome'  => $_SESSION['user']['nome']
                    ?? ($this->user['nome'] ?? ''),
                'coren' => $_SESSION['user']['coren']
                    ?? ($this->user['coren'] ?? ''),
            ],
        ]);
    }

    public function update(string $uuid): void
    {
        // Permite apenas POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        // Instancia o model
        $relatorioModel = new RelatorioPlantao();

        // Busca o relatório atual
        $relatorio = $relatorioModel->buscarPorUuid($uuid);

        if (!$relatorio) {
            throw new \RuntimeException('Relatório não encontrado.');
        }

        /**
         * ==========================================================
         * MAPEAMENTO DOS CAMPOS DO FORMULÁRIO
         * ==========================================================
         * O form.php envia:
         * - pa
         * - fc
         * - temperatura
         * - spo2
         * - hgt
         * - evolucao_enfermagem
         * - intercorrencias
         * - medicacoes
         * - observacoes
         * - data_inicio
         * - data_fim
         * - status
         *
         * O banco utiliza:
         * - pa
         * - fc
         * - temperatura
         * - spo2
         * - hgt
         * - evolucao
         * - intercorrencias
         * - medicacoes
         * - observacoes_gerais
         * - data_inicio
         * - data_fim
         * - status
         */

        $dados = [
            // Mantém vínculo com paciente
            'paciente_id' => (int)($relatorio['paciente_id'] ?? 0),
            'cuidador_id' => (int)($_POST['cuidador_id'] ?? ($relatorio['cuidador_id'] ?? 0)),
            'assinado' => ($_POST['acao'] ?? '') === 'assinar' ? 1 : (int)($relatorio['assinado'] ?? 0),

            // Datas
            'data_inicio' => !empty($_POST['data_inicio'])
                ? str_replace('T', ' ', $_POST['data_inicio']) . ':00'
                : ($relatorio['data_inicio'] ?? null),

            'data_fim' => !empty($_POST['data_fim'])
                ? str_replace('T', ' ', $_POST['data_fim']) . ':00'
                : null,

            // Status
            'status' => ($_POST['acao'] ?? '') === 'assinar' ? 'finalizado' : trim((string)($_POST['status'] ?? 'rascunho')),

            // Sinais vitais
            'pa' => trim((string)($_POST['pa'] ?? '')),
            'fc' => trim((string)($_POST['fc'] ?? '')),
            'temperatura' => trim((string)($_POST['temperatura'] ?? '')),
            'spo2' => trim((string)($_POST['spo2'] ?? '')),
            'hgt' => trim((string)($_POST['hgt'] ?? '')),

            // Evolução de enfermagem
            'evolucao' => trim((string)(
                $_POST['evolucao_enfermagem']
                ?? $_POST['evolucao']
                ?? ''
            )),

            // Intercorrências
            'intercorrencias' => !empty($_POST['sem_intercorrencias'])
                ? []
                : ($_POST['intercorrencias'] ?? ($relatorio['intercorrencias'] ?? [])),

            // Medicações
            'medicacoes' => $_POST['medicacoes'] ?? ($relatorio['medicacoes'] ?? []),

            // Observações gerais
            'observacoes_gerais' => trim((string)(
                $_POST['observacoes']
                ?? $_POST['observacoes_gerais']
                ?? ''
            )),

            // Campos adicionais (preservados se existirem)
            'estado_paciente' => trim((string)(
                $_POST['estado_paciente']
                ?? ($relatorio['estado_paciente'] ?? '')
            )),

            'alimentacao' => trim((string)(
                $_POST['alimentacao']
                ?? ($relatorio['alimentacao'] ?? '')
            )),

            'eliminacoes' => $_POST['eliminacoes'] ?? ($relatorio['eliminacoes'] ?? ''),

            'consciencia' => trim((string)(
                $_POST['consciencia']
                ?? ($relatorio['consciencia'] ?? '')
            )),

            'nivel_dor' => (int)(
                $_POST['nivel_dor']
                ?? ($relatorio['nivel_dor'] ?? 0)
            ),

            'hidratacao_ml' => (int)(
                $_POST['hidratacao_ml']
                ?? ($relatorio['hidratacao_ml'] ?? 0)
            ),

            'higiene' => trim((string)(
                $_POST['higiene']
                ?? ($relatorio['higiene'] ?? '')
            )),

            'sono' => trim((string)(
                $_POST['sono']
                ?? ($relatorio['sono'] ?? '')
            )),

            'decubito' => $_POST['decubito'] ?? ($relatorio['decubito'] ?? ''),

            'observacao_sv' => trim((string)(
                $_POST['observacao_sv']
                ?? ($relatorio['observacao_sv'] ?? '')
            )),
        ];

        try {
            // Atualiza no banco
            $relatorioModel->atualizarCompleto(
                (int)$relatorio['id'],
                $dados
            );

            // Sucesso
            $_SESSION['success'] = 'Relatório atualizado com sucesso.';

            // Redireciona para visualização
            header(
                'Location: ' .
                    BASE_URL .
                    '/relatorio-plantao/plantao/' .
                    rawurlencode($uuid)
            );
            exit;
        } catch (\Throwable $e) {
            // Log técnico
            error_log('Erro ao atualizar relatório: ' . $e->getMessage());

            // Mensagem ao usuário
            $_SESSION['error'] = 'Erro ao atualizar relatório.';

            // Retorna para edição
            header(
                'Location: ' .
                    BASE_URL .
                    '/relatorio-plantao/plantao/' .
                    rawurlencode($uuid) .
                    '/editar'
            );
            exit;
        }
    }

    private function normalizarPayload(array $req, array $base = []): array
    {
        $dataInicio = !empty($req['data_inicio'])
            ? str_replace('T', ' ', (string)$req['data_inicio']) . ':00'
            : ($base['data_inicio'] ?? null);

        $dataFim = !empty($req['data_fim'])
            ? str_replace('T', ' ', (string)$req['data_fim']) . ':00'
            : ($base['data_fim'] ?? null);

        $pacienteId = (int)($req['paciente_id'] ?? ($base['paciente_id'] ?? 0));
        $cuidadorId = !empty($req['cuidador_id'])
            ? (int)$req['cuidador_id']
            : (int)($base['cuidador_id'] ?? 0);

        return [
            'paciente_id' => $pacienteId,
            'cuidador_id' => $cuidadorId ?: null,
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'evolucao' => trim((string)($req['evolucao'] ?? ($base['evolucao'] ?? ''))),
            'estado_paciente' => trim((string)($req['estado_paciente'] ?? ($base['estado_paciente'] ?? ''))),
            'alimentacao' => $this->stringValue($req['alimentacao'] ?? ($base['alimentacao'] ?? null)),
            'eliminacoes' => $this->toJsonValue($req['eliminacoes'] ?? ($base['eliminacoes'] ?? [])),
            'medicacoes' => $this->toJsonValue($req['medicacoes'] ?? ($base['medicacoes'] ?? [])),
            'intercorrencias' => !empty($req['sem_intercorrencias'])
                ? $this->toJsonValue([])
                : $this->toJsonValue($req['intercorrencias'] ?? ($base['intercorrencias'] ?? [])),
            'observacoes_gerais' => trim((string)($req['observacoes_gerais'] ?? ($base['observacoes_gerais'] ?? ''))),
            'consciencia' => $this->stringValue($req['consciencia'] ?? ($base['consciencia'] ?? null)),
            'nivel_dor' => (int)($req['nivel_dor'] ?? ($base['nivel_dor'] ?? 0)),
            'hidratacao_ml' => (int)($req['hidratacao_ml'] ?? ($base['hidratacao_ml'] ?? 0)),
            'higiene' => $this->stringValue($req['higiene'] ?? ($base['higiene'] ?? null)),
            'sono' => $this->stringValue($req['sono'] ?? ($base['sono'] ?? null)),
            'decubito' => $this->toJsonValue($req['decubito'] ?? ($base['decubito'] ?? [])),
            'pa' => trim((string)($req['sv_pa'] ?? ($base['pa'] ?? ''))),
            'fc' => trim((string)($req['sv_fc'] ?? ($base['fc'] ?? ''))),
            'temperatura' => trim((string)($req['sv_temp'] ?? ($base['temperatura'] ?? ''))),
            'spo2' => trim((string)($req['sv_spo2'] ?? ($base['spo2'] ?? ''))),
            'hgt' => trim((string)($req['sv_hgt'] ?? ($base['hgt'] ?? ''))),
            'observacao_sv' => trim((string)($req['observacao_sv'] ?? ($base['observacao_sv'] ?? ''))),
        ];
    }

    private function stringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function toJsonValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }
            if ($trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{')) {
                return $trimmed;
            }
            return json_encode([$trimmed], JSON_UNESCAPED_UNICODE);
        }

        if (is_array($value)) {
            $filtered = array_values(array_filter($value, static fn($item) => $item !== null && $item !== ''));
            return json_encode($filtered, JSON_UNESCAPED_UNICODE);
        }

        return json_encode([(string)$value], JSON_UNESCAPED_UNICODE);
    }

    private function redirectAposSalvar(int $pacienteId, int $relatorioId): void
    {
        if ($pacienteId > 0) {
            $pacienteModel = new Paciente();
            $paciente      = $pacienteModel->buscarPorId($pacienteId);
            $uuid          = $paciente['uuid'] ?? null;

            if ($uuid) {
                header('Location: ' . BASE_URL . '/relatorio-plantao/paciente/' . $uuid);
                exit;
            }
        }

        if ($relatorioId > 0) {
            $model     = new RelatorioPlantao();
            $relatorio = $model->buscarPorIdCompleto($relatorioId);
            $uuid      = $relatorio['uuid'] ?? null;

            if ($uuid) {
                header('Location: ' . BASE_URL . '/relatorio-plantao/plantao/' . $uuid . '/editar');
                exit;
            }
        }

        header('Location: ' . BASE_URL . '/relatorio-plantao');
        exit;
    }


    private function normalizarPaciente(?array $raw, Paciente $model, int $id): array
    {
        if (!$raw) {
            return [
                'id' => 0,
                'nome' => '',
                'iniciais' => '',
                'prontuario' => '',
                'idade' => 0,
                'diagnostico' => '',
                'tem_diabetes' => false,
                'acamado' => false,
            ];
        }

        $anamnese = [];
        if ($id > 0 && method_exists($model, 'buscarAnamnese')) {
            $anamnese = $model->buscarAnamnese($id) ?? [];
        }

        $nome = $raw['nome_completo'] ?? $raw['nome'] ?? '';

        return [
            'id' => (int)($raw['id'] ?? 0),
            'nome' => $nome,
            'iniciais' => $this->iniciais($nome),
            'prontuario' => (string)($raw['id'] ?? ''),
            'idade' => $this->calcularIdade($raw['data_nascimento'] ?? ''),
            'diagnostico' => $raw['diagnostico'] ?? '',
            'tem_diabetes' => ($anamnese['diabetes'] ?? '') === 'Sim' || !empty($anamnese['tem_diabetes']),
            'acamado' => ($anamnese['acamado'] ?? '') === 'Sim' || !empty($anamnese['acamado']),
        ];
    }

    private function iniciais(string $nome): string
    {
        $partes = array_values(array_filter(explode(' ', trim($nome))));
        $ini = '';

        foreach (array_slice($partes, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }

        return $ini ?: '?';
    }

    private function calcularIdade(string $dataNasc): int
    {
        if (!$dataNasc) {
            return 0;
        }

        try {
            return (int)(new \DateTime($dataNasc))->diff(new \DateTime())->y;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Exibe um relatório individual em modo leitura.
     *
     * Rota:
     * GET /relatorio-plantao/plantao/{uuid}
     */
    public function show(string $uuid): void
    {
        $model = new RelatorioPlantao();

        // Busca pelo UUID
        $relatorio = $model->buscarPorUuid($uuid);

        if (!$relatorio) {
            $_SESSION['error'] = 'Relatório não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        // Carregar dados do paciente
        $paciente = [];
        if (!empty($relatorio['paciente_id'])) {
            $pacienteModel = new Paciente();
            $pacienteRaw = $pacienteModel->buscarPorId((int)$relatorio['paciente_id']);
            $paciente = $this->normalizarPaciente(
                $pacienteRaw,
                $pacienteModel,
                (int)$relatorio['paciente_id']
            );
        }

        // Carregar dados do cuidador (opcional)
        $cuidador = [];

        if (!empty($relatorio['cuidador_id'])) {
            $cuidadorModel = new Cuidador();

            // O seu model já possui o método all()
            $listaCuidadores = $cuidadorModel->all();

            foreach ($listaCuidadores as $item) {
                if ((int)($item['id'] ?? 0) === (int)$relatorio['cuidador_id']) {
                    $cuidador = [
                        'nome'     => $item['nome_completo'] ?? $item['nome'] ?? '',
                        'registro' => $item['registro'] ?? $item['coren'] ?? '',
                    ];
                    break;
                }
            }
        }
        // Renderizar view de visualização completa
        $this->view('relatorio_plantao/show', [
            'pageTitle' => 'Relatório de Plantão',
            'relatorio' => $relatorio,
            'paciente'  => $paciente,
            'cuidador'  => $cuidador,
        ]);
    }
}
