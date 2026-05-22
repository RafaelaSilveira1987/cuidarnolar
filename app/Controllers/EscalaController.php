<?php

namespace App\Controllers;

use App\Models\Escala;
use App\Models\EscalaOcorrencia;
use App\Models\EscalaConflito;
use App\Models\EscalaSubstituicao;

class EscalaController extends BaseController
{
    private Escala $escala;
    private EscalaOcorrencia $escalaOcorrencia;
    private EscalaSubstituicao $escalaSubstituicao;

    public function __construct()
    {
        $this->escala = new Escala();
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
        $filtros = [
            'paciente_id'    => (int)($_GET['paciente_id']    ?? 0) ?: null,
            'colaborador_id' => (int)($_GET['colaborador_id'] ?? 0) ?: null,
        ];

        // // ── 4. Busca dados do banco ──────────────────────────
        $pacientes     = $this->escala->listarPacientes();
        $colaboradores = $this->escala->listarCuidadores();
        if (!is_array($colaboradores)) {
            $colaboradores = [];
        }

        // Ocorrências da semana (plantões gerados/alocados)
        $ocorrencias = $this->escalaOcorrencia->porSemana(
            $dias[0]['date'],
            $dias[6]['date'],
            $filtros['paciente_id'],
            $filtros['colaborador_id']
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
            'colaboradores' => $colaboradores ?? [],
            'pacientes' => $pacientes ?? [],
            '_csrf' => $_csrf ?? null,
        ]);
    }

    // =========================================================
    // Helpers privados
    // =========================================================

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
}