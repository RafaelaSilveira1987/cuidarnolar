<?php

namespace App\Controllers;

use App\Models\Anamnese;
use App\Models\ContratoPaciente;
use App\Models\Escala;
use App\Models\Financeiro;
use App\Models\Historico;
use App\Models\MedicacaoPaciente;
use App\Models\Paciente;
use App\Models\PlanoCuidado;
use App\Models\Responsavel;

class PacienteController extends ResourceController
{
    protected string $modelClass = Paciente::class;
    protected string $routeBase = '/pacientes';
    protected string $viewTitle = 'Pacientes';
    protected string $singularTitle = 'Paciente';

    protected array $columns = [
        'id'               => '#',
        'nome_completo'    => 'Nome',
        'cpf'              => 'CPF',
        'responsavel_nome' => 'Responsável',
        'cuidador_nome'    => 'Cuidador',
        'status'           => 'Status',
    ];

    protected array $detailFields = [
        'id'                  => '#',
        'nome_completo'       => 'Nome completo',
        'data_nascimento'     => 'Nascimento',
        'idade_calculada'     => 'Idade',
        'sexo'                => 'Sexo',
        'cpf'                 => 'CPF',
        'rg'                  => 'RG',
        'cartao_nac_sus'      => 'CNS / Cartao SUS',
        'telefone_principal'  => 'Telefone principal',
        'telefone_secundario' => 'Telefone secundario',
        'email'               => 'E-mail',
        'endereco_completo'   => 'Endereco',
        'diagnostico'         => 'Diagnostico',
        'cid_principal'       => 'CID principal',
        'comorbidades'        => 'Comorbidades',
        'alergias'            => 'Alergias',
        'tipo_sanguineo'      => 'Tipo sanguineo',
        'peso'                => 'Peso',
        'altura'              => 'Altura',
        'responsavel_nome'    => 'Responsavel vinculado',
        'cuidador_nome'       => 'Cuidador',
        'status'              => 'Status',
    ];

