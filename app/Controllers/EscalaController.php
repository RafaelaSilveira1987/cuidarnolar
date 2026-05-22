<?php

namespace App\Controllers;

use App\Models\Escala;
use App\Models\EscalaOcorrencia;
use App\Models\EscalaSubstituicao;

class EscalaController extends BaseController
{
    private Escala $escala;
    private EscalaOcorrencia $escalaOcorrencia;
    private EscalaSubstituicao $escalaSubstituicao;

    public function __construct()
    {
        $this->escala             = new Escala();
        $this->escalaOcorrencia   = new EscalaOcorrencia();
        $this->escalaSubstituicao = new EscalaSubstituicao();
    }

    // =========================================================
    // GET /escalas — Central de Cobertura
    // =========================================================
    public function index(): void
    {
        // ── 1. Semana selecionada ────────────────────────────
        // Aceita ?semana=2025-05-19 (segunda-feira da semana)
        $semanaParam = $_GET['semana'] ?? null;
        $dataBase    = $this->resolverSegunda($semanaParam);

        // ── 2. Gera os 7 dias da semana ──────────────────────
        $dias = $this->gerarDias($dataBase);

        // ── 3. Filtros ───────────────────────────────────────
        $pacienteUuid    = $this->sanitizarUuid($_GET['paciente_uuid']    ?? null);
        $colaboradorUuid = $this->sanitizarUuid($_GET['colaborador_uuid'] ?? null);

        // Resolve UUIDs → IDs numéricos exigidos pelo Model
        $pacienteId    = null;
        $colaboradorId = null;

        if ($pacienteUuid) {
            $pac = $this->escala->buscarPacientePorUuid($pacienteUuid);
            $pacienteId = $pac['id'] ?? null;
        }

        if ($colaboradorUuid) {
            $col = $this->escala->buscarCuidadorPorUuid($colaboradorUuid);
            $colaboradorId = $col['id'] ?? null;
        }

        $filtros = [
            'paciente_uuid'    => $pacienteUuid,
            'colaborador_uuid' => $colaboradorUuid,
        ];

        // ── 4. Busca dados do banco ──────────────────────────
        $pacientes     = $this->escala->listarPacientes();
        $colaboradores = $this->escala->listaCuidadores();

        // Ocorrências da semana (plantões gerados/alocados)
        $ocorrencias = $this->escalaOcorrencia->porSemana(
            $dias[0]['date'],
            $dias[6]['date'],
            $pacienteId,
            $colaboradorId
        );

        // Substituições da semana
        $substituicoes = $this->escalaSubstituicao->porSemana(
            $dias[0]['date'],
            $dias[6]['date']
        );

        // ── 5. Monta estrutura de cobertura por paciente ─────
        $cobertura = $this->montarCobertura($ocorrencias, $substituicoes, $dias, $filtros);

        // ── 6. Resumo dos cards superiores ───────────────────
        $resumo = $this->calcularResumo($cobertura, $colaboradores);

        // ── 7. Alertas ───────────────────────────────────────
        $alertas = $this->gerarAlertas($cobertura, $substituicoes);

        // ── 8. Semanas disponíveis no select ─────────────────
        $semanas = $this->listarSemanas(8); // 4 anteriores + atual + 3 próximas

        // ── 9. Render ────────────────────────────────────────
        $this->view('escalas/index', [
            'dias'          => $dias,
            'semana_ativa'  => $dias[0]['date'],
            'semanas'       => $semanas,
            'pacientes'     => $pacientes,
            'colaboradores' => $colaboradores,
            'cobertura'     => $cobertura,
            'resumo'        => $resumo,
            'alertas'       => $alertas,
            'filtros'       => $filtros,
        ]);
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
    private function resolverSegunda(?string $data): string
    {
        $ts = $data ? strtotime($data) : time();
        $dow = (int)date('N', $ts); // 1=seg … 7=dom
        return date('Y-m-d', strtotime('-' . ($dow - 1) . ' days', $ts));
    }

    /**
     * Gera array com os 7 dias da semana a partir da segunda-feira.
     *
     * @return array [{date, label, num}, ...]
     */
    private function gerarDias(string $segunda): array
    {
        $labels = ['SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB', 'DOM'];
        $dias   = [];
        for ($i = 0; $i < 7; $i++) {
            $ts      = strtotime("+{$i} days", strtotime($segunda));
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
    private function montarCobertura(
        array $ocorrencias,
        array $substituicoes,
        array $dias,
        array $filtros
    ): array {
        // Indexa substituições por escala_id para lookup rápido
        $subIdx = [];
        foreach ($substituicoes as $sub) {
            $subIdx[$sub['escala_ocorrencia_id']] = $sub;
        }

        // Agrupa ocorrências por paciente → turno
        $porPaciente = [];
        foreach ($ocorrencias as $oc) {
            $pid = $oc['paciente_id'];
            if (!isset($porPaciente[$pid])) {
                $porPaciente[$pid] = [
                    'id'            => $pid,
                    'nome'          => $oc['paciente_nome'],
                    'iniciais'      => $this->iniciais($oc['paciente_nome']),
                    'cor_avatar'    => $oc['cor_avatar']   ?? '#dbeafe',
                    'cor_avatar_t'  => $oc['cor_avatar_t'] ?? '#1e3a8a',
                    'endereco'      => $oc['endereco']     ?? '',
                    'tipo_contrato' => $oc['tipo_contrato'] ?? '12h',
                    '_turnos'       => [], // temporário: turno_label → [data → plantao]
                    '_turnos_meta'  => [], // icone
                ];
            }

            $turno = $oc['turno_label'] ?? 'Turno';
            $icone = $this->iconeParaTurno($oc['turno_inicio'] ?? '07:00');

            if (!isset($porPaciente[$pid]['_turnos'][$turno])) {
                $porPaciente[$pid]['_turnos'][$turno]      = [];
                $porPaciente[$pid]['_turnos_meta'][$turno] = $icone;
            }

            // Verifica se tem substituição ativa
            $sub     = $subIdx[$oc['id']] ?? null;
            $status  = 'ok';
            $subNome = null;
            if ($sub) {
                $status  = 'sub';
                $subNome = $oc['colaborador_nome'] ?? null;
            } elseif (empty($oc['colaborador_id'])) {
                $status = 'vago';
            }

            $porPaciente[$pid]['_turnos'][$turno][$oc['data_plantao']] = [
                'data'           => $oc['data_plantao'],
                'escala_id'      => $oc['id'],
                'colaborador_id' => $sub ? ($sub['substituto_id'] ?? null) : ($oc['colaborador_id'] ?? null),
                'colaborador'    => $sub ? ($sub['substituto_nome'] ?? '—') : ($oc['colaborador_nome'] ?? '—'),
                'inicio'         => $oc['turno_inicio'] ?? '00:00',
                'fim'            => $oc['turno_fim']    ?? '00:00',
                'status'         => $status,
                'sub_nome'       => $subNome,
            ];
        }

        // Converte para formato final com plantões ordenados pelos 7 dias
        $coresAvatares = [
            ['#d1fae5', '#064e3b'],
            ['#dbeafe', '#1e3a8a'],
            ['#fef3c7', '#78350f'],
            ['#fce7f3', '#831843'],
            ['#e0e7ff', '#312e81'],
            ['#dcfce7', '#14532d'],
        ];
        $corIdx = 0;

        $resultado = [];
        foreach ($porPaciente as $pid => $pac) {
            $turnos = [];
            $totalSlots = 0;
            $cobertos   = 0;

            foreach ($pac['_turnos'] as $turnoLabel => $plantoesPorData) {
                $plantoes = [];
                foreach ($dias as $d) {
                    $totalSlots++;
                    if (isset($plantoesPorData[$d['date']])) {
                        $p = $plantoesPorData[$d['date']];
                        if ($p['status'] === 'ok' || $p['status'] === 'sub') $cobertos++;
                        $plantoes[] = $p;
                    } else {
                        // Slot sem ocorrência registrada = vago
                        $plantoes[] = [
                            'data'           => $d['date'],
                            'escala_id'      => null,
                            'colaborador_id' => null,
                            'colaborador'    => '',
                            'inicio'         => '—',
                            'fim'            => '—',
                            'status'         => 'vago',
                            'sub_nome'       => null,
                        ];
                    }
                }
                $turnos[] = [
                    'label'    => $turnoLabel,
                    'icone'    => $pac['_turnos_meta'][$turnoLabel],
                    'plantoes' => $plantoes,
                ];
            }

            $pct = $totalSlots > 0 ? round(($cobertos / $totalSlots) * 100) : 0;

            // Cor do avatar: usa a definida no banco ou gera uma sequencial
            $cor = empty($pac['cor_avatar']) || $pac['cor_avatar'] === '#dbeafe'
                ? $coresAvatares[$corIdx % count($coresAvatares)]
                : [$pac['cor_avatar'], $pac['cor_avatar_t']];
            $corIdx++;

            $resultado[] = array_merge($pac, [
                'cor_avatar'    => $cor[0],
                'cor_avatar_t'  => $cor[1],
                'cobertura_pct' => $pct,
                'turnos'        => $turnos,
            ]);
        }

        return $resultado;
    }

    // =========================================================
    // POST /escala/excluir — Remove um plantão avulso
    // =========================================================
    public function excluir(): void
    {
        $escalaId = (int) ($_POST['escala_id'] ?? 0);

        if (!$escalaId) {
            $this->redirectComErro('/escala', 'ID de plantão inválido.');
            return;
        }

        $this->escalaOcorrencia->delete($escalaId);

        header("Location: " . BASE_URL . "/escala?sucesso=" . urlencode('Plantão removido.'));
        exit;
    }

    /**
     * Calcula o resumo dos 4 cards superiores.
     */
    private function calcularResumo(array $cobertura, array $colaboradores): array
    {
        $totalPac  = count($cobertura);
        $cobertos  = 0;
        $vagos     = 0;
        $subs      = 0;

        foreach ($cobertura as $pac) {
            $pacCoberto = true;
            foreach ($pac['turnos'] as $turno) {
                foreach ($turno['plantoes'] as $p) {
                    if ($p['status'] === 'vago') {
                        $vagos++;
                        $pacCoberto = false;
                    }
                    if ($p['status'] === 'sub') {
                        $subs++;
                    }
                }
            }
            if ($pacCoberto) $cobertos++;
        }

        return [
            'total_pac'     => $totalPac,
            'cobertos'      => $cobertos,
            'vagos'         => $vagos,
            'substituicoes' => $subs,
            'ativos'        => count($colaboradores),
        ];
    }

    /**
     * Gera lista de alertas para a sidebar.
     */
    private function gerarAlertas(array $cobertura, array $substituicoes): array
    {
        $alertas = [];

        foreach ($cobertura as $pac) {
            foreach ($pac['turnos'] as $turno) {
                foreach ($turno['plantoes'] as $p) {
                    if ($p['status'] === 'vago') {
                        $data = date('d/m', strtotime($p['data']));
                        $alertas[] = [
                            'tipo'     => 'danger',
                            'texto'    => "{$pac['nome']} — {$data} sem cobertura",
                            'subtexto' => "Turno {$turno['label']}: {$p['inicio']}–{$p['fim']} em aberto",
                        ];
                    }
                }
            }
        }

        foreach ($substituicoes as $sub) {
            $data = date('d/m', strtotime($sub['data_plantao']));
            $alertas[] = [
                'tipo'     => 'warn',
                'texto'    => "Substituição ativa — {$sub['paciente_nome']} em {$data}",
                'subtexto' => "{$sub['substituto_nome']} cobrindo {$sub['colaborador_original_nome']}",
            ];
        }

        // Limita a 10 alertas para não poluir a sidebar
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

    // =========================================================
    // POST /escala/salvar — Cria ou atualiza um plantão avulso
    // =========================================================
    public function salvar(): void
    {
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

        $dados = [
            'escala_base_id' => null,           // plantão avulso
            'paciente_id'    => $paciente['id'],
            'cuidador_id'    => $cuidador['id'],
            'data_plantao'   => $dataPlantao,
            'inicio'         => $inicio,
            'fim'            => $fim,
            'tipo_plantao'   => $tipoPlantao,
            'status'         => 'previsto',
            'origem'         => 'Manual',
            'observacoes'    => $observacao ?: null,
        ];

        if ($escalaId) {
            $this->escalaOcorrencia->update($escalaId, $dados);
            $msg = 'Plantao atualizado com sucesso.';
        } else {
            $conflito = $this->escalaOcorrencia->conflito(
                $cuidador['id'],
                $inicio,
                $fim
            );

            if ($conflito) {
                $this->redirectComErro('/escala', 'Conflito de horario: este cuidador ja tem plantao neste periodo.');
                return;
            }

            $this->escalaOcorrencia->createRecord($dados);
            $msg = 'Plantao alocado com sucesso.';
        }

        $segunda = $this->resolverSegunda($dataPlantao);
        header("Location: " . BASE_URL . "/escala?semana={$segunda}&sucesso=" . urlencode($msg));
        exit;
    }

    // =========================================================
    // POST /escala/substituir — Registra substituicao de cuidador
    // =========================================================
    public function substituir(): void
    {
        $escalaId       = (int) ($_POST['escala_id']     ?? 0);
        $substitutoUuid = $this->sanitizarUuid($_POST['substituto_id'] ?? null);
        $motivo         = trim($_POST['motivo']     ?? '');
        $observacao     = trim($_POST['observacao'] ?? '');

        if (!$escalaId || !$substitutoUuid) {
            $this->redirectComErro('/escala', 'Dados invalidos para substituicao.');
            return;
        }

        $substituto = $this->escala->buscarCuidadorPorUuid($substitutoUuid);
        if (!$substituto) {
            $this->redirectComErro('/escala', 'Substituto nao encontrado.');
            return;
        }

        // Busca o cuidador original da ocorrência para registrar corretamente
        $ocorrencia = $this->escalaOcorrencia->find($escalaId);
        if (!$ocorrencia) {
            $this->redirectComErro('/escala', 'Plantao nao encontrado.');
            return;
        }

        $this->escalaSubstituicao->createRecord([
            'ocorrencia_id'          => $escalaId,
            'cuidador_original_id'   => $ocorrencia['cuidador_id'] ?? null,
            'cuidador_substituto_id' => $substituto['id'],
            'motivo'                 => $motivo ?: null,
            'observacoes'            => $observacao ?: null,
        ]);

        header("Location: " . BASE_URL . "/escala?sucesso=" . urlencode('Substituicao registrada.'));
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
        $_SESSION['erro'] = $msg;
        header("Location: " . BASE_URL . $rota);
        exit;
    }
}