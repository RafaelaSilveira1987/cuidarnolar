<?php
/**
 * app/Views/escalas/index.php
 * Grade operacional de escalas — visual por paciente.
 */

$csrf = $_csrf ?? '';
$modo = $modo ?? 'semana';
$filtros = $filtros ?? [];
$aprovacaoPeriodo = $aprovacaoPeriodo ?? null;
$historicoEscala = $historicoEscala ?? [];
$dias = $dias ?? [];
$cobertura = $cobertura ?? [];
$pacientes = $pacientes ?? [];
$colaboradores = $colaboradores ?? [];
$pacienteGrade = $cobertura[0] ?? null;
$pacienteSelecionado = $pacienteSelecionado ?? ($pacienteGrade['paciente'] ?? null);
$plantaoPorData = [];
$escopoCuidador = function_exists('is_cuidador_scope') && is_cuidador_scope();

if ($pacienteGrade) {
    foreach (($pacienteGrade['turnos'] ?? []) as $turnoCodigo => $turno) {
        foreach (($turno['plantoes'] ?? []) as $data => $plantao) {
            if (($plantao['status'] ?? '') === 'fora_vigencia') {
                continue;
            }
            $plantaoPorData[$data][] = $plantao;
        }
    }
}

foreach ($plantaoPorData as $data => &$itens) {
    usort($itens, static fn($a, $b) => strcmp((string)($a['hora_inicio'] ?? ''), (string)($b['hora_inicio'] ?? '')));
}
unset($itens);

function escala_data_br(?string $data): string
{
    if (!$data) {
        return '—';
    }
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '—';
}

function escala_data_hora_br(?string $data): string
{
    if (!$data) {
        return '—';
    }
    $ts = strtotime($data);
    return $ts ? date('d/m/Y H:i', $ts) : '—';
}

function escala_tipo_historico_label(?string $tipo): string
{
    return match ($tipo) {
        'aprovacao' => 'Aprovação',
        'fechamento' => 'Fechamento',
        'substituicao' => 'Substituição',
        'ajuste' => 'Ajuste',
        default => 'Registro',
    };
}

function escala_tipo_historico_icone(?string $tipo): string
{
    return match ($tipo) {
        'aprovacao' => '✓',
        'fechamento' => '✔',
        'substituicao' => '⇄',
        'ajuste' => '✎',
        default => '•',
    };
}

function escala_tipo_historico_classe(?string $tipo): string
{
    return preg_match('/^[a-z0-9_-]+$/i', (string)$tipo) ? (string)$tipo : 'registro';
}

function escala_hora(?string $hora): string
{
    if (!$hora) {
        return '--:--';
    }
    if (preg_match('/\d{2}:\d{2}/', $hora, $m)) {
        return $m[0];
    }
    return substr($hora, 0, 5);
}

function escala_cor(?array $colaborador): string
{
    $cor = trim((string)($colaborador['cor'] ?? ''));
    return preg_match('/^#[0-9a-fA-F]{6}$/', $cor) ? $cor : '#01948e';
}

function escala_nome_curto(?string $nome): string
{
    $nome = trim((string)$nome);
    if ($nome === '') {
        return 'Sem cuidador';
    }
    $partes = preg_split('/\s+/', $nome);
    return count($partes) > 1 ? $partes[0] . ' ' . end($partes) : $nome;
}

$queryBase = function (array $extra = []) use ($filtros): string {
    $params = array_filter([
        'paciente' => $filtros['paciente'] ?? null,
        'cuidador' => $filtros['cuidador'] ?? null,
        'modo' => $filtros['modo'] ?? 'semana',
        'periodo' => $filtros['periodo'] ?? date('Y-m-d'),
    ], static fn($v) => $v !== null && $v !== '');

    return http_build_query(array_merge($params, $extra));
};
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/escala.css">