    public function show(string $id): void
    {
        $model = $this->pacienteModel();
        $record = $this->resolvePaciente($id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $record['idade_calculada'] = $this->calcularIdade($record['data_nascimento'] ?? '');
        $record['idade'] = $record['idade_calculada'];

        $aba = (string) $this->input('aba', 'cadastro');
        $abasValidas = ['cadastro', 'responsaveis', 'anamnese', 'historico', 'plano', 'plantao', 'medicacoes', 'contratos', 'contrato_escala'];

        if (!in_array($aba, $abasValidas, true)) {
            $aba = 'cadastro';
        }

        $pacienteId = (int) $record['id'];
        $responsaveis = (new Responsavel())->listByPacienteId($pacienteId);
        $contratoModel = new ContratoPaciente();
        $contratoAtivo = $contratoModel->contratoAtivoPorPaciente($pacienteId);
        $escalaModel = new Escala();
        $planoModel = new PlanoCuidado();

        $this->view('pacientes/show', [
            'pageTitle'    => 'Paciente — ' . ($record['nome_completo'] ?? ''),
            'title'        => 'Paciente — ' . ($record['nome_completo'] ?? ''),
            'routeBase'    => $this->routeBase,
            'record'       => $record,
            'paciente'     => $record,
            'fields'       => $this->detailFields,
            'aba'          => $aba,
            'responsaveis' => $responsaveis,
            'anamneses'    => (new Anamnese())->listByPacienteId($pacienteId),
            'historicos'   => (new Historico())->listByPacienteId($pacienteId),
            'medicacoes'   => (new MedicacaoPaciente())->listByPacienteId($pacienteId),
            'contratoAtivo' => $contratoAtivo ?: [],
            'contratosPaciente' => $contratoModel->historicoPorPaciente($pacienteId),
            'contratoFinanceiroResumo' => $contratoAtivo ? $contratoModel->resumoFinanceiroContrato((int)$contratoAtivo['id']) : [],
            'empresaContratoPadrao' => $contratoModel->empresaPadrao(),
            'responsaveisOptions' => $model->responsaveisOptions(),
            'tipoCoberturaSugerido' => $contratoModel->inferirTipoCobertura($contratoAtivo ?: null),
            'escalaResumo' => $escalaModel->resumoPorPaciente($pacienteId),
            'cuidadoresEscalaOptions' => $escalaModel->listaCuidadores(),
            'planoCuidadoAtivo' => $planoModel->planoAtivoPorPaciente($pacienteId) ?: [],
            'planosCuidadoHistorico' => $planoModel->historicoPorPaciente($pacienteId),
            'planosCuidadoModelos' => $planoModel->listarModelos(),
        ]);
    }

    public function create(): void
    {
        $this->renderPacienteForm([], [], 'Novo Paciente');
    }

    public function store(): void
    {
        $data = $this->pacienteInput();
        $errors = $this->validatePaciente($data);

        if ($errors !== []) {
            $this->renderPacienteForm($data, $errors, 'Novo Paciente');
            return;
        }

        $id = $this->pacienteModel()->createPaciente($data);
        (new MedicacaoPaciente())->salvarListaPaciente($id, $this->medicacoesInput());

        $novoPaciente = $this->pacienteModel()->findForShow($id);
        $resourceKey = $novoPaciente['uuid'] ?? $id;

        $this->flash('success', 'Paciente cadastrado com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)$resourceKey));
    }

    public function edit(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderPacienteForm($paciente, [], 'Editar Paciente', (int) $paciente['id']);
    }

    public function update(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->pacienteInput();
        $errors = $this->validatePaciente($data);

        if ($errors !== []) {
            $this->renderPacienteForm(array_merge($paciente, $data), $errors, 'Editar Paciente', (int) $paciente['id']);
            return;
        }

        $pacienteId = (int) $paciente['id'];
        $model->updatePaciente($pacienteId, $data);
        (new MedicacaoPaciente())->salvarListaPaciente($pacienteId, $this->medicacoesInput());

        $this->flash('success', 'Paciente atualizado com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $pacienteId)));
    }

    public function inativar(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $model->inativar((int) $paciente['id'], (string) $this->input('motivo_inativacao', ''));
        $this->flash('success', 'Paciente inativado com sucesso.');
        $this->redirect('/pacientes');
    }

    protected function renderPacienteForm(
        array $paciente,
        array $errors,
        string $title,
        ?int $id = null
    ): void {
        $model = $this->pacienteModel();

        $this->view('pacientes/form', [
            'pageTitle' => $title,
            'title' => $title,
            'paciente' => $paciente,
            'errors' => $errors,
            'responsaveis' => $model->responsaveisOptions(),
            'cuidadores' => $model->cuidadoresOptions(),
            'medicacoes' => $id ? (new MedicacaoPaciente())->listByPacienteId($id) : [],
            'medicacaoOptions' => (new MedicacaoPaciente())->formOptions(),
            'action' => $id ? '/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) : '/pacientes',
            'isEdit' => $id !== null,
        ]);
    }

    private function validatePaciente(array $data): array
    {
        $errors = [];

        if (trim((string) ($data['nome_completo'] ?? '')) === '') {
            $errors['nome_completo'] = 'Informe o nome completo.';
        }

        if (!empty($data['data_nascimento']) && strtotime((string) $data['data_nascimento']) === false) {
            $errors['data_nascimento'] = 'Informe uma data de nascimento valida.';
        }

        if (!in_array(($data['status'] ?? 'Ativo'), ['Ativo', 'Inativo'], true)) {
            $errors['status'] = 'Status invalido.';
        }

        return $errors;
    }

    private function pacienteInput(): array
    {
        return [
            'nome_completo' => $this->input('nome_completo', ''),
            'data_nascimento' => $this->input('data_nascimento', ''),
            'sexo' => $this->input('sexo', ''),
            'cpf' => $this->input('cpf', ''),
            'rg' => $this->input('rg', ''),
            'cartao_nac_sus' => $this->input('cartao_nac_sus', ''),
            'foto' => $this->input('foto', ''),
            'endereco_completo' => $this->input('endereco_completo', ''),
            'telefone_principal' => $this->input('telefone_principal', ''),
            'telefone_secundario' => $this->input('telefone_secundario', ''),
            'email' => $this->input('email', ''),
            'plano_saude' => $this->input('plano_saude', ''),
            'responsavel_id' => $this->input('responsavel_id', ''),
            'cuidador_id' => $this->input('cuidador_id', ''),
            'anamnese_id' => $this->input('anamnese_id', ''),
            'diagnostico' => $this->input('diagnostico', ''),
            'cid_principal' => $this->input('cid_principal', ''),
            'diagnostico_principal' => $this->input('diagnostico_principal', ''),
            'comorbidades' => $this->input('comorbidades', ''),
            'alergias' => $this->input('alergias', ''),
            'historico_cirurgico' => $this->input('historico_cirurgico', ''),
            'tipo_sanguineo' => $this->input('tipo_sanguineo', ''),
            'peso' => $this->input('peso', ''),
            'altura' => $this->input('altura', ''),
            'motivo_homecare' => $this->input('motivo_homecare', ''),
            'dieta_tipo' => $this->input('dieta_tipo', ''),
            'dieta_restricao' => $this->input('dieta_restricao', ''),
            'alimentacao_via' => $this->input('alimentacao_via', ''),
            'sonda_vesical' => $this->input('sonda_vesical', 'Nao'),
            'incontinencia' => $this->input('incontinencia', ''),
            'mobilidade' => $this->input('mobilidade', ''),
            'estado_cognitivo_base' => $this->input('estado_cognitivo_base', ''),
            'usa_sonda' => $this->input('usa_sonda', 'Nao'),
            'usa_oxigenio' => $this->input('usa_oxigenio', 'Nao'),
            'traqueostomia' => $this->input('traqueostomia', 'Nao'),
            'gastrostomia' => $this->input('gastrostomia', 'Nao'),
            'colostomia' => $this->input('colostomia', 'Nao'),
            'cateter_vesical' => $this->input('cateter_vesical', 'Nao'),
            'gtt' => $this->input('gtt', 'Nao'),
            'sne' => $this->input('sne', 'Nao'),
            'cateter_venoso' => $this->input('cateter_venoso', 'Nao'),
            'picc' => $this->input('picc', 'Nao'),
            'lesao_pressao' => $this->input('lesao_pressao', 'Nao'),
            'curativos' => $this->input('curativos', ''),
            'areas_risco' => $this->input('areas_risco', ''),
            'condutas_permanentes' => $this->input('condutas_permanentes', []),
            'convenio' => $this->input('convenio', ''),
            'numero_carteirinha' => $this->input('numero_carteirinha', ''),
            'prescricao_medica' => $this->input('prescricao_medica', ''),
            'termos_assinados' => $this->input('termos_assinados', ''),
            'observacoes_clinicas' => $this->input('observacoes_clinicas', ''),
            'status' => $this->input('status', 'Ativo'),
            'motivo_inativacao' => $this->input('motivo_inativacao', ''),
        ];
    }


    public function salvarEscalaBase(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $cuidadorIds = $_POST['cuidador_ids'] ?? [];
        if (!is_array($cuidadorIds)) {
            $cuidadorIds = [];
        }

        $cuidadorCores = $_POST['cuidador_cores'] ?? [];
        if (!is_array($cuidadorCores)) {
            $cuidadorCores = [];
        }

        $data = [
            'nome' => $this->input('nome', 'Escala base'),
            'tipo_cobertura' => $this->input('tipo_cobertura', '12h'),
            'hora_inicio' => $this->input('hora_inicio', '07:00'),
            'hora_fim' => $this->input('hora_fim', '19:00'),
            'tipo_atendimento' => $this->input('tipo_atendimento', 'domiciliar'),
            'local' => $this->input('local', ''),
            'recorrente' => $this->input('recorrente', 'sim'),
            'domingo' => $this->input('domingo', ''),
            'segunda' => $this->input('segunda', ''),
            'terca' => $this->input('terca', ''),
            'quarta' => $this->input('quarta', ''),
            'quinta' => $this->input('quinta', ''),
            'sexta' => $this->input('sexta', ''),
            'sabado' => $this->input('sabado', ''),
            'revezamento_automatico' => $this->input('revezamento_automatico', ''),
            'observacoes' => $this->input('observacoes', ''),
        ];

        (new Escala())->salvarBasePaciente((int)$paciente['id'], $data, $cuidadorIds, $cuidadorCores);

        $this->flash('success', 'Escala base atualizada com sucesso. O contrato financeiro fica na aba Contratos do paciente.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=contrato_escala');
    }


    public function planoNovo(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $model = new PlanoCuidado();
        $gerar = (string)$this->input('gerar', '');
        $modelo = (string)$this->input('modelo', '');
        $record = $gerar === '1'
            ? $model->gerarRascunhoPaciente($paciente, $modelo)
            : $model->gerarRascunhoPaciente($paciente, $modelo ?: 'geral');

        if ($gerar !== '1') {
            $record['objetivos'] = '';
            $record['monitoramento'] = '';
            $record['oxigenoterapia'] = '';
            $record['nebulizacao'] = '';
            $record['controle_ambiental'] = '';
            $record['alimentacao_hidratacao'] = '';
            $record['atividade_repouso'] = '';
            $record['medicamentos'] = '';
            $record['comunicacao_familia'] = '';
            $record['sinais_alerta'] = '';
            $record['observacoes'] = '';
        }

        $this->renderPlanoCuidadoForm($paciente, $record, [], 'Novo plano de cuidados');
    }

    public function planoStore(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->planoCuidadoInput();
        $errors = $this->validatePlanoCuidado($data);

        if ($errors !== []) {
            $this->renderPlanoCuidadoForm($paciente, $data, $errors, 'Novo plano de cuidados');
            return;
        }

        $model = new PlanoCuidado();
        $planoId = $model->salvarPlano((int)$paciente['id'], $data);

        if (($data['status'] ?? '') === 'Ativo') {
            $model->ativarPlano((int)$paciente['id'], $planoId);
        }

        $this->flash('success', 'Plano de cuidados cadastrado com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=plano');
    }

    public function planoEditar(string $id, string $planoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $plano = (new PlanoCuidado())->findByPaciente((int)$paciente['id'], (int)$planoId);
        if (!$plano) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Plano de cuidados não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $this->renderPlanoCuidadoForm($paciente, $plano, [], 'Editar plano de cuidados', (int)$planoId);
    }

    public function planoUpdate(string $id, string $planoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $model = new PlanoCuidado();
        $plano = $model->findByPaciente((int)$paciente['id'], (int)$planoId);
        if (!$plano) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Plano de cuidados não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $data = $this->planoCuidadoInput();
        $errors = $this->validatePlanoCuidado($data);

        if ($errors !== []) {
            $this->renderPlanoCuidadoForm($paciente, array_merge($plano, $data), $errors, 'Editar plano de cuidados', (int)$planoId);
            return;
        }

        $model->salvarPlano((int)$paciente['id'], $data, (int)$planoId);

        if (($data['status'] ?? '') === 'Ativo') {
            $model->ativarPlano((int)$paciente['id'], (int)$planoId);
        }

        $this->flash('success', 'Plano de cuidados atualizado com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=plano');
    }

    public function planoAtivar(string $id, string $planoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        (new PlanoCuidado())->ativarPlano((int)$paciente['id'], (int)$planoId);
        $this->flash('success', 'Plano de cuidados ativado.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=plano');
    }

    public function planoArquivar(string $id, string $planoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        (new PlanoCuidado())->arquivarPlano((int)$paciente['id'], (int)$planoId);
        $this->flash('success', 'Plano de cuidados arquivado.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=plano');
    }


    public function planoPdf(string $id, string $planoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $model = new PlanoCuidado();
        $plano = $model->findByPaciente((int)$paciente['id'], (int)$planoId);

        if (!$plano) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Plano de cuidados não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $empresa = [];
        try {
            $empresa = (new ContratoPaciente())->empresaPadrao();
        } catch (\Throwable) {
            $empresa = [];
        }

        ob_start();
        require dirname(__DIR__) . '/Views/pacientes/plano-pdf.php';
        $html = ob_get_clean();

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $nomePaciente = preg_replace('/[^a-zA-Z0-9_-]/', '-', strtolower((string)($paciente['nome_completo'] ?? 'paciente')));
        $versao = (int)($plano['versao'] ?? 1);
        $filename = 'plano-cuidados-' . trim($nomePaciente, '-') . '-v' . $versao . '.pdf';

        $dompdf->stream($filename, [
            'Attachment' => false,
        ]);
    }

    private function renderPlanoCuidadoForm(array $paciente, array $record, array $errors, string $title, ?int $planoId = null): void
    {
        $model = new PlanoCuidado();

        $this->view('pacientes/plano-form', [
            'pageTitle' => $title,
            'title' => $title,
            'routeBase' => $this->routeBase,
            'paciente' => $paciente,
            'record' => $record,
            'errors' => $errors,
            'modelosPlano' => $model->listarModelos(),
            'action' => $planoId
                ? '/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '/planos/' . $planoId
                : '/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '/planos',
            'isEdit' => $planoId !== null,
        ]);
    }

    private function planoCuidadoInput(): array
    {
        return [
            'modelo_chave' => $this->input('modelo_chave', ''),
            'titulo' => $this->input('titulo', ''),
            'subtitulo' => $this->input('subtitulo', ''),
            'responsavel_tecnico' => $this->input('responsavel_tecnico', ''),
            'data_inicio' => $this->input('data_inicio', date('Y-m-d')),
            'data_revisao' => $this->input('data_revisao', ''),
            'status' => $this->input('status', 'Rascunho'),
            'versao' => $this->input('versao', '1'),
            'resumo_clinico' => $this->input('resumo_clinico', ''),
            'objetivos' => $this->input('objetivos', ''),
            'monitoramento' => $this->input('monitoramento', ''),
            'oxigenoterapia' => $this->input('oxigenoterapia', ''),
            'nebulizacao' => $this->input('nebulizacao', ''),
            'controle_ambiental' => $this->input('controle_ambiental', ''),
            'alimentacao_hidratacao' => $this->input('alimentacao_hidratacao', ''),
            'atividade_repouso' => $this->input('atividade_repouso', ''),
            'medicamentos' => $this->input('medicamentos', ''),
            'comunicacao_familia' => $this->input('comunicacao_familia', ''),
            'sinais_alerta' => $this->input('sinais_alerta', ''),
            'observacoes' => $this->input('observacoes', ''),
        ];
    }

    private function validatePlanoCuidado(array $data): array
    {
        $errors = [];

        if (trim((string)($data['titulo'] ?? '')) === '') {
            $errors['titulo'] = 'Informe o título do plano.';
        }

        if (trim((string)($data['data_inicio'] ?? '')) === '') {
            $errors['data_inicio'] = 'Informe a data de início.';
        }

        if (trim((string)($data['objetivos'] ?? '')) === '') {
            $errors['objetivos'] = 'Informe pelo menos os objetivos do cuidado.';
        }

        return $errors;
    }

    public function contratoNovo(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderContratoPacienteForm($paciente, [], [], 'Novo contrato do paciente');
    }

    public function contratoStore(string $id): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->contratoPacienteInput();
        $errors = $this->validateContratoPaciente($data);

        if ($errors !== []) {
            $this->renderContratoPacienteForm($paciente, $data, $errors, 'Novo contrato do paciente');
            return;
        }

        (new ContratoPaciente())->salvarContratoCompleto((int)$paciente['id'], $data, $paciente);

        $this->flash('success', 'Contrato cadastrado com sucesso. Agora você pode gerar o financeiro pela aba Contratos.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=contratos');
    }

    public function contratoEditar(string $id, string $contratoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $contrato = (new ContratoPaciente())->findByPaciente((int)$paciente['id'], (int)$contratoId);
        if (!$contrato) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Contrato não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $this->renderContratoPacienteForm($paciente, $contrato, [], 'Editar contrato do paciente', (int)$contratoId);
    }

    public function contratoUpdate(string $id, string $contratoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $contratoModel = new ContratoPaciente();
        $contrato = $contratoModel->findByPaciente((int)$paciente['id'], (int)$contratoId);
        if (!$contrato) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Contrato não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $data = $this->contratoPacienteInput();
        $errors = $this->validateContratoPaciente($data);

        if ($errors !== []) {
            $this->renderContratoPacienteForm($paciente, array_merge($contrato, $data), $errors, 'Editar contrato do paciente', (int)$contratoId);
            return;
        }

        $contratoModel->salvarContratoCompleto((int)$paciente['id'], $data, $paciente, (int)$contratoId);

        $this->flash('success', 'Contrato atualizado com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=contratos');
    }

    public function contratoGerarFinanceiro(string $id, string $contratoId): void
    {
        $paciente = $this->resolvePaciente($id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente não encontrado.'], 'layouts/blank');
            return;
        }

        $contrato = (new ContratoPaciente())->findByPaciente((int)$paciente['id'], (int)$contratoId);
        if (!$contrato) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Contrato não encontrado para este paciente.'], 'layouts/blank');
            return;
        }

        $inicio = trim((string)$this->input('periodo_inicio', date('Y-m-01')));
        $fim = trim((string)$this->input('periodo_fim', date('Y-m-t')));

        $resultado = (new Financeiro())->gerarReceitasContratoPaciente((int)$contrato['id'], $inicio, $fim);

        $tipoFlash = ((int)($resultado['criadas'] ?? 0) > 0) ? 'success' : 'info';
        $this->flash($tipoFlash, (string)($resultado['mensagem'] ?? 'Processamento concluído.'));
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=contratos');
    }

    private function renderContratoPacienteForm(array $paciente, array $record, array $errors, string $title, ?int $contratoId = null): void
    {
        $contratoModel = new ContratoPaciente();
        $empresaPadrao = $contratoModel->empresaPadrao();

        foreach ($empresaPadrao as $key => $value) {
            if (!array_key_exists($key, $record) || trim((string)$record[$key]) === '') {
                $record[$key] = $value;
            }
        }

        if (($record['responsavel_legal_id'] ?? '') === '' && !empty($paciente['responsavel_id'])) {
            $record['responsavel_legal_id'] = $paciente['responsavel_id'];
        }

        if (($record['responsavel_financeiro_id'] ?? '') === '' && !empty($paciente['responsavel_id'])) {
            $record['responsavel_financeiro_id'] = $paciente['responsavel_id'];
        }

        $this->view('pacientes/contrato-form', [
            'pageTitle' => $title,
            'title' => $title,
            'routeBase' => $this->routeBase,
            'paciente' => $paciente,
            'record' => $record,
            'errors' => $errors,
            'responsaveisOptions' => $this->pacienteModel()->responsaveisOptions(),
            'action' => $contratoId
                ? '/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '/contratos/' . $contratoId
                : '/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '/contratos',
            'isEdit' => $contratoId !== null,
        ]);
    }

    private function contratoPacienteInput(): array
    {
        $servicos = $_POST['servicos_contratados'] ?? [];
        if (!is_array($servicos)) {
            $servicos = [];
        }

        return [
            'responsavel_legal_id' => $this->input('responsavel_legal_id', ''),
            'responsavel_financeiro_id' => $this->input('responsavel_financeiro_id', ''),
            'tipo_servico' => $this->input('tipo_servico', ''),
            'servicos_contratados' => $servicos,
            'escala_contratada' => $this->input('escala_contratada', ''),
            'tipo_plantao' => $this->input('tipo_plantao', ''),
            'hora_inicio_padrao' => $this->input('hora_inicio_padrao', ''),
            'hora_fim_padrao' => $this->input('hora_fim_padrao', ''),
            'tipo_prazo' => $this->input('tipo_prazo', 'Indeterminado'),
            'tipo_cobranca' => $this->input('tipo_cobranca', 'Mensal'),
            // Campo visual removido da tela. Mantido como null para compatibilidade com a coluna antiga.
            'valor_contrato' => null,
            'valor_mensal' => $this->input('valor_mensal', ''),
            'valor_semanal' => $this->input('valor_semanal', ''),
            'valor_plantao' => $this->input('valor_plantao', ''),
            'forma_pagamento' => $this->input('forma_pagamento', ''),
            'dia_vencimento' => $this->input('dia_vencimento', '10'),
            'multa_percentual' => $this->input('multa_percentual', ''),
            'juros_percentual' => $this->input('juros_percentual', ''),
            'vigencia_inicio' => $this->input('vigencia_inicio', ''),
            'vigencia_fim' => $this->input('vigencia_fim', ''),
            'empresa_razao_social' => $this->input('empresa_razao_social', ''),
            'empresa_cnpj' => $this->input('empresa_cnpj', ''),
            'empresa_endereco' => $this->input('empresa_endereco', ''),
            'empresa_responsavel_contrato' => $this->input('empresa_responsavel_contrato', ''),
            'status' => $this->input('status', 'Ativo'),
            'observacoes' => $this->input('observacoes', ''),
        ];
    }

    private function validateContratoPaciente(array $data): array
    {
        $errors = [];

        $servicos = $data['servicos_contratados'] ?? [];
        if (!is_array($servicos) || array_filter($servicos) === []) {
            $errors['servicos_contratados'] = 'Selecione pelo menos um serviço contratado.';
        }

        if (trim((string)($data['vigencia_inicio'] ?? '')) === '') {
            $errors['vigencia_inicio'] = 'Informe a data de início do contrato.';
        }

        if (($data['tipo_prazo'] ?? '') === 'Determinado' && trim((string)($data['vigencia_fim'] ?? '')) === '') {
            $errors['vigencia_fim'] = 'Contrato determinado precisa de previsão de término.';
        }

        $tipoCobranca = trim((string)($data['tipo_cobranca'] ?? 'Mensal'));
        if (!in_array($tipoCobranca, ['Mensal', 'Semanal', 'Por plantão'], true)) {
            $tipoCobranca = 'Mensal';
        }
        $valorMensal = $this->valorMonetarioContrato($data['valor_mensal'] ?? null);
        $valorSemanal = $this->valorMonetarioContrato($data['valor_semanal'] ?? null);
        $valorPlantao = $this->valorMonetarioContrato($data['valor_plantao'] ?? null);

        if ($tipoCobranca === 'Mensal' && $valorMensal <= 0) {
            $errors['valor_mensal'] = 'Para cobrança mensal, informe o valor mensal maior que zero.';
        }

        if ($tipoCobranca === 'Semanal' && $valorSemanal <= 0) {
            $errors['valor_semanal'] = 'Para cobrança semanal, informe o valor semanal maior que zero.';
        }

        if ($tipoCobranca === 'Por plantão' && $valorPlantao <= 0) {
            $errors['valor_plantao'] = 'Para cobrança por plantão, informe o valor por plantão maior que zero.';
        }

        if ($valorMensal <= 0 && $valorSemanal <= 0 && $valorPlantao <= 0) {
            $errors['valor_mensal'] = $errors['valor_mensal'] ?? 'Informe pelo menos um valor maior que zero.';
        }

        return $errors;
    }

    private function valorMonetarioContrato(mixed $valor): float
    {
        $valor = trim((string)$valor);
        if ($valor === '') {
            return 0.0;
        }

        $valor = str_replace(['R$', ' ', "Â "], '', $valor);

        if (str_contains($valor, ',') && str_contains($valor, '.')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (str_contains($valor, ',')) {
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float)$valor : 0.0;
    }

    private function medicacoesInput(): array
    {
        $itens = $_POST['medicacoes_continuas'] ?? [];
        return is_array($itens) ? $itens : [];
    }

    private function calcularIdade(mixed $dataNasc): string
    {
        if (!$dataNasc) {
            return '';
        }

        try {
            return (string) (new \DateTime((string) $dataNasc))->diff(new \DateTime())->y;
        } catch (\Throwable) {
            return '';
        }
    }


    private function resolvePaciente(string $id): array|false
    {
        $model = $this->pacienteModel();

        if (ctype_digit($id)) {
            return $model->findForShow((int) $id);
        }

        if (method_exists($model, 'findForShowByUuid')) {
            return $model->findForShowByUuid($id);
        }

        return false;
    }

    private function pacienteModel(): Paciente
    {
        return new Paciente();
    }
}
