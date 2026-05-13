<?php

namespace App\Controllers;

use App\Models\RelatorioPlantion;

/**
 * Controller — Relatório de Plantão
 *
 * Rotas esperadas (adicionar no arquivo de rotas do projeto):
 *   GET  /relatorio-plantao/{pacienteId}              → index()
 *   GET  /relatorio-plantao/{pacienteId}/{data}       → show()
 *   POST /relatorio-plantao/{id}/assinar              → assinar()
 */
class RelatorioPlantiontController extends BaseController
{
    private RelatorioPlantion $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new RelatorioPlantion();
    }

    // -------------------------------------------------------------------------
    // Listagem (index) — lista todos os relatórios, com paginação e busca
    // -------------------------------------------------------------------------

    public function index(): void
    {
        $page   = (int)   ($_GET['page']   ?? 1);
        $search = (string)($_GET['search'] ?? '');

        $result = $this->model->listForIndex($page, 15, $search);

        $this->render('relatorio_plantao/index', [
            'title'        => 'Relatórios de Plantão',
            'relatorios'   => $result['data'],
            'pagination'   => $result,
            'search'       => $search,
        ]);
    }

    // -------------------------------------------------------------------------
    // Visualização por paciente + data (show) — renderiza a tela principal
    // -------------------------------------------------------------------------

    public function show(int $pacienteId, string $data = ''): void
    {
        if ($data === '') {
            $data = date('Y-m-d');
        }

        // Valida formato da data
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $this->redirect('/relatorio-plantao/' . $pacienteId);
            return;
        }

        // Busca todos os turnos do dia para o paciente
        $turnos = $this->model->findByPacienteAndDate($pacienteId, $data);

        if (empty($turnos)) {
            $this->render('relatorio_plantao/show', [
                'title'       => 'Relatório de Plantão',
                'paciente'    => null,
                'plantaoData' => '{}',
                'dataAtual'   => $data,
                'pacienteId'  => $pacienteId,
            ]);
            return;
        }

        // Monta o paciente a partir do primeiro registro
        $primeiro = $turnos[0];
        $paciente = [
            'nome'        => $primeiro['paciente_nome'],
            'prontuario'  => $primeiro['paciente_id'],
            'diagnostico' => $primeiro['diagnostico'] ?? '',
            'iniciais'    => $this->iniciais($primeiro['paciente_nome']),
            'idade'       => $this->calcularIdade($primeiro['data_nascimento'] ?? ''),
        ];

        // Monta o array indexado por turno para o JS (window.PLANTAO_DATA)
        $plantaoData = [];
        foreach ($turnos as $turno) {
            $id    = (int) $turno['id'];
            $chave = $turno['turno']; // manha | tarde | noite

            $plantaoData[$chave] = [
                'turno'          => $chave,
                'label'          => $this->labelTurno($chave),
                'horario'        => $this->horarioTurno($chave),
                'icone'          => $this->iconeTurno($chave),
                'enfermeiro'     => $turno['enfermeiro_nome'],
                'coren'          => $turno['enfermeiro_coren'] ?? '',
                'status'         => $turno['status_turno'],
                'status_label'   => $this->labelStatus($turno['status_turno']),
                'assinado'       => (bool) $turno['assinado'],
                'sinais_vitais'  => $this->formatarSinais($this->model->getSinaisVitais($id)),
                'medicacoes'     => $this->formatarMedicacoes($this->model->getMedicacoes($id)),
                'evolucao'       => $turno['evolucao'] ?? '',
                'intercorrencias'=> $this->formatarIntercorrencias($this->model->getIntercorrencias($id)),
            ];
        }

        $this->render('relatorio_plantao/show', [
            'title'       => 'Relatório de Plantão — ' . $paciente['nome'],
            'paciente'    => $paciente,
            'plantaoData' => json_encode($plantaoData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG),
            'dataAtual'   => $data,
            'pacienteId'  => $pacienteId,
        ]);
    }

    // -------------------------------------------------------------------------
    // Assinatura — chamada via POST (pode ser AJAX)
    // -------------------------------------------------------------------------

    public function assinar(int $id): void
    {
        $sucesso = $this->model->assinar($id);

        // Responde JSON se a requisição for AJAX
        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $sucesso]);
            exit;
        }

        // Fallback: redireciona de volta
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/relatorio-plantao');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function iniciais(string $nome): string
    {
        $partes = explode(' ', trim($nome));
        $ini    = '';
        foreach (array_slice($partes, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini;
    }

    private function calcularIdade(string $dataNasc): int
    {
        if ($dataNasc === '') return 0;
        try {
            $nasc = new \DateTime($dataNasc);
            return (int) $nasc->diff(new \DateTime())->y;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function labelTurno(string $turno): string
    {
        return match($turno) {
            'manha' => 'Manhã',
            'tarde' => 'Tarde',
            'noite' => 'Noite',
            default => ucfirst($turno),
        };
    }

    private function horarioTurno(string $turno): string
    {
        return match($turno) {
            'manha' => '07:00 – 13:00',
            'tarde' => '13:00 – 19:00',
            'noite' => '19:00 – 07:00',
            default => '',
        };
    }

    private function iconeTurno(string $turno): string
    {
        return match($turno) {
            'manha' => '☀️',
            'tarde' => '🌤️',
            'noite' => '🌙',
            default => '🕐',
        };
    }

    private function labelStatus(string $status): string
    {
        return match($status) {
            'concluido'      => 'Concluído',
            'intercorrencia' => 'Intercorrência',
            'andamento'      => 'Em andamento',
            default          => ucfirst($status),
        };
    }

    private function formatarSinais(array $sinais): array
    {
        // Mapeia colunas do banco para o formato esperado pelo JS
        return array_map(fn($s) => [
            'label'   => $s['label']   ?? $s['tipo']   ?? '',
            'valor'   => $s['valor']   ?? '',
            'unidade' => $s['unidade'] ?? '',
            'status'  => $s['status']  ?? 'normal',
            'texto'   => $s['texto']   ?? '',
        ], $sinais);
    }

    private function formatarMedicacoes(array $meds): array
    {
        return array_map(fn($m) => [
            'nome'    => $m['nome']              ?? $m['medicamento']       ?? '',
            'via'     => $m['via']               ?? '',
            'horario' => $m['horario_previsto']  ?? $m['horario']           ?? '',
            'status'  => $m['status']            ?? 'pendente',
        ], $meds);
    }

    private function formatarIntercorrencias(array $lista): array
    {
        return array_map(fn($i) => [
            'descricao' => $i['descricao'] ?? '',
            'horario'   => $i['horario']   ?? '',
        ], $lista);
    }

    private function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }
}
