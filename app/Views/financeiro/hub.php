<?php

/**
 * app/Views/financeiro/hub.php
 *
 * Hub principal do módulo Financeiro.
 * Recebe do controller:
 *   $resumo   — array com receitas, despesas, a_receber, resultado (floats)
 *   $alertas  — array de ['texto', 'detalhe'] com pendências em atraso
 *   $counts   — array com lancamentos, contratos_ativos, receber_vencidas, pagar_pendentes
 *   $mes_ref  — string ex: "Maio 2026"
 */

$resumo  ??= ['receitas' => 0, 'despesas' => 0, 'a_receber' => 0, 'resultado' => 0];
$alertas ??= [];
$counts  ??= ['lancamentos' => 0, 'contratos_ativos' => 0, 'receber_vencidas' => 0, 'pagar_pendentes' => 0];
$mes_ref ??= date('F Y');

function fmt(float $v): string
{
    return 'R$ ' . number_format(abs($v), 0, ',', '.');
}
?>

<link rel="stylesheet" href="<?= url('/assets/css/financeiro.css') ?>">
<link rel="stylesheet" href="<?= url('/assets/css/app.css') ?>">

<div class="hub">

    <!-- ── Cabeçalho ─────────────────────────────────────────── -->
    <div class="hub-header">
        <div>
            <div class="hub-title">Financeiro</div>
            <div class="hub-sub"><?= htmlspecialchars($mes_ref) ?></div>
        </div>
        <a href="<?= url('/financeiro/novo') ?>" class="btn-primary">
            <i class="ti ti-plus" aria-hidden="true"></i> Novo lançamento
        </a>
    </div>

    <!-- ── Navegação ──────────────────────────────────────────── -->
    <?php
    $finSubnav = 'hub';
    include __DIR__ . '/_subnav.php';
    ?>

    <!-- ── Métricas ───────────────────────────────────────────── -->
    <div class="metrics">
        <div class="metric metric--receita">
            <div class="metric__label">
                <i class="ti ti-arrow-down-circle" aria-hidden="true"></i> Receitas
            </div>
            <div class="metric__value"><?= fmt($resumo['receitas']) ?></div>
            <div class="metric__delta delta-up">
                <?= (int)$counts['lancamentos'] ?> lançamentos no mês
            </div>
        </div>

        <div class="metric metric--despesa">
            <div class="metric__label">
                <i class="ti ti-arrow-up-circle" aria-hidden="true"></i> Despesas
            </div>
            <div class="metric__value"><?= fmt($resumo['despesas']) ?></div>
            <div class="metric__delta delta-down">saídas confirmadas</div>
        </div>

        <div class="metric metric--pendente">
            <div class="metric__label">
                <i class="ti ti-clock" aria-hidden="true"></i> A receber
            </div>
            <div class="metric__value"><?= fmt($resumo['a_receber']) ?></div>
            <div class="metric__delta">
                <?= (int)$counts['receber_vencidas'] ?> vencimento(s) este mês
            </div>
        </div>

        <div class="metric metric--resultado">
            <div class="metric__label">
                <i class="ti ti-chart-line" aria-hidden="true"></i> Resultado
            </div>
            <div class="metric__value">
                <?= $resumo['resultado'] >= 0 ? '+ ' : '− ' ?><?= fmt($resumo['resultado']) ?>
            </div>
            <div class="metric__delta <?= $resumo['resultado'] >= 0 ? 'delta-up' : 'delta-down' ?>">
                <?= $resumo['resultado'] >= 0 ? '↑ Positivo no período' : '↓ Negativo no período' ?>
            </div>
        </div>
    </div>

    <!-- ── Alertas de inadimplência ───────────────────────────── -->
    <?php if ($alertas): ?>
    <div class="alert-strip">
        <i class="ti ti-alert-triangle alert-strip__icon" aria-hidden="true"></i>
        <div>
            <?php if(count($alertas) > 0): ?>

            <a href="<?= url('/financeiro/lancamentos?status=pendente&atrasado=1') ?>" class="alert-strip__text">

                <?= count($alertas) ?> pagamento(s) em atraso

            </a>

            <?php endif; ?>

        </div>
    </div>
    <?php endif ?>

    <!-- ── Cards de movimentação ──────────────────────────────── -->
    <div class="section-label">Movimentação</div>
    <div class="grid-2">

        <!-- Lançamentos -->
        <a href="<?= url('/financeiro/lancamentos') ?>" class="card">
            <div class="card__top">
                <div class="card__icon icon--brand">
                    <i class="ti ti-arrows-exchange" aria-hidden="true"></i>
                </div>
                <span class="card__badge badge-brand"><?= (int)$counts['lancamentos'] ?> registros</span>
            </div>
            <div>
                <div class="card__title">Lançamentos</div>
                <div class="card__desc">Entradas e saídas com filtros por data, tipo e status</div>
            </div>
            <div class="card__stat stat-brand"><?= (int)$counts['lancamentos'] ?> lançamentos</div>
            <div class="card__foot">
                <span class="card__foot-label"><?= htmlspecialchars($mes_ref) ?></span>
                <span class="card__arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
            </div>
        </a>

        <!-- Contas a receber -->
        <a href="<?= url('/financeiro/contas-receber') ?>" class="card">
            <div class="card__top">
                <div class="card__icon icon--green">
                    <i class="ti ti-arrow-down-circle" aria-hidden="true"></i>
                </div>
                <?php if ($counts['receber_vencidas'] > 0): ?>
                <span class="card__badge badge-amber"><?= (int)$counts['receber_vencidas'] ?> vencida(s)</span>
                <?php else: ?>
                <span class="card__badge badge-green">Em dia</span>
                <?php endif ?>
            </div>
            <div>
                <div class="card__title">Contas a receber</div>
                <div class="card__desc">Mensalidades e cobranças pendentes de liquidação</div>
            </div>
            <div class="card__stat stat-amber"><?= fmt($resumo['a_receber']) ?></div>
            <div class="card__foot">
                <span class="card__foot-label">Pendentes</span>
                <span class="card__arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
            </div>
        </a>

        <!-- Contas a pagar -->
        <a href="<?= url('/financeiro/contas-pagar') ?>" class="card">
            <div class="card__top">
                <div class="card__icon icon--red">
                    <i class="ti ti-arrow-up-circle" aria-hidden="true"></i>
                </div>
                <span class="card__badge badge-gray"><?= (int)$counts['pagar_pendentes'] ?> pendente(s)</span>
            </div>
            <div>
                <div class="card__title">Contas a pagar</div>
                <div class="card__desc">Salários de cuidadores, insumos e encargos pendentes</div>
            </div>
            <div class="card__stat stat-red"><?= fmt($resumo['despesas']) ?></div>
            <div class="card__foot">
                <span class="card__foot-label">A vencer</span>
                <span class="card__arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
            </div>
        </a>

        <!-- Contratos -->
        <a href="<?= url('/financeiro/contratos') ?>" class="card">
            <div class="card__top">
                <div class="card__icon icon--teal">
                    <i class="ti ti-file-text" aria-hidden="true"></i>
                </div>
                <span class="card__badge badge-green"><?= (int)$counts['contratos_ativos'] ?> ativo(s)</span>
            </div>
            <div>
                <div class="card__title">Contratos</div>
                <div class="card__desc">Plano mensal por paciente, vigência e valor acordado</div>
            </div>
            <div class="card__stat stat-brand"><?= (int)$counts['contratos_ativos'] ?> paciente(s)</div>
            <div class="card__foot">
                <span class="card__foot-label">Todos ativos</span>
                <span class="card__arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
            </div>
        </a>

    </div>

    <!-- ── Cards de relatórios ─────────────────────────────────── -->
    <div class="section-label" style="margin-top:24px">Relatórios</div>
    <div class="relatorios-row">

        <a href="<?= url('/financeiro/relatorios/extrato') ?>" class="rel-card">
            <div class="rel-card__icon">
                <i class="ti ti-user" style="color:#6366f1" aria-hidden="true"></i>
            </div>
            <div class="rel-card__title">Extrato por paciente</div>
            <div class="rel-card__desc">Demonstrativo para família</div>
        </a>

        <a href="<?= url('/financeiro/relatorios/fluxo-caixa') ?>" class="rel-card">
            <div class="rel-card__icon">
                <i class="ti ti-chart-line" style="color:#e255d0" aria-hidden="true"></i>
            </div>
            <div class="rel-card__title">Fluxo de caixa</div>
            <div class="rel-card__desc">Entradas vs saídas por mês</div>
        </a>

        <a href="<?= url('/financeiro/relatorios/inadimplencia') ?>" class="rel-card">
            <div class="rel-card__icon">
                <i class="ti ti-alert-circle" style="color:#f59e00" aria-hidden="true"></i>
            </div>
            <div class="rel-card__title">Inadimplência</div>
            <div class="rel-card__desc">Receitas vencidas e em atraso</div>
        </a>

        <a href="<?= url('/financeiro/relatorios/dre') ?>" class="rel-card">
            <div class="rel-card__icon">
                <i class="ti ti-chart-bar" style="color:#a78bfa" aria-hidden="true"></i>
            </div>
            <div class="rel-card__title">DRE simplificado</div>
            <div class="rel-card__desc">Receita menos custos operacionais</div>
        </a>

    </div>

</div><!-- /.hub -->

<script src="<?= url('/assets/js/financeiro.js') ?>"></script>