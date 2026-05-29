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

            $medicacoes = $this->extrairMedicacoesPost($_POST['medicacoes'] ?? []);
            $relatorioModel->salvarMedicacoesPlantao($relatorioId, $medicacoes);

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

        if (!$paciente) {
            throw new \RuntimeException('Paciente não encontrado.');
        }

        $pacienteModel->garantirProntuario((int)$paciente['id']);

        $dados = $this->normalizarPayload($_POST);
        $dados['paciente_id'] = (int)$paciente['id'];
        $dados['status'] = ($_POST['acao'] ?? '') === 'assinar' ? 'finalizado' : 'rascunho';
        $dados['assinado'] = ($_POST['acao'] ?? '') === 'assinar' ? 1 : 0;
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
            'data_nascimento' => !empty($_POST['data_nascimento'])
                ? trim((string)$_POST['data_nascimento'])
                : null,
            'internacao' => trim((string)($_POST['internacao'] ?? '')),
            'tipo_local' => trim((string)($_POST['tipo_local'] ?? '')),
            'quarto' => trim((string)($_POST['quarto'] ?? '')),
            'nome_acompanhante' => trim((string)($_POST['nome_acompanhante'] ?? '')),

            // Status
            'status' => ($_POST['acao'] ?? '') === 'assinar' ? 'finalizado' : trim((string)($_POST['status'] ?? 'rascunho')),

            // Sinais vitais
            'pa' => trim((string)($_POST['pa'] ?? '')),
            'fc' => trim((string)($_POST['fc'] ?? '')),
            'temperatura' => trim((string)($_POST['temperatura'] ?? '')),
            'spo2' => trim((string)($_POST['spo2'] ?? '')),
            'frequencia_respiratoria' => trim((string)($_POST['frequencia_respiratoria'] ?? '')),
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
            'estado_geral' => trim((string)($_POST['estado_geral'] ?? '')),
            'queixas_referidas' => $_POST['queixas_referidas'] ?? null,
            'exame_fisico' => $_POST['exame_fisico'] ?? null,
            'pele_mucosas' => trim((string)($_POST['pele_mucosas'] ?? '')),
            'visita_medica' => $_POST['visita_medica'] ?? null,
            'entrada_saida_profissionais' => $_POST['entrada_saida_profissionais'] ?? null,
            'entrada_saida_familiares' => $_POST['entrada_saida_familiares'] ?? null,
            'plantao_entregue_para' => trim((string)($_POST['plantao_entregue_para'] ?? '')),
            'peso' => trim((string)($_POST['peso'] ?? '')),

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

            'diurese' => $_POST['diurese'] ?? null,
            'urina_horarios' => $_POST['urina_horarios'] ?? [],

            'evacuacao' => $_POST['evacuacao'] ?? null,
            'fezes_horarios' => $_POST['fezes_horarios'] ?? [],

            'dispositivos' => $_POST['dispositivos'] ?? [],
            'hidratacao_registros' => $_POST['hidratacao_registros'] ?? [],

            'alimentacao_via' => $_POST['alimentacao_via'] ?? null,
        ];

        try {
            // Atualiza no banco
            $relatorioModel->atualizarCompleto(
                (int)$relatorio['id'],
                $dados
            );

            // Sucesso
            $_SESSION['success'] = 'Relatório atualizado com sucesso.';

            $pacienteModel = new Paciente();
            $pacienteAtual = $pacienteModel->buscarPorId((int)($relatorio['paciente_id'] ?? 0));
            $pacienteUuid  = (string)($pacienteAtual['uuid'] ?? '');

            if ($pacienteUuid !== '') {
                header('Location: ' . BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid));
                exit;
            }

            header('Location: ' . BASE_URL . '/relatorio-plantao');
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
            'data_nascimento' => $this->stringValue($req['data_nascimento'] ?? $base['data_nascimento'] ?? null),
            'internacao' => trim((string)($req['internacao'] ?? ($base['internacao'] ?? ''))),
            'tipo_local' => $this->stringValue($req['tipo_local'] ?? $base['tipo_local'] ?? null),
            'quarto' => $this->stringValue($req['quarto'] ?? $base['quarto'] ?? null),
            'nome_acompanhante' => $this->stringValue($req['nome_acompanhante'] ?? $base['nome_acompanhante'] ?? null),
            'turno' => $this->stringValue($req['turno'] ?? $base['turno'] ?? null),
            'evolucao' => trim((string)($req['evolucao'] ?? ($base['evolucao'] ?? ''))),
            'estado_paciente' => trim((string)($req['estado_paciente'] ?? ($base['estado_paciente'] ?? ''))),
            'estado_geral' => $this->stringValue($req['estado_geral'] ?? $base['estado_geral'] ?? null),
            'queixas_referidas' => $this->toJsonValue($req['queixas_referidas'] ?? ($base['queixas_referidas'] ?? null)),
            'exame_fisico' => $this->toJsonValue($req['exame_fisico'] ?? ($base['exame_fisico'] ?? null)),
            'pele_mucosas' => $this->stringValue($req['pele_mucosas'] ?? $base['pele_mucosas'] ?? null),
            'alimentacao' => $this->stringValue($req['alimentacao'] ?? ($base['alimentacao'] ?? null)),
            'eliminacoes' => $this->toJsonValue($req['eliminacoes'] ?? ($base['eliminacoes'] ?? [])),
            'medicacoes' => $this->toJsonValue($req['medicacoes'] ?? ($base['medicacoes'] ?? [])),
            'intercorrencias' => !empty($req['sem_intercorrencias'])
                ? $this->toJsonValue([])
                : $this->toJsonValue($req['intercorrencias'] ?? ($base['intercorrencias'] ?? [])),
            'observacoes_gerais' => trim((string)($req['observacoes_gerais'] ?? ($base['observacoes_gerais'] ?? ''))),
            'visita_medica' => $this->toJsonValue($req['visita_medica'] ?? ($base['visita_medica'] ?? null)),
            'entrada_saida_profissionais' => $this->toJsonValue($req['entrada_saida_profissionais'] ?? ($base['entrada_saida_profissionais'] ?? null)),
            'entrada_saida_familiares' => $this->toJsonValue($req['entrada_saida_familiares'] ?? ($base['entrada_saida_familiares'] ?? null)),
            'plantao_entregue_para' => $this->stringValue($req['plantao_entregue_para'] ?? ($base['plantao_entregue_para'] ?? null)),
            'peso' => $this->stringValue($req['peso'] ?? ($base['peso'] ?? null)),
            'consciencia' => $this->stringValue($req['consciencia'] ?? ($base['consciencia'] ?? null)),
            'nivel_dor' => (int)($req['nivel_dor'] ?? ($base['nivel_dor'] ?? 0)),
            'hidratacao_ml' => (int)($req['hidratacao_ml'] ?? ($base['hidratacao_ml'] ?? 0)),
            'hidratacao_registros' => $this->toJsonValue($req['hidratacao_registros'] ?? ($base['hidratacao_registros'] ?? [])),
            'higiene' => $this->stringValue($req['higiene'] ?? ($base['higiene'] ?? null)),
            'sono' => $this->stringValue($req['sono'] ?? ($base['sono'] ?? null)),
            'decubito' => $this->toJsonValue($req['decubito'] ?? ($base['decubito'] ?? [])),
            'pa' => trim((string)($req['pa'] ?? ($base['pa'] ?? ''))),
            'fc' => trim((string)($req['fc'] ?? ($base['fc'] ?? ''))),
            'temperatura' => trim((string)($req['temperatura'] ?? ($base['temperatura'] ?? ''))),
            'spo2' => trim((string)($req['spo2'] ?? ($base['spo2'] ?? ''))),
            'frequencia_respiratoria' => trim((string)($req['frequencia_respiratoria'] ?? ($base['frequencia_respiratoria'] ?? ''))),
            'hgt' => trim((string)($req['hgt'] ?? ($base['hgt'] ?? ''))),
            'observacao_sv' => trim((string)($req['observacao_sv'] ?? ($base['observacao_sv'] ?? ''))),
            'urina_horarios' => $this->toJsonValue($req['urina_horarios'] ?? ($base['urina_horarios'] ?? [])),
            'fezes_horarios' => $this->toJsonValue($req['fezes_horarios'] ?? ($base['fezes_horarios'] ?? [])),
            'dispositivos' => $this->toJsonValue($req['dispositivos'] ?? ($base['dispositivos'] ?? [])),
            'alimentacao_via' => $this->stringValue($req['alimentacao_via'] ?? ($base['alimentacao_via'] ?? null)),
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

            'uuid' => $raw['uuid'] ?? null,

            'nome' => $nome,

            'iniciais' => $this->iniciais($nome),

            'prontuario' => (string)($raw['id'] ?? ''),

            'idade' => $this->calcularIdade(
                $raw['data_nascimento'] ?? ''
            ),

            'sexo' => $raw['sexo'] ?? null,

            'diagnostico' => $raw['diagnostico']
                ?? $raw['diagnostico_principal']
                ?? '',

            'cid_principal' => $raw['cid_principal'] ?? null,

            'alergias' => $raw['alergias'] ?? null,

            'tipo_sanguineo' => $raw['tipo_sanguineo'] ?? null,

            'mobilidade' => $raw['mobilidade'] ?? null,

            'estado_cognitivo_base' =>
            $raw['estado_cognitivo_base'] ?? null,

            'acamado' => ($anamnese['acamado'] ?? '') === 'Sim'
                || !empty($anamnese['acamado']),

            'tem_diabetes' => ($anamnese['diabetes'] ?? '') === 'Sim'
                || !empty($anamnese['tem_diabetes']),

            // dispositivos
            'usa_oxigenio' =>
            $raw['usa_oxigenio'] ?? 'Não',

            'usa_sonda' =>
            $raw['usa_sonda'] ?? 'Não',

            'traqueostomia' =>
            $raw['traqueostomia'] ?? 'Não',

            'gastrostomia' =>
            $raw['gastrostomia'] ?? 'Não',

            'colostomia' =>
            $raw['colostomia'] ?? 'Não',

            'cateter_vesical' =>
            $raw['cateter_vesical'] ?? 'Não',

            'gtt' => $raw['gtt'] ?? null,

            'sne' => $raw['sne'] ?? null,

            'picc' => $raw['picc'] ?? null,

            'cateter_venoso' =>
            $raw['cateter_venoso'] ?? null,

            // alertas clínicos
            'areas_risco' =>
            $raw['areas_risco'] ?? null,

            'condutas_permanentes' =>
            $raw['condutas_permanentes'] ?? null,

            'prescricao_medica' =>
            $raw['prescricao_medica'] ?? null,
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
     * Compatibilidade com links antigos.
     * A visualização oficial dos relatórios agora fica dentro do cadastro do paciente.
     */
    public function show(string $uuid): void
    {
        $model = new RelatorioPlantao();
        $relatorio = $model->buscarPorUuid($uuid);

        if (!$relatorio || empty($relatorio['paciente_id'])) {
            $_SESSION['error'] = 'Relatório não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        $pacienteModel = new Paciente();
        $paciente = $pacienteModel->buscarPorId((int)$relatorio['paciente_id']);

        if (!$paciente || empty($paciente['uuid'])) {
            $_SESSION['error'] = 'Paciente do relatório não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        header('Location: ' . BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode((string)$paciente['uuid']));
        exit;
    }

    public function generatePdf(string $uuid): void
    {
        $relatorioModel = new RelatorioPlantao();
        $pacienteModel  = new Paciente();

        $relatorio = $relatorioModel->buscarPorUuid($uuid);

        if (!$relatorio) {
            $_SESSION['error'] = 'Relatório não encontrado.';
            header('Location: ' . BASE_URL . '/relatorio-plantao');
            exit;
        }

        $pacienteId = (int)($relatorio['paciente_id'] ?? 0);

        $paciente = $pacienteId > 0
            ? $pacienteModel->buscarPorId($pacienteId)
            : [];

        if (!$paciente) {
            $paciente = [];
        }

        ob_start();

        require dirname(__DIR__) . '/Views/relatorio_plantao/pdf.php';

        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomePaciente = $paciente['nome_completo']
            ?? $relatorio['paciente_nome']
            ?? 'paciente';

        $dataRelatorio = !empty($relatorio['data_inicio'])
            ? date('d-m-Y', strtotime((string)$relatorio['data_inicio']))
            : date('d-m-Y');

        $filename = 'relatorio-plantao-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower($nomePaciente)) . '-' . $dataRelatorio . '.pdf';

        $dompdf->stream($filename, [
            'Attachment' => false,
        ]);
    }
}