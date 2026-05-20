<?php

/**
 * app/Controllers/RelatorioPlantaoController_snippet.php
 *
 * SNIPPETS para integrar no seu RelatorioPlantaoController.
 *
 * MÉTODOS INCLUÍDOS:
 * - paciente(string $pacienteUuid)
 * - show(string $plantaoId)
 * - novo(string $pacienteUuid)
 * - editar(string $plantaoId)
 *
 * IMPORTANTE:
 * 1. Copie apenas os métodos para dentro do seu controller real.
 * 2. Ajuste os nomes dos métodos do model caso sejam diferentes.
 * 3. O método $this->view() deve ser o mesmo utilizado pelo seu framework.
 */

namespace App\Controllers;

class RelatorioPlantaoController extends BaseController
{
    /**
     * Lista os relatórios de um paciente com filtro por data.
     *
     * Rota sugerida:
     * GET /relatorio-plantao/paciente/{uuid}
     */
    public function paciente(string $pacienteUuid): void
    {
        // Carregar models
        $pacienteModel  = new \App\Models\Paciente();
        $relatorioModel = new \App\Models\RelatorioPlantao();

        // Buscar paciente
        $paciente = $pacienteModel->findByUuid($pacienteUuid);

        if (!$paciente) {
            http_response_code(404);
            exit('Paciente não encontrado.');
        }

        // Buscar todos os relatórios do paciente.
        // A própria view fará o filtro por data via ?date=YYYY-MM-DD
        $plantoes = $relatorioModel->findByPaciente($paciente['id']);

        // Renderizar
        $this->view('relatorio_plantao/paciente', [
            'pageTitle' => 'Relatório de Plantão',
            'paciente'  => $paciente,
            'plantoes'  => $plantoes,
        ]);
    }

    /**
     * Exibe um relatório individual.
     *
     * Rota sugerida:
     * GET /relatorio-plantao/plantao/{uuid}
     */
    public function show(string $plantaoId): void
    {
        $relatorioModel = new \App\Models\RelatorioPlantao();

        // Buscar por UUID primeiro; se não encontrar, tenta por ID numérico
        $relatorio = $relatorioModel->findByUuid($plantaoId);

        if (!$relatorio && ctype_digit($plantaoId)) {
            $relatorio = $relatorioModel->find((int)$plantaoId);
        }

        if (!$relatorio) {
            http_response_code(404);
            exit('Relatório não encontrado.');
        }

        // Carregar paciente, se houver relacionamento
        $paciente = [];

        if (!empty($relatorio['paciente_id'])) {
            $pacienteModel = new \App\Models\Paciente();
            $paciente = $pacienteModel->find((int)$relatorio['paciente_id']) ?? [];
        }

        $this->view('relatorio_plantao/show', [
            'pageTitle' => 'Visualizar Relatório',
            'relatorio' => $relatorio,
            'paciente'  => $paciente,
        ]);
    }

    /**
     * Novo relatório.
     *
     * Rota sugerida:
     * GET|POST /relatorio-plantao/paciente/{uuid}/novo
     */
    public function novo(string $pacienteUuid): void
    {
        $pacienteModel  = new \App\Models\Paciente();
        $relatorioModel = new \App\Models\RelatorioPlantao();

        $paciente = $pacienteModel->findByUuid($pacienteUuid);

        if (!$paciente) {
            http_response_code(404);
            exit('Paciente não encontrado.');
        }

        // POST = salvar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $_POST;

            // Vincular ao paciente
            $dados['paciente_id'] = $paciente['id'];

            // Ajustar campos datetime-local (2026-01-01T08:00)
            if (!empty($dados['data_inicio'])) {
                $dados['data_inicio'] = str_replace('T', ' ', $dados['data_inicio']);
            }

            if (!empty($dados['data_fim'])) {
                $dados['data_fim'] = str_replace('T', ' ', $dados['data_fim']);
            }

            // Inserir
            $novoId = $relatorioModel->insert($dados);

            // Redirecionar para visualização
            if ($novoId) {
                header('Location: ' . BASE_URL . '/relatorio-plantao/plantao/' . $novoId);
                exit;
            }
        }

        // Exibir formulário
        $this->view('relatorio_plantao/form', [
            'pageTitle' => 'Novo Relatório',
            'paciente'  => $paciente,
            'relatorio' => [],
        ]);
    }

    /**
     * Editar relatório existente.
     *
     * Rota sugerida:
     * GET|POST /relatorio-plantao/plantao/{uuid}/editar
     */
    public function editar(string $plantaoId): void
    {
        $relatorioModel = new \App\Models\RelatorioPlantao();

        // Buscar por UUID ou ID
        $relatorio = $relatorioModel->findByUuid($plantaoId);

        if (!$relatorio && ctype_digit($plantaoId)) {
            $relatorio = $relatorioModel->find((int)$plantaoId);
        }

        if (!$relatorio) {
            http_response_code(404);
            exit('Relatório não encontrado.');
        }

        // Carregar paciente
        $paciente = [];

        if (!empty($relatorio['paciente_id'])) {
            $pacienteModel = new \App\Models\Paciente();
            $paciente = $pacienteModel->find((int)$relatorio['paciente_id']) ?? [];
        }

        // POST = salvar alterações
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = $_POST;

            // Ajustar datetime-local
            if (!empty($dados['data_inicio'])) {
                $dados['data_inicio'] = str_replace('T', ' ', $dados['data_inicio']);
            }

            if (!empty($dados['data_fim'])) {
                $dados['data_fim'] = str_replace('T', ' ', $dados['data_fim']);
            }

            // Atualizar
            $relatorioModel->update($relatorio['id'], $dados);

            // Redirecionar para visualização
            header('Location: ' . BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($plantaoId));
            exit;
        }

        // Exibir formulário
        $this->view('relatorio_plantao/form', [
            'pageTitle' => 'Editar Relatório',
            'paciente'  => $paciente,
            'relatorio' => $relatorio,
        ]);
    }
}