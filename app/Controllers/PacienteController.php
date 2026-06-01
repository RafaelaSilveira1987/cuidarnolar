<?php

namespace App\Controllers;

use App\Models\Anamnese;
use App\Models\ContratoPaciente;
use App\Models\Escala;
use App\Models\Historico;
use App\Models\MedicacaoPaciente;
use App\Models\Paciente;
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
        $abasValidas = ['cadastro', 'responsaveis', 'anamnese', 'historico', 'plano', 'plantao', 'medicacoes', 'contrato_escala'];

        if (!in_array($aba, $abasValidas, true)) {
            $aba = 'cadastro';
        }

        $pacienteId = (int) $record['id'];
        $responsaveis = (new Responsavel())->listByPacienteId($pacienteId);
        $contratoModel = new ContratoPaciente();
        $contratoAtivo = $contratoModel->contratoAtivoPorPaciente($pacienteId);
        $escalaModel = new Escala();

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
            'tipoCoberturaSugerido' => $contratoModel->inferirTipoCobertura($contratoAtivo ?: null),
            'escalaResumo' => $escalaModel->resumoPorPaciente($pacienteId),
            'cuidadoresEscalaOptions' => $escalaModel->listaCuidadores(),
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

        $contratoModel = new ContratoPaciente();
        $contratoModel->salvarAtivoPaciente((int)$paciente['id'], [
            'tipo_servico' => $this->input('contrato_tipo_servico', ''),
            'valor_mensal' => $this->input('contrato_valor_mensal', '0'),
            'dia_vencimento' => $this->input('contrato_dia_vencimento', '10'),
            'forma_pagamento' => $this->input('contrato_forma_pagamento', ''),
            'vigencia_inicio' => $this->input('contrato_vigencia_inicio', date('Y-m-d')),
            'vigencia_fim' => $this->input('contrato_vigencia_fim', ''),
            'status' => $this->input('contrato_status', 'Ativo'),
            'observacoes' => $this->input('contrato_observacoes', ''),
        ]);

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

        $this->flash('success', 'Contrato e escala atualizados com sucesso.');
        $this->redirect('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $id)) . '?aba=contrato_escala');
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