<section class="escala-page">
    <div class="escala-topbar">
        <div>
            <span class="escala-eyebrow">Central operacional</span>
            <h1>Gestão de Escalas</h1>
            <p>Visual limpo por paciente, com troca de cuidadores, aprovação da escala e conferência por semana ou mês.
            </p>
        </div>
        <div class="escala-actions-main">
            <a class="btn btn-date"
                href="<?= url('/escala?' . $queryBase(['periodo' => $periodoAnterior ?? date('Y-m-d')])) ?>">
                <i class="ti ti-arrow-back"></i>
            </a>

            <a class="btn btn-date" href="<?= url('/escala?' . $queryBase(['periodo' => date('Y-m-d')])) ?>">
                Hoje
            </a>

            <a class="btn btn-date"
                href="<?= url('/escala?' . $queryBase(['periodo' => $periodoProximo ?? date('Y-m-d')])) ?>">
                <i class="ti ti-arrow-forward-up"></i>
            </a>
        </div>
    </div>

    <form class="escala-filter-bar" method="GET" action="<?= url('/escala') ?>">
        <label>
            <span>Paciente</span>
            <select name="paciente" onchange="this.form.submit()">
                <?php if (empty($pacientes)): ?>
                <option value="">Nenhum paciente com escala base</option>
                <?php endif; ?>
                <?php foreach ($pacientes as $pac): ?>
                <?php $pUuid = (string)($pac['uuid'] ?? $pac['id'] ?? ''); ?>
                <option value="<?= e($pUuid) ?>" <?= ($filtros['paciente'] ?? '') === $pUuid ? 'selected' : '' ?>>
                    <?= e($pac['nome_completo'] ?? 'Paciente') ?><?= !empty($pac['tipo_cobertura']) ? ' — ' . e($pac['tipo_cobertura']) : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Cuidador</span>
            <select name="cuidador" onchange="this.form.submit()">
                <option value="">Todos</option>
                <?php foreach ($colaboradores as $col): ?>
                <?php $cUuid = (string)($col['uuid'] ?? $col['id'] ?? ''); ?>
                <option value="<?= e($cUuid) ?>" <?= ($filtros['cuidador'] ?? '') === $cUuid ? 'selected' : '' ?>>
                    <?= e($col['nome_completo'] ?? 'Cuidador') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <span>Visualização</span>
            <select name="modo" onchange="this.form.submit()">
                <option value="semana" <?= $modo === 'semana' ? 'selected' : '' ?>>Semana</option>
                <option value="mes" <?= $modo === 'mes' ? 'selected' : '' ?>>Mês</option>
            </select>
        </label>

        <label>
            <span>Período</span>
            <input type="date" name="periodo" value="<?= e($filtros['periodo'] ?? date('Y-m-d')) ?>"
                onchange="this.form.submit()">
        </label>

        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <div class="escala-summary-strip">
        <div><span>Período</span><strong><?= e($periodoLabel ?? '') ?></strong></div>
        <div><span>OK</span><strong><?= (int)($resumo['ok'] ?? 0) ?></strong></div>
        <div><span>Aguardando confirmação</span><strong><?= (int)($resumo['sugerido'] ?? 0) ?></strong></div>
        <div><span>Vagos</span><strong><?= (int)($resumo['vago'] ?? 0) ?></strong></div>
    </div>

    <?php if (!$pacienteGrade): ?>
    <section class="escala-empty-state">
        <h2>Nenhuma escala encontrada</h2>
        <p>Cadastre a escala base dentro do paciente para liberar a visualização operacional aqui.</p>
    </section>
    <?php else: ?>
    <?php
    $pac = $pacienteGrade['paciente'] ?? [];
    $statusAprovacao = strtolower((string)($aprovacaoPeriodo['status'] ?? ''));
    $periodoFechado = in_array($statusAprovacao, ['fechada', 'finalizada'], true);
    $periodoAprovado = in_array($statusAprovacao, ['aprovada', 'aprovado', 'confirmado', 'confirmada', 'ok'], true);
    $financeiroQuery = http_build_query([
        'data_inicio' => $periodoInicio ?? date('Y-m-01'),
        'data_fim' => $periodoFim ?? date('Y-m-t'),
        'data_vencimento' => date('Y-m-d'),
    ]);
    ?>

    <section class="escala-paciente-header">
        <div class="escala-paciente-identidade">
            <div class="escala-avatar"><?= e(strtoupper(substr((string)($pac['nome_completo'] ?? 'P'), 0, 1))) ?>
            </div>
            <div>
                <span>Paciente selecionado</span>
                <h2><?= e($pac['nome_completo'] ?? 'Paciente') ?></h2>
                <p>
                    Prontuário: <strong><?= e($pac['prontuario'] ?? '—') ?></strong>
                    · Contrato: <strong><?= e($pac['tipo_cobertura'] ?? '—') ?></strong>
                    · Local: <strong><?= e($pac['tipo_atendimento'] ?? '—') ?></strong>
                </p>
            </div>
        </div>

        <div class="escala-paciente-meta">
            <span>Vigência</span>
            <strong><?= escala_data_br($pac['contrato_data_inicio'] ?? null) ?> até
                <?= escala_data_br($pac['contrato_data_fim'] ?? null) ?></strong>
            <?php if (!empty($aprovacaoPeriodo)): ?>
            <small class="escala-aprovacao-badge">
                Período <?= e($aprovacaoPeriodo['status'] ?? 'aprovado') ?>
                <?= !empty($aprovacaoPeriodo['aprovado_em']) ? 'em ' . e(date('d/m/Y H:i', strtotime((string)$aprovacaoPeriodo['aprovado_em']))) : '' ?>
            </small>
            <?php endif; ?>
            <div class="escala-paciente-actions">
                <?php if (!$periodoFechado && !$escopoCuidador): ?>
                <form method="POST" action="<?= url('/escala/aprovar') ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="paciente_uuid" value="<?= e($pac['uuid'] ?? '') ?>">
                    <input type="hidden" name="modo" value="<?= e($modo) ?>">
                    <input type="hidden" name="periodo" value="<?= e($filtros['periodo'] ?? date('Y-m-d')) ?>">
                    <button type="submit"
                        class="btn btn-primary"><?= !empty($aprovacaoPeriodo) ? 'Reconfirmar período' : 'Confirmar escala do período' ?></button>
                </form>
                <?php endif; ?>

                <?php if ($periodoAprovado && !$periodoFechado && !$escopoCuidador): ?>
                <form method="POST" action="<?= url('/escala/fechar') ?>"
                    onsubmit="return confirm('Fechar a escala deste período? Os plantões confirmados serão finalizados e liberados para geração do financeiro dos cuidadores.');">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="paciente_uuid" value="<?= e($pac['uuid'] ?? '') ?>">
                    <input type="hidden" name="modo" value="<?= e($modo) ?>">
                    <input type="hidden" name="periodo" value="<?= e($filtros['periodo'] ?? date('Y-m-d')) ?>">
                    <button type="submit" class="btn btn-secondary">Fechar escala</button>
                </form>
                <?php endif; ?>

                <?php if ($periodoFechado && !$escopoCuidador): ?>
                <form method="POST" action="<?= url('/escala/cancelar-fechamento') ?>"
                    onsubmit="return confirm('Cancelar o fechamento deste período? Os plantões voltarão para confirmado e poderão ser ajustados. Se já houver financeiro gerado, o sistema vai bloquear.');">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="paciente_uuid" value="<?= e($pac['uuid'] ?? '') ?>">
                    <input type="hidden" name="modo" value="<?= e($modo) ?>">
                    <input type="hidden" name="periodo" value="<?= e($filtros['periodo'] ?? date('Y-m-d')) ?>">
                    <button type="submit" class="btn btn-secondary">Cancelar fechamento</button>
                </form>
                <a class="btn btn-primary" href="<?= url('/financeiro/contas-pagar/gerar') . '?' . e($financeiroQuery) ?>">
                    Gerar financeiro
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if ($periodoFechado): ?>
    <section class="escala-fechamento-card escala-fechamento-card--ok">
        <div>
            <span>Período fechado</span>
            <strong>Plantões finalizados e prontos para gerar contas a pagar.</strong>
            <p>Agora o financeiro dos cuidadores usa somente os plantões finalizados deste período. Bonito, seguro e sem pagar antes da hora.</p>
        </div>
        <div class="escala-fechamento-actions">
            <?php if (!$escopoCuidador): ?>
            <form method="POST" action="<?= url('/escala/cancelar-fechamento') ?>"
                onsubmit="return confirm('Cancelar o fechamento deste período? Os plantões voltarão para confirmado e poderão ser ajustados.');">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="paciente_uuid" value="<?= e($pac['uuid'] ?? '') ?>">
                <input type="hidden" name="modo" value="<?= e($modo) ?>">
                <input type="hidden" name="periodo" value="<?= e($filtros['periodo'] ?? date('Y-m-d')) ?>">
                <button type="submit" class="btn btn-secondary">Cancelar fechamento</button>
            </form>
            <a class="btn btn-primary" href="<?= url('/financeiro/contas-pagar/gerar') . '?' . e($financeiroQuery) ?>">Gerar financeiro dos cuidadores</a>
            <?php endif; ?>
        </div>
    </section>
    <?php elseif ($periodoAprovado): ?>
    <section class="escala-fechamento-card">
        <div>
            <span>Próximo passo</span>
            <strong>Fechar escala / finalizar plantões</strong>
            <p>Faça este fechamento no fim do período para liberar a geração do contas a pagar dos cuidadores.</p>
        </div>
    </section>
    <?php endif; ?>

    <section class="escala-calendar escala-calendar--<?= e($modo) ?>">
        <?php foreach ($dias as $dia): ?>
        <?php
                $data = $dia['data'];
                $plantoes = $plantaoPorData[$data] ?? [];
                ?>
        <article class="escala-day <?= !empty($dia['is_hoje']) ? 'is-today' : '' ?>">
            <header class="escala-day__head">
                <span><?= e($dia['semana_curta'] ?? '') ?></span>
                <strong><?= e($dia['dia'] ?? '') ?></strong>
            </header>

            <div class="escala-day__body">
                <?php if (empty($plantoes)): ?>
                <div class="escala-no-shift">Sem escala</div>
                <?php endif; ?>

                <?php foreach ($plantoes as $plantao): ?>
                <?php
                            $col = $plantao['colaborador'] ?? null;
                            $cor = escala_cor($col);
                            $nome = escala_nome_curto($col['nome'] ?? null);
                            $status = $plantao['status'] ?? 'vago';
                            $turnoCodigo = (string)($plantao['turno_codigo'] ?? '');
                            $turnoForm = match ($turnoCodigo) {
                                '24h' => '24h',
                                'noturno' => 'noturno',
                                'diurno' => 'diurno',
                                default => 'personalizado',
                            };
                            ?>
                <div class="escala-shift escala-shift--<?= e($status) ?> <?= $periodoFechado ? 'escala-shift--locked' : '' ?>" style="--cuidador-cor: <?= e($cor) ?>"
                    draggable="<?= $periodoFechado ? 'false' : 'true' ?>" data-escala-id="<?= e($plantao['escala_id'] ?? '') ?>"
                    data-paciente-uuid="<?= e($pac['uuid'] ?? '') ?>" data-data="<?= e($data) ?>"
                    data-turno="<?= e($plantao['turno_codigo'] ?? '') ?>">
                    <div class="escala-shift__bar"></div>
                    <div class="escala-shift__content">
                        <div class="escala-shift__name">
                            <span class="escala-color-dot"></span>
                            <strong><?= e($nome) ?></strong>
                        </div>
                        <div class="escala-shift__time">
                            <?= e(escala_hora($plantao['hora_inicio'] ?? null)) ?> -
                            <?= e(escala_hora($plantao['hora_fim'] ?? null)) ?>
                        </div>
                        <div class="escala-shift__status"><?= e($plantao['status_label'] ?? '') ?></div>

                        <div class="escala-shift__actions">
                            <?php if ($periodoFechado): ?>
                            <span class="escala-locked-badge" title="Período fechado"><i class="ti ti-lock" aria-hidden="true"></i> Fechado</span>
                            <?php elseif ($escopoCuidador): ?>
                            <span class="escala-locked-badge" title="Somente visualização"><i class="ti ti-eye" aria-hidden="true"></i> Visualização</span>
                            <?php elseif (!empty($plantao['escala_id'])): ?>
                            <button type="button" class="mini-btn mini-btn--icon js-escala-editar"
                                data-id="<?= e($plantao['escala_id']) ?>" data-paciente="<?= e($pac['uuid'] ?? '') ?>"
                                data-data="<?= e($data) ?>" data-inicio="<?= e($plantao['hora_inicio'] ?? '') ?>"
                                data-fim="<?= e($plantao['hora_fim'] ?? '') ?>"
                                data-cuidador="<?= e($col['uuid'] ?? '') ?>" data-turno="<?= e($turnoForm) ?>"
                                title="Editar plantão" aria-label="Editar plantão"><i class="ti ti-pencil"
                                    aria-hidden="true"></i></button>
                            <form method="POST" action="<?= url('/escala/excluir') ?>"
                                onsubmit="return confirm('Excluir este plantão?')">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= e($plantao['escala_id']) ?>">
                                <button type="submit" class="mini-btn mini-btn--icon mini-btn--danger"
                                    title="Excluir plantão" aria-label="Excluir plantão"><i class="ti ti-trash"
                                        aria-hidden="true"></i></button>
                            </form>
                            <button type="button" class="mini-btn mini-btn--icon js-escala-substituir"
                                data-id="<?= e($plantao['escala_id']) ?>" data-data="<?= e($data) ?>"
                                data-cuidador="<?= e($col['uuid'] ?? '') ?>"
                                data-cuidador-id="<?= e($col['id'] ?? '') ?>" title="Substituir cuidador"
                                aria-label="Substituir cuidador"><i class="ti ti-arrows-sort"
                                    aria-hidden="true"></i></button>
                            <?php elseif (!empty($col['uuid'])): ?>
                            <form method="POST" action="<?= url('/escala/salvar') ?>">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="paciente_uuid" value="<?= e($pac['uuid'] ?? '') ?>">
                                <input type="hidden" name="cuidador_uuid" value="<?= e($col['uuid'] ?? '') ?>">
                                <input type="hidden" name="data_plantao" value="<?= e($data) ?>">
                                <input type="hidden" name="inicio" value="<?= e($plantao['hora_inicio'] ?? '') ?>">
                                <input type="hidden" name="fim" value="<?= e($plantao['hora_fim'] ?? '') ?>">
                                <input type="hidden" name="turno" value="<?= e($turnoForm) ?>">
                                <button type="submit" class="mini-btn">Confirmar</button>
                            </form>
                            <?php else: ?>
                            <button type="button" class="mini-btn js-escala-editar"
                                data-paciente="<?= e($pac['uuid'] ?? '') ?>" data-data="<?= e($data) ?>"
                                data-inicio="<?= e($plantao['hora_inicio'] ?? '') ?>"
                                data-fim="<?= e($plantao['hora_fim'] ?? '') ?>"
                                data-turno="<?= e($turnoForm) ?>">Definir</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </section>

    <section class="escala-historico-card">
        <div class="escala-historico-card__head">
            <div>
                <span class="escala-eyebrow">Histórico operacional</span>
                <h5>Movimentações do período</h5>
                <p>Substituições, aprovações e ajustes feitos nesta grade ficam registrados aqui.</p>
            </div>
            <strong><?= count($historicoEscala) ?></strong>
        </div>

        <?php if (empty($historicoEscala)): ?>
        <div class="escala-historico-empty">
            Nenhuma movimentação registrada para este período.
        </div>
        <?php else: ?>
        <div class="escala-historico-list">
            <?php foreach ($historicoEscala as $item): ?>
            <?php $tipoHistorico = escala_tipo_historico_classe($item['tipo'] ?? 'registro'); ?>
            <article class="escala-historico-item escala-historico-item--<?= e($tipoHistorico) ?>">
                <div class="escala-historico-item__icon">
                    <?= e(escala_tipo_historico_icone($item['tipo'] ?? null)) ?>
                </div>
                <div class="escala-historico-item__body">
                    <div class="escala-historico-item__top">
                        <strong><?= e($item['titulo'] ?? 'Registro da escala') ?></strong>
                        <span><?= e(escala_tipo_historico_label($item['tipo'] ?? null)) ?></span>
                    </div>

                    <p><?= e($item['detalhe'] ?? '') ?></p>

                    <div class="escala-historico-item__meta">
                        <?php if (!empty($item['data_plantao'])): ?>
                        <span>Plantão: <?= e(escala_data_br((string)$item['data_plantao'])) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($item['data'])): ?>
                        <span>Registro: <?= e(escala_data_hora_br((string)$item['data'])) ?></span>
                        <?php endif; ?>

                        <?php if (!empty($item['motivo'])): ?>
                        <span>Motivo: <?= e($item['motivo']) ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($item['observacoes'])): ?>
                    <div class="escala-historico-item__obs">
                        <?= e($item['observacoes']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/partials/modal_criar.php'; ?>
<?php include __DIR__ . '/partials/modal_substituicao.php'; ?>
<script>
window.APP_BASE_URL = "<?= BASE_URL ?>";
</script>
<script src="<?= BASE_URL ?>/assets/js/escalas.js"></script>