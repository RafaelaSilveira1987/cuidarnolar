<?php

namespace App\Controllers;

use App\Models\Escala;
use App\Models\EscalaOcorrencia;
use App\Models\EscalaSubstituicao;
use App\Models\EscalaAprovacao;

class EscalaController extends BaseController
{
    private Escala $escala;
    private EscalaOcorrencia $escalaOcorrencia;
    private EscalaSubstituicao $escalaSubstituicao;
    private EscalaAprovacao $escalaAprovacao;

    public function __construct()
    {
        parent::__construct();

        $this->escala             = new Escala();
        $this->escalaOcorrencia   = new EscalaOcorrencia();
        $this->escalaSubstituicao = new EscalaSubstituicao();
         $this->escalaAprovacao = new EscalaAprovacao();
    }

    // =========================================================
    // GET /escalas — Central de Cobertura
    // =========================================================
    public function index(): void
    {
        $modo = ($_GET['modo'] ?? 'semana') === 'mes' ? 'mes' : 'semana';
        $dataBase = $this->sanitizarData($_GET['periodo'] ?? $_GET['semana'] ?? null) ?: date('Y-m-d');

        [$inicioPeriodo, $fimPeriodo, $dias, $periodoLabel, $periodoAnterior, $periodoProximo] = $this->resolverPeriodo($modo, $dataBase);

        $pacienteFiltroUuid = $this->sanitizarUuid($_GET['paciente'] ?? null);
        $cuidadorFiltroUuid = $this->sanitizarUuid($_GET['cuidador'] ?? null);

        $cuidadorFiltro = null;
        $cuidadorId = null;

        if (\is_cuidador_scope()) {
            $cuidadorId = \current_cuidador_id();
            $cuidadorFiltroUuid = null;
        } elseif ($cuidadorFiltroUuid) {
            $cuidadorFiltro = $this->escala->buscarCuidadorPorUuid($cuidadorFiltroUuid);
            $cuidadorId = $cuidadorFiltro['id'] ?? null;
        }

        $todosPacientesOperacionais = $this->escala->listarPacientesOperacionais(null, $cuidadorId);
        $pacienteSelecionado = null;

        if ($pacienteFiltroUuid) {
            foreach ($todosPacientesOperacionais as $pac) {
                if ((string)($pac['uuid'] ?? '') === (string)$pacienteFiltroUuid || (string)($pac['id'] ?? '') === (string)$pacienteFiltroUuid) {
                    $pacienteSelecionado = $pac;
                    break;
                }
            }
        }

        if (!$pacienteSelecionado && !empty($todosPacientesOperacionais)) {
            // A tela de escala fica intencionalmente focada em um paciente por vez.
            $pacienteSelecionado = $todosPacientesOperacionais[0];
            $pacienteFiltroUuid = (string)($pacienteSelecionado['uuid'] ?? $pacienteSelecionado['id'] ?? '');
        }

        $pacienteId = $pacienteSelecionado ? (int)$pacienteSelecionado['id'] : null;
        $pacientesOperacionais = $pacienteId
            ? $this->escala->listarPacientesOperacionais($pacienteId, $cuidadorId)
            : [];

        $ocorrencias = $this->escalaOcorrencia->porSemana($inicioPeriodo, $fimPeriodo, $pacienteId, $cuidadorId);
        $substituicoes = $this->escalaSubstituicao->porSemana($inicioPeriodo, $fimPeriodo);

        $filtros = [
            'paciente' => $pacienteFiltroUuid,
            'cuidador' => $cuidadorFiltroUuid,
            'modo' => $modo,
            'periodo' => $dataBase,
        ];

        $cobertura = $this->montarCobertura($pacientesOperacionais, $ocorrencias, $substituicoes, $dias, $filtros);
        $resumo = $this->calcularResumo($cobertura, $this->escala->listaCuidadores());
        $alertas = $this->gerarAlertas($cobertura, $substituicoes);

        $aprovacaoPeriodo = null;
        $escalaBaseId = (int)($pacientesOperacionais[0]['escala_base_id'] ?? $pacienteSelecionado['escala_base_id'] ?? 0);
        if ($pacienteId && $escalaBaseId) {
            $aprovacaoPeriodo = $this->escalaAprovacao->buscarPorPeriodo(
                $escalaBaseId,
                $pacienteId,
                $inicioPeriodo,
                $fimPeriodo
            );
        }

        $historicoEscala = $this->montarHistoricoEscala(
            $substituicoes,
            $ocorrencias,
            $aprovacaoPeriodo
        );

        $this->view('escalas/index', [
            'dias' => $dias,
            'modo' => $modo,
            'periodoLabel' => $periodoLabel,
            'periodoInicio' => $inicioPeriodo,
            'periodoFim' => $fimPeriodo,
            'periodoAnterior' => $periodoAnterior,
            'periodoProximo' => $periodoProximo,
            'pacientes' => \is_cuidador_scope() ? $todosPacientesOperacionais : $this->escala->listarPacientesOperacionais(null, null),
            'pacienteSelecionado' => $pacientesOperacionais[0] ?? $pacienteSelecionado,
            'colaboradores' => \is_cuidador_scope() ? $this->filtrarColaboradorAtual($this->escala->listaCuidadores()) : $this->escala->listaCuidadores(),
            'cobertura' => $cobertura,
            'resumo' => $resumo,
            'alertas' => $alertas,
            'filtros' => $filtros,
            'aprovacaoPeriodo' => $aprovacaoPeriodo,
            'historicoEscala' => $historicoEscala,
        ]);
    }

    public function excluir(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/escala');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->flash('error', 'Plantão não informado para exclusão.');
            $this->redirect('/escala');
            return;
        }

