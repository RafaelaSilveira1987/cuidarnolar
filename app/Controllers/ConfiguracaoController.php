<?php

namespace App\Controllers;

use App\Models\EmpresaConfig;
use App\Models\TabelaPlantao;
use App\Models\Usuario;

class ConfiguracaoController extends BaseController
{
    public function index(): void
    {
        $this->redirect('/configuracoes/empresa');
    }

    public function empresa(): void
    {
        $this->view('configuracoes/empresa', [
            'pageTitle' => 'Configurações',
            'empresa' => (new EmpresaConfig())->atual(),
            'activeTab' => 'empresa',
        ]);
    }

    public function empresaSalvar(): void
    {
        (new EmpresaConfig())->salvar([
            'razao_social' => $this->input('razao_social', ''),
            'nome_fantasia' => $this->input('nome_fantasia', ''),
            'cnpj' => $this->input('cnpj', ''),
            'inscricao_estadual' => $this->input('inscricao_estadual', ''),
            'endereco' => $this->input('endereco', ''),
            'cidade' => $this->input('cidade', ''),
            'estado' => $this->input('estado', ''),
            'cep' => $this->input('cep', ''),
            'telefone' => $this->input('telefone', ''),
            'email' => $this->input('email', ''),
            'responsavel_contrato' => $this->input('responsavel_contrato', ''),
            'observacoes_contrato' => $this->input('observacoes_contrato', ''),
        ]);

        $this->flash('success', 'Dados da empresa atualizados com sucesso. Novos contratos passam a puxar essas informações.');
        $this->redirect('/configuracoes/empresa');
    }

    public function plantoes(): void
    {
        $model = new TabelaPlantao();
        $editarId = (int)$this->input('editar', 0);
        $registro = $editarId > 0 ? ($model->buscar($editarId) ?: []) : [];

        $this->view('configuracoes/plantoes', [
            'pageTitle' => 'Tabela de plantões',
            'activeTab' => 'plantoes',
            'plantoes' => $model->listar(false),
            'registro' => $registro,
            'errors' => [],
        ]);
    }

    public function plantaoSalvar(): void
    {
        $model = new TabelaPlantao();
        $id = (int)$this->input('id', 0);
        $data = $this->plantaoInput();
        $errors = $model->validar($data);

        if ($errors !== []) {
            $this->view('configuracoes/plantoes', [
                'pageTitle' => 'Tabela de plantões',
                'activeTab' => 'plantoes',
                'plantoes' => $model->listar(false),
                'registro' => $data + ['id' => $id],
                'errors' => $errors,
            ]);
            return;
        }

        $model->salvar($data, $id > 0 ? $id : null);
        $this->flash('success', $id > 0 ? 'Regra de plantão atualizada.' : 'Regra de plantão cadastrada.');
        $this->redirect('/configuracoes/plantoes');
    }

    public function plantaoAlternar(string $id): void
    {
        (new TabelaPlantao())->alternarAtivo((int)$id);
        $this->flash('success', 'Status da regra de plantão atualizado.');
        $this->redirect('/configuracoes/plantoes');
    }

    public function permissoes(): void
    {
        $usuarios = [];
        try {
            $usuarios = (new Usuario())->all('id', 'ASC');
        } catch (\Throwable) {
            $usuarios = [];
        }

        $this->view('configuracoes/permissoes', [
            'pageTitle' => 'Permissões de usuários',
            'activeTab' => 'permissoes',
            'usuarios' => $usuarios,
        ]);
    }

    private function plantaoInput(): array
    {
        return [
            'titulo' => $this->input('titulo', ''),
            'tipo_plantao' => $this->input('tipo_plantao', '12h'),
            'periodo' => $this->input('periodo', 'Diurno'),
            'hora_inicio' => $this->input('hora_inicio', ''),
            'hora_fim' => $this->input('hora_fim', ''),
            'valor_cuidador' => $this->input('valor_cuidador', ''),
            'valor_extra' => $this->input('valor_extra', ''),
            'descricao' => $this->input('descricao', ''),
            'ativo' => $this->input('ativo', ''),
            'ordem' => $this->input('ordem', '0'),
        ];
    }
}
