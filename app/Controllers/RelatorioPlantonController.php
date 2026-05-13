<?php

namespace App\Controllers;

use App\Models\RelatorioPlantion;

class RelatorioPlantonController extends ResourceController
{
    protected string $modelClass = RelatorioPlantion::class;
    protected string $routeBase = '/relatorio-plantao';
    protected string $viewTitle = 'Relatório de Plantão';
    protected string $singularTitle = 'Relatório de Plantão';

    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $pacienteBusca = trim((string) $this->input('busca', ''));
        $result = $this->relatorioPlantonModel()->listForIndex($page, 15, $pacienteBusca);

        $this->view('relatorio_plantao/index', [
            'pageTitle' => $this->viewTitle,
            'title' => $this->viewTitle,
            'routeBase' => $this->routeBase,
            'rows' => $result['data'],
            'pagination' => $result,
            'search' => $pacienteBusca,
        ]);
    }

    public function show(string $id): void
    {
        $relatorio = $this->relatorioPlantonModel()->findForShow((int) $id);

        if (!$relatorio) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Relatório não encontrado.'], 'layouts/blank');
            return;
        }

        $this->view('relatorio_plantao/show', [
            'pageTitle' => $this->singularTitle,
            'title' => $this->singularTitle,
            'routeBase' => $this->routeBase,
            'relatorio' => $relatorio,
        ]);
    }

    public function diarioPaciente(string $pacienteId): void
    {
        $data = $this->input('data', date('Y-m-d'));
        $relatorios = $this->relatorioPlantonModel()->findByPacienteAndData((int) $pacienteId, $data);

        if (empty($relatorios)) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Nenhum relatório encontrado para esta data.'], 'layouts/blank');
            return;
        }

        $paciente = $this->relatorioPlantonModel()->getPaciente((int) $pacienteId);

        $this->view('relatorio_plantao/diario_paciente', [
            'pageTitle' => 'Diário - ' . $paciente['nome_completo'],
            'title' => 'Diário de ' . $paciente['nome_completo'],
            'paciente' => $paciente,
            'relatorios' => $relatorios,
            'dataConsulta' => $data,
            'routeBase' => $this->routeBase,
        ]);
    }

    public function assinarRelatorio(string $id): void
    {
        $relatorio = $this->relatorioPlantonModel()->findForShow((int) $id);

        if (!$relatorio) {
            $this->flash('error', 'Relatório não encontrado.');
            $this->redirect($this->routeBase);
            return;
        }

        if ($relatorio['assinado']) {
            $this->flash('warning', 'Relatório já está assinado.');
            $this->redirect($this->routeBase . '/' . $id);
            return;
        }

        $enfermeiroCoren = $this->input('enfermeiro_coren', '');
        if (trim($enfermeiroCoren) === '') {
            $this->flash('error', 'Informe o COREN do profissional.');
            $this->redirect($this->routeBase . '/' . $id);
            return;
        }

        $this->relatorioPlantonModel()->assinarRelatorio((int) $id, $enfermeiroCoren);
        $this->flash('success', 'Relatório assinado com sucesso.');
        $this->redirect($this->routeBase . '/' . $id);
    }

    private function relatorioPlantonModel(): RelatorioPlantion
    {
        return new RelatorioPlantion();
    }
}