        $this->escalaOcorrencia->delete($id);
        $this->flash('success', 'Plantão removido da escala.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/escala');
    }

    public function aprovar(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/escala');
            return;
        }

        $pacienteUuid = $this->sanitizarUuid($_POST['paciente_uuid'] ?? null);
        $modo = ($_POST['modo'] ?? 'semana') === 'mes' ? 'mes' : 'semana';
        $periodo = $this->sanitizarData($_POST['periodo'] ?? null) ?: date('Y-m-d');

        if (!$pacienteUuid) {
            $this->flash('error', 'Selecione um paciente para aprovar a escala.');
            $this->redirect('/escala');
            return;
        }

        $paciente = $this->escala->buscarPacientePorUuid($pacienteUuid);
        if (!$paciente) {
            $this->flash('error', 'Paciente não localizado para aprovar escala.');
            $this->redirect('/escala');
            return;
        }

        [$inicioPeriodo, $fimPeriodo, $dias] = $this->resolverPeriodo($modo, $periodo);

        $pacientes = $this->escala->listarPacientesOperacionais((int)$paciente['id'], null);
        if (empty($pacientes)) {
            $this->flash('error', 'Este paciente ainda não possui contrato/escala base operacional.');
            $this->redirect('/escala?paciente=' . rawurlencode($pacienteUuid) . '&modo=' . $modo . '&periodo=' . rawurlencode($periodo));
            return;
        }

        $ocorrencias = $this->escalaOcorrencia->porSemana($inicioPeriodo, $fimPeriodo, (int)$paciente['id'], null);
        $cobertura = $this->montarCobertura($pacientes, $ocorrencias, [], $dias, []);

        $confirmados = 0;
        $escalaBaseId = (int)($pacientes[0]['escala_base_id'] ?? 0);

        foreach (($cobertura[0]['turnos'] ?? []) as $turno) {
            foreach (($turno['plantoes'] ?? []) as $plantao) {
                if (($plantao['status'] ?? '') !== 'sugerido') {
                    continue;
                }

                $cuidadorUuid = $plantao['colaborador']['uuid'] ?? null;
                if (!$cuidadorUuid || !$escalaBaseId) {
                    continue;
                }

                $cuidador = $this->escala->buscarCuidadorPorUuid((string)$cuidadorUuid);
                if (!$cuidador) {
                    continue;
                }

                $inicioPlantao = $this->combinarDataHora(
                    (string)$plantao['data'],
                    (string)$plantao['hora_inicio']
                );

                $fimPlantao = $this->combinarDataHora(
                    (string)$plantao['data'],
                    (string)$plantao['hora_fim']
                );

                if (strtotime($fimPlantao) <= strtotime($inicioPlantao)) {
                    $fimPlantao = date('Y-m-d H:i:s', strtotime($fimPlantao . ' +1 day'));
                }

                $this->escalaOcorrencia->createRecord([
                    'escala_base_id' => $escalaBaseId,
                    'paciente_id' => (int)$paciente['id'],
                    'cuidador_id' => (int)$cuidador['id'],
                    'data_plantao' => $plantao['data'],
                    'inicio' => $inicioPlantao,
                    'fim' => $fimPlantao,
                    'tipo_plantao' => $this->calcularTipoPlantao($inicioPlantao, $fimPlantao),
                    'status' => 'confirmado',
                    'origem' => 'Manual',
                    'observacoes' => null,
                ]);

                $confirmados++;
            }
        }

        $this->escalaAprovacao->registrar(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo,
            $confirmados
        );

        $this->flash('success', $confirmados > 0
            ? "Escala aprovada. {$confirmados} plantão(ões) foram confirmados."
            : 'Escala aprovada. Não havia plantões novos para confirmar neste período.');

        $this->redirect('/escala?paciente=' . rawurlencode($pacienteUuid) . '&modo=' . $modo . '&periodo=' . rawurlencode($periodo));
    }


    public function fechar(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/escala');
            return;
        }

        $pacienteUuid = $this->sanitizarUuid($_POST['paciente_uuid'] ?? null);
        $modo = ($_POST['modo'] ?? 'semana') === 'mes' ? 'mes' : 'semana';
        $periodo = $this->sanitizarData($_POST['periodo'] ?? null) ?: date('Y-m-d');
        $retorno = '/escala' . ($pacienteUuid
            ? '?paciente=' . rawurlencode($pacienteUuid) . '&modo=' . $modo . '&periodo=' . rawurlencode($periodo)
            : '');

        if (!$pacienteUuid) {
            $this->flash('error', 'Selecione um paciente para fechar a escala.');
            $this->redirect('/escala');
            return;
        }

        $paciente = $this->escala->buscarPacientePorUuid($pacienteUuid);
        if (!$paciente) {
            $this->flash('error', 'Paciente não localizado para fechar escala.');
            $this->redirect('/escala');
            return;
        }

        [$inicioPeriodo, $fimPeriodo] = $this->resolverPeriodo($modo, $periodo);

        $pacientes = $this->escala->listarPacientesOperacionais((int)$paciente['id'], null);
        $escalaBaseId = (int)($pacientes[0]['escala_base_id'] ?? $paciente['escala_base_id'] ?? 0);

        if ($escalaBaseId <= 0) {
            $this->flash('error', 'Este paciente ainda não possui escala base operacional para fechamento.');
            $this->redirect($retorno);
            return;
        }

        $aprovacao = $this->escalaAprovacao->buscarPorPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        $status = strtolower((string)($aprovacao['status'] ?? ''));
        $statusAprovado = in_array($status, ['aprovada', 'aprovado', 'confirmado', 'confirmada', 'ok'], true);
        $statusFechado = in_array($status, ['fechada', 'finalizada'], true);

        if (!$aprovacao) {
            $this->flash('error', 'Primeiro confirme/aprove a escala deste período. Depois faça o fechamento.');
            $this->redirect($retorno);
            return;
        }

        if ($statusFechado) {
            $this->flash('info', 'Este período já está fechado. O financeiro dos cuidadores já pode ser gerado.');
            $this->redirect($retorno);
            return;
        }

        if (!$statusAprovado) {
            $this->flash('error', 'A escala precisa estar aprovada para permitir o fechamento.');
            $this->redirect($retorno);
            return;
        }

        $alterados = $this->escalaOcorrencia->finalizarPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        $totalFinalizados = $this->escalaOcorrencia->contarFinalizadosPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        if ($totalFinalizados <= 0) {
            $this->flash('error', 'Nenhum plantão confirmado foi encontrado para finalizar neste período.');
            $this->redirect($retorno);
            return;
        }

        $this->escalaAprovacao->fecharPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo,
            $totalFinalizados
        );

        $this->flash('success', "Escala fechada com sucesso. {$alterados} plantão(ões) foram finalizados. Agora já dá para gerar o financeiro dos cuidadores.");
        $this->redirect($retorno);
    }


    public function cancelarFechamento(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/escala');
            return;
        }

        $pacienteUuid = $this->sanitizarUuid($_POST['paciente_uuid'] ?? null);
        $modo = ($_POST['modo'] ?? 'semana') === 'mes' ? 'mes' : 'semana';
        $periodo = $this->sanitizarData($_POST['periodo'] ?? null) ?: date('Y-m-d');
        $retorno = '/escala' . ($pacienteUuid
            ? '?paciente=' . rawurlencode($pacienteUuid) . '&modo=' . $modo . '&periodo=' . rawurlencode($periodo)
            : '');

        if (!$pacienteUuid) {
            $this->flash('error', 'Selecione um paciente para cancelar o fechamento.');
            $this->redirect('/escala');
            return;
        }

        $paciente = $this->escala->buscarPacientePorUuid($pacienteUuid);
        if (!$paciente) {
            $this->flash('error', 'Paciente não localizado para cancelar fechamento.');
            $this->redirect('/escala');
            return;
        }

        [$inicioPeriodo, $fimPeriodo] = $this->resolverPeriodo($modo, $periodo);
        $pacientes = $this->escala->listarPacientesOperacionais((int)$paciente['id'], null);
        $escalaBaseId = (int)($pacientes[0]['escala_base_id'] ?? $paciente['escala_base_id'] ?? 0);

        if ($escalaBaseId <= 0) {
            $this->flash('error', 'Este paciente ainda não possui escala base operacional.');
            $this->redirect($retorno);
            return;
        }

        $aprovacao = $this->escalaAprovacao->buscarPorPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        $status = strtolower((string)($aprovacao['status'] ?? ''));
        if (!$aprovacao || !in_array($status, ['fechada', 'finalizada'], true)) {
            $this->flash('info', 'Este período não está fechado.');
            $this->redirect($retorno);
            return;
        }

        $bloqueadosFinanceiro = $this->escalaOcorrencia->contarFinalizadosComFinanceiroPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        if ($bloqueadosFinanceiro > 0) {
            $this->flash('error', 'Não é possível cancelar o fechamento porque já existe financeiro gerado para ' . $bloqueadosFinanceiro . ' plantão(ões). Cancele/estorne os lançamentos financeiros antes.');
            $this->redirect($retorno);
            return;
        }

        $reabertos = $this->escalaOcorrencia->cancelarFinalizacaoPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo
        );

        $this->escalaAprovacao->cancelarFechamentoPeriodo(
            $escalaBaseId,
            (int)$paciente['id'],
            $inicioPeriodo,
            $fimPeriodo,
            $reabertos
        );

        $this->flash('success', "Fechamento cancelado. {$reabertos} plantão(ões) voltaram para confirmado e podem ser ajustados novamente.");
        $this->redirect($retorno);
    }

    public function mover(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        $this->trocar();
    }

    public function paciente(string $uuid): void
    {
        $uuid = $this->sanitizarUuid($uuid);
        $this->redirect('/escala' . ($uuid ? '?paciente=' . rawurlencode($uuid) : ''));
    }

    public function colaborador(string $uuid): void
    {
        $uuid = $this->sanitizarUuid($uuid);
        $this->redirect('/escala' . ($uuid ? '?cuidador=' . rawurlencode($uuid) : ''));
    }


    private function bloquearCuidadorEmEdicaoEscala(): bool
    {
        if (!\is_cuidador_scope()) {
            return false;
        }

        $this->flash('error', 'Seu perfil de cuidador permite visualizar a própria escala, mas não alterar a escala operacional.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/escala');
        return true;
    }

    private function filtrarColaboradorAtual(array $colaboradores): array
    {
        $cuidadorId = \current_cuidador_id();
        if (!$cuidadorId) {
            return [];
        }

        return array_values(array_filter($colaboradores, static fn(array $c): bool => (int)($c['id'] ?? 0) === $cuidadorId));
    }

    // =========================================================
    // Helpers privados
    // =========================================================

    /**
     * Valida e retorna um UUID v4 ou string numérica (id legado), ou null.
     */
    private function sanitizarUuid(mixed $valor): ?string
    {
        if (!isset($valor) || !is_scalar($valor)) return null;
        $str = trim((string) $valor);
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $str)) {
            return strtolower($str);
        }
        if (preg_match('/^[0-9]+$/', $str)) return $str;
        return null;
    }

    private function calcularTipoPlantao(?string $inicio, ?string $fim): string
    {
        if (empty($inicio) || empty($fim)) {
            return '12h';
        }

        $inicioTs = strtotime($inicio);
        $fimTs = strtotime($fim);

        if (!$inicioTs || !$fimTs) {
            return '12h';
        }

        $horas = ($fimTs - $inicioTs) / 3600;

        if ($horas < 0) {
            $horas += 24;
        }

        if ($horas <= 6.5) {
            return '6h';
        }

        if ($horas <= 8.5) {
            return '8h';
        }

        if ($horas <= 12.5) {
            return '12h';
        }

        return '24h';
    }

    /**
     * Valida e retorna uma data no formato Y-m-d ou null.
     */
    private function sanitizarData(mixed $valor): ?string
    {
        if (!isset($valor) || !is_scalar($valor)) return null;
        $str = trim((string) $valor);
        if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $str) && strtotime($str) !== false) {
            return $str;
        }
        return null;
    }

    /**
     * Resolve a segunda-feira da semana a partir de uma data qualquer.
     * Se nenhuma data for passada, usa a semana atual.
     */
    private function resolverDomingo(?string $data): string
    {
        $ts = $data ? strtotime($data) : time();
        $dow = (int)date('w', $ts); // 0=dom … 6=sáb
        return date('Y-m-d', strtotime('-' . $dow . ' days', $ts));
    }

    /**
     * Gera array com os 7 dias da semana a partir do domingo.
     *
     * @return array [{date, label, num}, ...]
     */
    private function gerarDias(string $domingo): array
    {
        $labels = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
        $dias   = [];
        for ($i = 0; $i < 7; $i++) {
            $ts      = strtotime("+{$i} days", strtotime($domingo));
            $dias[]  = [
                'date'  => date('Y-m-d', $ts),
                'label' => $labels[$i],
                'num'   => date('d', $ts),
            ];
        }
        return $dias;
    }

    /**
     * Monta a estrutura de cobertura agrupada por paciente.
     *
     * Retorna array de pacientes, cada um com:
     *   id, nome, iniciais, cor_avatar, cor_avatar_t,
     *   endereco, tipo_contrato, cobertura_pct, turnos[]
     *
     * Cada turno tem: label, icone, plantoes[7]
     * Cada plantão tem: data, escala_id, colaborador_id,
     *   colaborador, inicio, fim, status, sub_nome
     */
    private function montarCobertura(array $pacientes, array $ocorrencias, array $substituicoes, array $dias, array $filtros = []): array
    {
        $porPacienteTurnoData = [];
        $porPacienteData = [];

        foreach ($ocorrencias as $ocorrencia) {
            $pacienteId = (int)($ocorrencia['paciente_id'] ?? 0);
            $data = (string)($ocorrencia['data_plantao'] ?? '');

            if (!$pacienteId || $data === '') {
                continue;
            }

            $metaTurno = $this->turnoMetaPorHorario(
                (string)($ocorrencia['inicio'] ?? $ocorrencia['hora_inicio'] ?? ''),
                (string)($ocorrencia['fim'] ?? $ocorrencia['hora_fim'] ?? ''),
                (string)($ocorrencia['tipo_plantao'] ?? '')
            );

            $turno = (string)($metaTurno['codigo'] ?? 'personalizado');

            // Guarda lista, não item único. Em cobertura 24h existem dois plantões 12h
            // no mesmo dia: diurno e noturno. Antes o segundo sobrescrevia o primeiro.
            $porPacienteTurnoData[$pacienteId][$turno][$data][] = $ocorrencia;
            $porPacienteData[$pacienteId][$data][] = $ocorrencia;
        }

        $substituicoesIndex = [];
        foreach ($substituicoes as $sub) {
            $origem = (int)($sub['cuidador_original_id'] ?? 0);
            $data = (string)($sub['data_inicio'] ?? $sub['data_plantao'] ?? '');
            if ($origem && $data !== '') {
                $substituicoesIndex[$origem][$data][] = $sub;
            }
        }

        $resultado = [];

        foreach ($pacientes as $paciente) {
            $pacienteId = (int)($paciente['id'] ?? 0);
            $tipoCobertura = (string)($paciente['tipo_cobertura'] ?? $paciente['tipo_contrato'] ?? '12h');
            $turnos = $this->turnosPorCobertura($tipoCobertura);
            $equipe = $this->equipePaciente($paciente);

            if (empty($equipe) && !empty($paciente['cuidador_referencia_id'])) {
                $equipe[] = [
                    'id' => (int)$paciente['cuidador_referencia_id'],
                    'uuid' => $paciente['cuidador_referencia_uuid'] ?? null,
                    'nome' => $paciente['cuidador_referencia_nome'] ?? 'Cuidador referência',
                    'cor' => $paciente['cuidador_referencia_cor'] ?? null,
                ];
            }

            $turnosMontados = [];
            foreach ($turnos as $turnoCodigo => $turnoInfo) {
                $turnosMontados[$turnoCodigo] = [
                    'label' => $turnoInfo['label'],
                    'inicio' => $turnoInfo['inicio'],
                    'fim' => $turnoInfo['fim'],
                    'plantoes' => [],
                ];

                foreach ($dias as $indice => $dia) {
                    $data = $dia['data'];

                    if (!$this->diaRespeitaEscala($paciente, $data)) {
                        $turnosMontados[$turnoCodigo]['plantoes'][$data] = [
                            'status' => 'fora_vigencia',
                            'status_label' => 'Fora da vigência',
                            'data' => $data,
                            'turno_codigo' => $turnoCodigo,
                            'turno_label' => $turnoInfo['label'],
                            'hora_inicio' => $turnoInfo['inicio'],
                            'hora_fim' => $turnoInfo['fim'],
                            'colaborador' => null,
                        ];
                        continue;
                    }

                    $ocorrencia = $this->ocorrenciaCompativel(
                        $porPacienteTurnoData[$pacienteId][$turnoCodigo][$data] ?? [],
                        $data,
                        $turnoInfo['inicio'],
                        $turnoInfo['fim']
                    );

                    if (!$ocorrencia) {
                        $ocorrencia = $this->ocorrenciaCompativel(
                            $porPacienteData[$pacienteId][$data] ?? [],
                            $data,
                            $turnoInfo['inicio'],
                            $turnoInfo['fim']
                        );
                    }

                    $cuidador = null;
                    $status = 'vago';
                    $statusLabel = 'Vago';
                    $escalaId = null;
                    $observacoes = null;

                    if ($ocorrencia) {
                        $statusOriginal = (string)($ocorrencia['status'] ?? 'confirmado');
                        $status = in_array($statusOriginal, ['pendente', 'cancelado'], true) ? $statusOriginal : 'ok';
                        $statusLabel = match ($status) {
                            'pendente' => 'Pendente',
                            'cancelado' => 'Cancelado',
                            default => 'OK',
                        };
                        $escalaId = $ocorrencia['id'] ?? null;
                        $observacoes = $ocorrencia['observacoes'] ?? null;

                        if (!empty($ocorrencia['cuidador_id'])) {
                            $cuidador = [
                                'id' => (int)$ocorrencia['cuidador_id'],
                                'uuid' => $ocorrencia['cuidador_uuid'] ?? null,
                                'nome' => $ocorrencia['cuidador_nome'] ?? 'Cuidador',
                                'cor' => $ocorrencia['cuidador_cor'] ?? null,
                            ];
                        }
                    } elseif (!empty($equipe)) {
                        $cuidador = $equipe[$indice % count($equipe)];
                        $status = 'sugerido';
                        $statusLabel = 'Sugerido';
                    }

                    $temSubstituicao = false;
                    if ($cuidador && !empty($substituicoesIndex[(int)$cuidador['id']][$data])) {
                        $temSubstituicao = true;
                        $status = 'substituido';
                        $statusLabel = 'Substituído';
                    }

                    $turnosMontados[$turnoCodigo]['plantoes'][$data] = [
                        'status' => $status,
                        'status_label' => $statusLabel,
                        'data' => $data,
                        'turno_codigo' => $turnoCodigo,
                        'turno_label' => $turnoInfo['label'],
                        'hora_inicio' => $turnoInfo['inicio'],
                        'hora_fim' => $turnoInfo['fim'],
                        'colaborador' => $cuidador,
                        'escala_id' => $escalaId,
                        'observacoes' => $observacoes,
                        'tem_substituicao' => $temSubstituicao,
                    ];
                }
            }

            $resultado[] = [
                'paciente' => $paciente,
                'turnos' => $turnosMontados,
            ];
        }

        return $resultado;
    }

    private function resolverPeriodo(string $modo, string $dataBase): array
    {
        $base = new \DateTimeImmutable($dataBase ?: date('Y-m-d'));

        if ($modo === 'mes') {
            $inicio = $base->modify('first day of this month');
            $fim = $base->modify('last day of this month');
            $label = $this->nomeMes((int)$base->format('n')) . ' de ' . $base->format('Y');
            $anterior = $base->modify('-1 month')->format('Y-m-01');
            $proximo = $base->modify('+1 month')->format('Y-m-01');

            return [
                $inicio->format('Y-m-d'),
                $fim->format('Y-m-d'),
                $this->gerarDiasDoPeriodo($inicio, $fim),
                $label,
                $anterior,
                $proximo,
            ];
        }

        $inicio = $base->modify('last sunday');
        if ($base->format('w') === '0') {
            $inicio = $base;
        }
        $fim = $inicio->modify('+6 days');
        $label = $inicio->format('d/m/Y') . ' a ' . $fim->format('d/m/Y');
        $anterior = $inicio->modify('-7 days')->format('Y-m-d');
        $proximo = $inicio->modify('+7 days')->format('Y-m-d');

        return [
            $inicio->format('Y-m-d'),
            $fim->format('Y-m-d'),
            $this->gerarDiasDoPeriodo($inicio, $fim),
            $label,
            $anterior,
            $proximo,
        ];
    }

    private function gerarDiasDoPeriodo(\DateTimeImmutable $inicio, \DateTimeImmutable $fim): array
    {
        $dias = [];
        for ($dia = $inicio; $dia <= $fim; $dia = $dia->modify('+1 day')) {
            $dias[] = [
                'data' => $dia->format('Y-m-d'),
                'dia' => $dia->format('d'),
                'semana' => $this->nomeDiaSemana((int)$dia->format('w')),
                'semana_curta' => $this->nomeDiaSemanaCurto((int)$dia->format('w')),
                'is_hoje' => $dia->format('Y-m-d') === date('Y-m-d'),
            ];
        }
        return $dias;
    }

    private function diaRespeitaEscala(array $paciente, string $data): bool
    {
        $dia = new \DateTimeImmutable($data);
        $mapa = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado',
        ];

        $campoDia = $mapa[(int)$dia->format('w')];
        if (array_key_exists($campoDia, $paciente) && (int)$paciente[$campoDia] !== 1) {
            return false;
        }

        $inicio = $paciente['contrato_data_inicio'] ?? $paciente['data_inicio'] ?? null;
        $fim = $paciente['contrato_data_fim'] ?? $paciente['data_fim'] ?? null;

        if (!empty($inicio) && $data < substr((string)$inicio, 0, 10)) {
            return false;
        }

        if (!empty($fim) && $data > substr((string)$fim, 0, 10)) {
            return false;
        }

        return true;
    }

    private function ocorrenciaCompativel(array $entrada, string $data, string $inicio, string $fim): ?array
    {
        foreach ($this->achatarOcorrencias($entrada) as $ocorrencia) {
            if (($ocorrencia['data_plantao'] ?? '') !== $data) {
                continue;
            }

            $ocInicio = $this->extrairHora($ocorrencia['hora_inicio'] ?? $ocorrencia['inicio'] ?? '');
            $ocFim = $this->extrairHora($ocorrencia['hora_fim'] ?? $ocorrencia['fim'] ?? '');

            if ($ocInicio === $inicio) {
                return $ocorrencia;
            }

            // Para plantão que cobre uma faixa inteira. Ex.: 24h cobrindo 07-19 e 19-07.
            if ($this->intervaloCobreTurno($ocorrencia, $data, $inicio, $fim)) {
                return $ocorrencia;
            }
        }

        return null;
    }

    private function achatarOcorrencias(array $entrada): array
    {
        if (isset($entrada['id'], $entrada['data_plantao'])) {
            return [$entrada];
        }

        $lista = [];
        foreach ($entrada as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach ($this->achatarOcorrencias($item) as $ocorrencia) {
                $lista[] = $ocorrencia;
            }
        }

        return $lista;
    }

    private function extrairHora(mixed $valor): string
    {
        $valor = trim((string)$valor);

        if ($valor === '') {
            return '';
        }

        if (preg_match('/\d{4}-\d{2}-\d{2}[ T](\d{2}:\d{2})/', $valor, $m)) {
            return $m[1];
        }

        if (preg_match('/^(\d{2}:\d{2})/', $valor, $m)) {
            return $m[1];
        }

        return '';
    }

    private function intervaloCobreTurno(array $ocorrencia, string $data, string $inicio, string $fim): bool
    {
        $inicioOc = strtotime((string)($ocorrencia['inicio'] ?? ''));
        $fimOc = strtotime((string)($ocorrencia['fim'] ?? ''));

        if (!$inicioOc || !$fimOc) {
            return false;
        }

        $inicioTurno = strtotime($data . ' ' . $inicio . ':00');
        $fimTurno = strtotime($data . ' ' . $fim . ':00');

        if ($fimTurno <= $inicioTurno) {
            $fimTurno = strtotime('+1 day', $fimTurno);
        }

        return $inicioOc <= $inicioTurno && $fimOc >= $fimTurno;
    }

    private function turnosPorCobertura(string $tipoCobertura): array
    {
        $turnos = [];
        foreach ($this->turnosPorContrato($this->normalizarContrato($tipoCobertura)) as $turno) {
            $codigo = (string)($turno['codigo'] ?? 'personalizado');
            $turnos[$codigo] = [
                'label' => $turno['label'] ?? $codigo,
                'inicio' => $turno['inicio'] ?? '07:00',
                'fim' => $turno['fim'] ?? '19:00',
            ];
        }

        return $turnos;
    }

    private function equipePaciente(array $paciente): array
    {
        return $this->normalizarEquipeEscala($paciente);
    }

    private function combinarDataHora(string $data, string $hora): string
    {
        $hora = preg_match('/^\d{2}:\d{2}/', $hora) ? substr($hora, 0, 5) : '00:00';
        $base = new \DateTimeImmutable($data . ' ' . $hora . ':00');

        return $base->format('Y-m-d H:i:s');
    }

    private function normalizarTurno(string $turno): string
    {
        $turno = strtolower(trim($turno));
        return match (true) {
            str_contains($turno, '07_19'), str_contains($turno, '07:00'), str_contains($turno, 'dia') => '12h_dia',
            str_contains($turno, '19_07'), str_contains($turno, '19:00'), str_contains($turno, 'noite') => '12h_noite',
            str_contains($turno, '24') => '24h',
            str_contains($turno, '8') => '8h',
            str_contains($turno, '6') => '6h',
            default => $turno !== '' ? $turno : 'manual',
        };
    }

    private function nomeDiaSemana(int $dia): string
    {
        return ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'][$dia] ?? '';
    }

    private function nomeDiaSemanaCurto(int $dia): string
    {
        return ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'][$dia] ?? '';
    }

    private function nomeMes(int $mes): string
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ][$mes] ?? '';
    }

    private function calcularResumo(array $cobertura, array $colaboradores): array
    {
        $resumo = [
            'total_pac' => count($cobertura),
            'ok' => 0,
            'sugerido' => 0,
            'vago' => 0,
            'substituido' => 0,
            'pendente' => 0,
            'ativos' => count($colaboradores),
        ];

        foreach ($cobertura as $pac) {
            foreach (($pac['turnos'] ?? []) as $turno) {
                foreach (($turno['plantoes'] ?? []) as $plantao) {
                    $status = $plantao['status'] ?? 'vago';
                    if (isset($resumo[$status])) {
                        $resumo[$status]++;
                    }
                }
            }
        }

        return $resumo;
    }

    /**
     * Gera lista de alertas para a sidebar.
     */
    private function montarHistoricoEscala(array $substituicoes, array $ocorrencias, ?array $aprovacaoPeriodo): array
    {
        $historico = [];

        if (!empty($aprovacaoPeriodo)) {
            $dataAprovacao = $aprovacaoPeriodo['aprovado_em']
                ?? $aprovacaoPeriodo['data_aprovacao']
                ?? $aprovacaoPeriodo['atualizado_em']
                ?? $aprovacaoPeriodo['criado_em']
                ?? null;

            $statusAprovacao = strtolower((string)($aprovacaoPeriodo['status'] ?? 'aprovada'));
            $historico[] = [
                'tipo' => in_array($statusAprovacao, ['fechada', 'finalizada'], true) ? 'fechamento' : 'aprovacao',
                'titulo' => in_array($statusAprovacao, ['fechada', 'finalizada'], true) ? 'Período fechado' : 'Período aprovado',
                'data' => $aprovacaoPeriodo['fechado_em'] ?? $dataAprovacao,
                'data_plantao' => $aprovacaoPeriodo['data_inicio'] ?? $aprovacaoPeriodo['periodo_inicio'] ?? null,
                'detalhe' => in_array($statusAprovacao, ['fechada', 'finalizada'], true)
                    ? 'Plantões finalizados e liberados para geração do contas a pagar.'
                    : 'Escala do período marcada como ' . ($aprovacaoPeriodo['status'] ?? 'aprovada') . '.',
                'observacoes' => $aprovacaoPeriodo['observacoes'] ?? null,
            ];
        }

        foreach ($substituicoes as $sub) {
            $dataPlantao = $sub['data_plantao'] ?? $sub['data_inicio'] ?? null;
            $horaInicio = $sub['hora_inicio'] ?? null;
            $horaFim = $sub['hora_fim'] ?? null;

            $substitutoNome = $sub['cuidador_substituto_nome']
                ?? $sub['substituto_nome']
                ?? 'Cuidador substituto';

            $originalNome = $sub['cuidador_original_nome']
                ?? $sub['colaborador_original_nome']
                ?? 'cuidador original';

            $periodo = trim(($horaInicio ?: '') . (($horaInicio || $horaFim) ? ' - ' : '') . ($horaFim ?: ''));

            $historico[] = [
                'tipo' => 'substituicao',
                'titulo' => 'Substituição de cuidador',
                'data' => $sub['criado_em'] ?? $sub['created_at'] ?? null,
                'data_plantao' => $dataPlantao,
                'detalhe' => $substitutoNome . ' cobriu ' . $originalNome . ($periodo ? ' no horário ' . $periodo : '') . '.',
                'motivo' => $this->textoMotivoSubstituicao($sub['motivo'] ?? null),
                'observacoes' => $sub['observacoes'] ?? null,
            ];
        }

        foreach ($ocorrencias as $ocorrencia) {
            $origem = (string)($ocorrencia['origem'] ?? '');
            $status = (string)($ocorrencia['status'] ?? '');

            if (!in_array($origem, ['Manual', 'Substituicao'], true)) {
                continue;
            }

            // Substituições já entram com detalhe próprio acima, então evitamos poluir o quadro.
            if ($status === 'substituido') {
                continue;
            }

            $dataRegistro = $ocorrencia['atualizado_em']
                ?? $ocorrencia['updated_at']
                ?? $ocorrencia['criado_em']
                ?? $ocorrencia['created_at']
                ?? null;

            $historico[] = [
                'tipo' => 'ajuste',
                'titulo' => $status === 'confirmado' ? 'Plantão confirmado/ajustado' : 'Ajuste manual de plantão',
                'data' => $dataRegistro,
                'data_plantao' => $ocorrencia['data_plantao'] ?? null,
                'detalhe' => 'Plantão ' . ($ocorrencia['hora_inicio'] ?? substr((string)($ocorrencia['inicio'] ?? ''), 11, 5)) . ' - ' . ($ocorrencia['hora_fim'] ?? substr((string)($ocorrencia['fim'] ?? ''), 11, 5)) . ' registrado manualmente.',
                'observacoes' => $ocorrencia['observacoes'] ?? null,
            ];
        }

        usort($historico, static function (array $a, array $b): int {
            $dataA = strtotime((string)($a['data'] ?? $a['data_plantao'] ?? '')) ?: 0;
            $dataB = strtotime((string)($b['data'] ?? $b['data_plantao'] ?? '')) ?: 0;
            return $dataB <=> $dataA;
        });

        return array_slice($historico, 0, 12);
    }

    private function textoMotivoSubstituicao(?string $motivo): string
    {
        $motivo = trim((string)$motivo);

        return match ($motivo) {
            'falta' => 'Falta',
            'atestado' => 'Atestado',
            'emergencia' => 'Emergência',
            'troca' => 'Troca operacional',
            '' => 'Substituição operacional',
            default => ucfirst(str_replace('_', ' ', $motivo)),
        };
    }

    /**
     * Gera lista de alertas para uso interno. O histórico visual fica no rodapé da grade.
     */
    private function gerarAlertas(array $cobertura, array $substituicoes): array
    {
        $alertas = [];

        foreach ($cobertura as $pac) {
            $pacienteNome = $pac['paciente']['nome_completo'] ?? $pac['nome'] ?? 'Paciente';

            foreach (($pac['turnos'] ?? []) as $turno) {
                foreach (($turno['plantoes'] ?? []) as $p) {
                    if (($p['status'] ?? '') === 'vago') {
                        $data = date('d/m', strtotime((string)($p['data'] ?? 'now')));
                        $alertas[] = [
                            'tipo'     => 'danger',
                            'texto'    => "{$pacienteNome} — {$data} sem cobertura",
                            'subtexto' => "Turno " . ($turno['label'] ?? '-') . ": " . ($p['hora_inicio'] ?? $turno['inicio'] ?? '--:--') . "–" . ($p['hora_fim'] ?? $turno['fim'] ?? '--:--') . " em aberto",
                        ];
                    }
                }
            }
        }

        foreach ($substituicoes as $sub) {
            $dataBase = $sub['data_plantao'] ?? $sub['data_inicio'] ?? null;
            $data = $dataBase ? date('d/m', strtotime((string)$dataBase)) : '--/--';
            $pacienteNome = $sub['paciente_nome'] ?? 'Paciente';
            $substitutoNome = $sub['cuidador_substituto_nome'] ?? $sub['substituto_nome'] ?? 'Cuidador substituto';
            $originalNome = $sub['cuidador_original_nome'] ?? $sub['colaborador_original_nome'] ?? 'cuidador original';

            $alertas[] = [
                'tipo'     => 'warn',
                'texto'    => "Substituição ativa — {$pacienteNome} em {$data}",
                'subtexto' => "{$substitutoNome} cobrindo {$originalNome}",
            ];
        }

        return array_slice($alertas, 0, 10);
    }

    /**
     * Lista N semanas para o select (passadas + futuras em torno de hoje).
     */
    private function listarSemanas(int $total = 8): array
    {
        $semanas    = [];
        $hoje       = time();
        $dow        = (int)date('N', $hoje);
        $segunda    = strtotime('-' . ($dow - 1) . ' days', $hoje);
        $offset     = -4; // 4 semanas para trás
        $fim        = $total + $offset;

        for ($i = $offset; $i < $fim; $i++) {
            $ts     = strtotime("{$i} weeks", $segunda);
            $tsFim  = strtotime('+6 days', $ts);
            $value  = date('Y-m-d', $ts);
            $label  = date('d/m', $ts) . ' – ' . date('d/m', $tsFim);
            if ($i === 0) $label .= ' (atual)';
            $semanas[] = compact('value', 'label');
        }

        return $semanas;
    }

    /**
     * Gera iniciais a partir do nome completo (máx 2 letras).
     */
    private function iniciais(string $nome): string
    {
        $partes = explode(' ', trim($nome));
        if (count($partes) === 1) return strtoupper(substr($partes[0], 0, 2));
        return strtoupper(substr($partes[0], 0, 1) . substr(end($partes), 0, 1));
    }

    /**
     * Retorna o ícone Tabler adequado baseado no horário de início do turno.
     */
    private function iconeParaTurno(string $inicio): string
    {
        $h = (int)explode(':', $inicio)[0];
        if ($h >= 6 && $h < 18) return 'ti-sun';
        return 'ti-moon';
    }



    private function normalizarEquipeEscala(array $pac): array
    {
        $ids = array_values(array_filter(explode('||', (string)($pac['equipe_ids'] ?? ''))));
        $uuids = array_values(array_filter(explode('||', (string)($pac['equipe_uuids'] ?? ''))));
        $nomes = array_values(array_filter(explode('||', (string)($pac['equipe_nomes'] ?? ''))));
        $cores = explode('||', (string)($pac['equipe_cores'] ?? ''));

        $equipe = [];
        foreach ($ids as $i => $id) {
            $id = (int)$id;
            if ($id <= 0) continue;
            $equipe[] = [
                'id' => $id,
                'uuid' => $uuids[$i] ?? null,
                'nome' => $nomes[$i] ?? 'Cuidador',
                'cor' => $cores[$i] ?? null,
            ];
        }

        return $equipe;
    }

    private function cuidadorSugeridoParaDia(array $pac, int $diaIndex): array
    {
        $equipe = $pac['equipe_escala'] ?? [];
        if (is_array($equipe) && count($equipe) > 0) {
            return $equipe[$diaIndex % count($equipe)];
        }

        if (!empty($pac['cuidador_referencia_id'])) {
            return [
                'id' => (int)$pac['cuidador_referencia_id'],
                'uuid' => $pac['cuidador_referencia_uuid'] ?? null,
                'nome' => $pac['cuidador_referencia_nome'] ?? 'Cuidador referência',
                'cor' => $pac['cuidador_referencia_cor'] ?? null,
            ];
        }

        return [];
    }

    private function normalizarContrato(string $valor): string
    {
        $v = strtolower(trim($valor));
        if (str_contains($v, '24')) return '24h';
        if (str_contains($v, '8')) return '8h';
        if (str_contains($v, '6')) return '6h';
        return '12h';
    }

    private function turnosPorContrato(string $tipoContrato): array
    {
        return match ($tipoContrato) {
            '24h' => [
                ['codigo' => 'diurno', 'label' => 'Diurno 07h-19h', 'inicio' => '07:00', 'fim' => '19:00', 'icone' => 'ti-sun'],
                ['codigo' => 'noturno', 'label' => 'Noturno 19h-07h', 'inicio' => '19:00', 'fim' => '07:00', 'icone' => 'ti-moon'],
            ],
            '8h' => [
                ['codigo' => 'personalizado', 'label' => 'Turno 08h-16h', 'inicio' => '08:00', 'fim' => '16:00', 'icone' => 'ti-clock'],
            ],
            '6h' => [
                ['codigo' => 'personalizado', 'label' => 'Turno 08h-14h', 'inicio' => '08:00', 'fim' => '14:00', 'icone' => 'ti-clock'],
            ],
            default => [
                ['codigo' => 'diurno', 'label' => 'Diurno 07h-19h', 'inicio' => '07:00', 'fim' => '19:00', 'icone' => 'ti-sun'],
            ],
        };
    }

    private function turnoMetaPorHorario(string $inicio, string $fim, ?string $tipoPlantao = null): array
    {
        $hi = strlen($inicio) >= 16 ? substr($inicio, 11, 5) : substr($inicio, 0, 5);
        $hf = strlen($fim) >= 16 ? substr($fim, 11, 5) : substr($fim, 0, 5);

        if ($tipoPlantao === '24h') {
            return ['codigo' => '24h', 'label' => '24 horas', 'inicio' => $hi ?: '07:00', 'fim' => $hf ?: '07:00', 'icone' => 'ti-clock-24'];
        }

        if ($hi === '19:00') {
            return ['codigo' => 'noturno', 'label' => 'Noturno 19h-07h', 'inicio' => '19:00', 'fim' => '07:00', 'icone' => 'ti-moon'];
        }

        if ($hi === '07:00' && $hf === '19:00') {
            return ['codigo' => 'diurno', 'label' => 'Diurno 07h-19h', 'inicio' => '07:00', 'fim' => '19:00', 'icone' => 'ti-sun'];
        }

        $label = 'Turno ' . ($hi ?: '--:--') . '-' . ($hf ?: '--:--');
        return ['codigo' => 'personalizado', 'label' => $label, 'inicio' => $hi ?: '07:00', 'fim' => $hf ?: '19:00', 'icone' => 'ti-clock'];
    }

    // =========================================================
    // POST /escala/salvar — Cria ou atualiza um plantão avulso
    // =========================================================
    public function salvar(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        $pacienteUuid = $this->sanitizarUuid($_POST['paciente_uuid'] ?? null);
        $cuidadorUuid = $this->sanitizarUuid($_POST['cuidador_uuid'] ?? null);
        $dataPlantao  = $this->sanitizarData($_POST['data_plantao']  ?? null);
        $turno        = trim($_POST['turno']      ?? '');
        $observacao   = trim($_POST['observacao'] ?? '');
        $escalaId     = (int) ($_POST['escala_id'] ?? 0) ?: null;

        if (!$pacienteUuid || !$cuidadorUuid || !$dataPlantao || !$turno) {
            $this->redirectComErro('/escala', 'Preencha todos os campos obrigatórios.');
            return;
        }

        $paciente = $this->escala->buscarPacientePorUuid($pacienteUuid);
        $cuidador = $this->escala->buscarCuidadorPorUuid($cuidadorUuid);

        if (!$paciente || !$cuidador) {
            $this->redirectComErro('/escala', 'Paciente ou cuidador não encontrado.');
            return;
        }

        [$inicio, $fim] = $this->resolverHorarioTurno($turno, $dataPlantao, $_POST);

        // Deriva tipo_plantao a partir do turno
        $tipoPlantao = match ($turno) {
            '24h'        => '24h',
            'noturno'    => '12h',
            'personalizado' => '12h',
            default      => '12h',  // diurno
        };

        $escalaBaseAtiva = $this->escala->escalaBaseAtivaPorPaciente((int)$paciente['id']);
        $escalaBaseId = (int)($escalaBaseAtiva['id'] ?? 0);

        if ($escalaId) {
            $ocorrenciaAtual = $this->escalaOcorrencia->find($escalaId);
            $escalaBaseId = (int)($ocorrenciaAtual['escala_base_id'] ?? $escalaBaseId);
        }

        $dados = [
            'escala_base_id' => $escalaBaseId ?: null,
            'paciente_id'    => $paciente['id'],
            'cuidador_id'    => $cuidador['id'],
            'data_plantao'   => $dataPlantao,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'tipo_plantao'   => $tipoPlantao,
            'status'         => 'confirmado',
            'origem'         => 'Manual',
            'observacoes'    => $observacao ?: null,
        ];

        if ($escalaId) {
            $this->escalaOcorrencia->update($escalaId, $dados);
            $msg = 'Plantao atualizado com sucesso.';
        } else {
            $conflito = $this->escalaOcorrencia->conflito(
                (int)$cuidador['id'],
                $dataPlantao,
                substr($inicio, 11, 5),
                substr($fim, 11, 5),
                $escalaId
            );

            if ($conflito) {
                $this->redirectComErro('/escala', 'Conflito de horario: este cuidador ja tem plantao neste periodo.');
                return;
            }

            $this->escalaOcorrencia->createRecord($dados);
            $msg = 'Plantao alocado com sucesso.';
        }

        $periodoRetorno = $this->resolverDomingo($dataPlantao);
        $this->flash('success', $msg);
        $this->redirect('/escala?paciente=' . rawurlencode($pacienteUuid) . '&modo=semana&periodo=' . rawurlencode($periodoRetorno));
    }

    // =========================================================
    // POST /escala/trocar — Troca dois cuidadores de lugar na grade
    // =========================================================
    public function trocar(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        $origemId = (int)($_POST['origem_id'] ?? 0);
        $destinoId = (int)($_POST['destino_id'] ?? 0);
        $periodo = $this->sanitizarData($_POST['periodo'] ?? ($_POST['semana'] ?? null)) ?: date('Y-m-d');
        $modo = in_array(($_POST['modo'] ?? 'semana'), ['semana', 'mes'], true) ? $_POST['modo'] : 'semana';
        $pacienteUuid = $this->sanitizarUuid($_POST['paciente_uuid'] ?? null);

        $query = http_build_query(array_filter([
            'paciente' => $pacienteUuid,
            'modo' => $modo,
            'periodo' => $periodo,
        ], static fn($v) => $v !== null && $v !== ''));
        $rotaVolta = '/escala' . ($query ? '?' . $query : '');

        if (!$origemId || !$destinoId || $origemId === $destinoId) {
            $this->redirectComErro($rotaVolta, 'Selecione dois plantões diferentes para trocar.');
            return;
        }

        $ok = $this->escalaOcorrencia->trocarCuidadores($origemId, $destinoId);

        if (!$ok) {
            $this->redirectComErro($rotaVolta, 'Não foi possível trocar os cuidadores.');
            return;
        }

        $this->flash('success', 'Cuidadores trocados com sucesso.');
        $this->redirect($rotaVolta);
    }

    // =========================================================
    // POST /escala/substituir — Registra substituicao de cuidador
    // =========================================================
    public function substituir(): void
    {
        if ($this->bloquearCuidadorEmEdicaoEscala()) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/escala');
            return;
        }

        $escalaId = (int)($_POST['escala_id'] ?? 0);
        $substitutoUuid = $this->sanitizarUuid($_POST['substituto_id'] ?? null);
        $motivo = trim((string)($_POST['motivo'] ?? ''));
        $observacao = trim((string)($_POST['observacao'] ?? ''));
        $voltar = $_SERVER['HTTP_REFERER'] ?? url('/escala');

        if (!$escalaId || !$substitutoUuid) {
            $this->flash('error', 'Dados inválidos para substituição.');
            header('Location: ' . $voltar);
            exit;
        }

        $substituto = $this->escala->buscarCuidadorPorUuid($substitutoUuid);
        if (!$substituto) {
            $this->flash('error', 'Substituto não encontrado.');
            header('Location: ' . $voltar);
            exit;
        }

        $ok = $this->escalaSubstituicao->registrar(
            $escalaId,
            (int)$substituto['id'],
            $motivo ?: 'Substituição manual',
            $observacao ?: null
        );

        $this->flash($ok ? 'success' : 'error', $ok ? 'Substituição registrada.' : 'Não foi possível registrar a substituição.');
        header('Location: ' . $voltar);
        exit;
    }

    // =========================================================
    // Helpers de suporte
    // =========================================================

    /**
     * Resolve inicio e fim absolutos (Y-m-d H:i:s) com base no turno.
     */
    private function resolverHorarioTurno(string $turno, string $data, array $post): array
    {
        $map = [
            'diurno'  => ['07:00:00', '19:00:00'],
            'noturno' => ['19:00:00', '07:00:00'],
            '24h'     => ['07:00:00', '07:00:00'],
        ];

        if ($turno === 'personalizado') {
            $hi = ($post['inicio'] ?? '07:00') . ':00';
            $hf = ($post['fim']    ?? '19:00') . ':00';
        } else {
            [$hi, $hf] = $map[$turno] ?? ['07:00:00', '19:00:00'];
        }

        $inicioDt = new \DateTime("{$data} {$hi}");
        $fimDt    = new \DateTime("{$data} {$hf}");

        if ($turno === '24h') {
            $fimDt = clone $inicioDt;
            $fimDt->modify('+1 day');
        } elseif ($fimDt <= $inicioDt) {
            $fimDt->modify('+1 day');
        }

        return [
            $inicioDt->format('Y-m-d H:i:s'),
            $fimDt->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Redireciona com mensagem de erro na session.
     */
    private function redirectComErro(string $rota, string $msg): void
    {
        $this->flash('error', $msg);
        header("Location: " . BASE_URL . $rota);
        exit;
    }
}