<?php

namespace App\Controllers;

use App\Models\Anamnese;
use App\Models\Historico;
use App\Models\MedicacaoPaciente;
use App\Models\Paciente;

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
        'responsavel_nome' => 'Responsavel',
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
        $record = $model->findForShow((int) $id);

        if (!$record) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $record['idade_calculada'] = $this->calcularIdade($record['data_nascimento'] ?? '');

        $aba = (string) $this->input('aba', 'cadastro');
        $abasValidas = ['cadastro', 'anamnese', 'historico', 'plano', 'plantao', 'medicacoes'];

        if (!in_array($aba, $abasValidas, true)) {
            $aba = 'cadastro';
        }

        $pacienteId = (int) $record['id'];

        $this->view('pacientes/show', [
            'pageTitle'  => $this->singularTitle,
            'title'      => $this->singularTitle,
            'routeBase'  => $this->routeBase,
            'record'     => $record,
            'fields'     => $this->detailFields,
            'aba'        => $aba,
            'anamneses'  => (new Anamnese())->listByPacienteId($pacienteId),
            'historicos' => (new Historico())->listByPacienteId($pacienteId),
            'medicacoes' => (new MedicacaoPaciente())->listByPacienteId($pacienteId),
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

        $this->flash('success', 'Paciente cadastrado com sucesso.');
        $this->redirect('/pacientes/' . $id);
    }

    public function edit(string $id): void
    {
        $paciente = $this->pacienteModel()->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $this->renderPacienteForm($paciente, [], 'Editar Paciente', (int) $id);
    }

    public function update(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $model->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $data = $this->pacienteInput();
        $errors = $this->validatePaciente($data);

        if ($errors !== []) {
            $this->renderPacienteForm(array_merge($paciente, $data), $errors, 'Editar Paciente', (int) $id);
            return;
        }

        $pacienteId = (int) $id;
        $model->updatePaciente($pacienteId, $data);
        (new MedicacaoPaciente())->salvarListaPaciente($pacienteId, $this->medicacoesInput());

        $this->flash('success', 'Paciente atualizado com sucesso.');
        $this->redirect('/pacientes/' . $id);
    }

    public function inativar(string $id): void
    {
        $model = $this->pacienteModel();
        $paciente = $model->findForShow((int) $id);

        if (!$paciente) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Paciente nao encontrado.'], 'layouts/blank');
            return;
        }

        $model->inativar((int) $id, (string) $this->input('motivo_inativacao', ''));
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
            'action' => $id ? "/pacientes/{$id}" : '/pacientes',
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
            'responsavel_nome_texto' => $this->input('responsavel_nome_texto', ''),
            'responsavel_parentesco' => $this->input('responsavel_parentesco', ''),
            'responsavel_telefone' => $this->input('responsavel_telefone', ''),
            'responsavel_email' => $this->input('responsavel_email', ''),
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

    private function pacienteModel(): Paciente
    {
        return new Paciente();
    }
}
