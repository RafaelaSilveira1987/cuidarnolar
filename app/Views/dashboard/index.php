<?php
$resumo ??= [];
$alertasOperacionais ??= [];
$alertasFinanceiros ??= [];
$operacaoHoje ??= [];
$proximosEventos ??= [];
$hoje = date('d/m/Y');
?>

<link rel="stylesheet" href="<?= url('/assets/css/dashboard_v22.css') ?>">

<section class="dash-hero-v22">
    <div>
        <span class="dash-kicker">Painel de comando</span>
        <h1>Dashboard</h1>
        <p>Visão rápida da operação, escala, planos de cuidado e pendências importantes.</p>
    </div>
    <div class="dash-date-pill"><?= e($hoje) ?></div>
</section>

<section class="dash-metric-grid-v22">
    <a class="dash-metric-v22" href="<?= url('/escala') ?>">
        <span>Plantões hoje</span>
        <strong><?= (int)($resumo['plantoes_hoje'] ?? 0) ?></strong>
        <small>Agenda operacional do dia</small>
    </a>

    <a class="dash-metric-v22" href="<?= url('/escala') ?>">
        <span>Escalas pendentes</span>
        <strong><?= (int)($resumo['escalas_pendentes'] ?? 0) ?></strong>
        <small>Sugeridas, previstas ou pendentes</small>
    </a>

    <a class="dash-metric-v22 dash-metric-v22--warn" href="<?= url('/financeiro/contas-pagar/gerar') ?>">
        <span>Aguardando financeiro</span>
        <strong><?= (int)($resumo['fechadas_aguardando_financeiro'] ?? 0) ?></strong>
        <small>Escalas fechadas sem contas a pagar</small>
    </a>

    <a class="dash-metric-v22" href="<?= url('/pacientes') ?>">
        <span>Planos em rascunho</span>
        <strong><?= (int)($resumo['planos_rascunho'] ?? 0) ?></strong>
        <small>Planos de cuidado aguardando ativação</small>
    </a>
</section>

<section class="panel dash-panel-v22">
    <div class="dash-panel-head-v22">
        <div>
            <h2>Operação de hoje</h2>
            <p>Plantões registrados para o dia atual.</p>
        </div>
        <a href="<?= url('/escala') ?>">Ver escala</a>
    </div>

    <?php if (empty($operacaoHoje)): ?>
    <p class="empty-state">Nenhum plantão encontrado para hoje.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table dash-table-v22">
            <thead>
                <tr>
                    <th>Horário</th>
                    <th>Paciente</th>
                    <th>Cuidador</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operacaoHoje as $plantao): ?>
                <tr>
                    <td><?= e(($plantao['inicio_hora'] ?? '--:--') . ' às ' . ($plantao['fim_hora'] ?? '--:--')) ?></td>
                    <td><?= e($plantao['paciente_nome'] ?? 'Paciente não informado') ?></td>
                    <td><?= e($plantao['cuidador_nome'] ?? 'Sem cuidador') ?></td>
                    <td><span class="dash-status-pill"><?= e($plantao['status'] ?? '-') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section class="panel dash-panel-v22">
    <div class="dash-panel-head-v22">
        <div>
            <h2>Próximos agendamentos</h2>
            <p>Eventos futuros cadastrados na agenda.</p>
        </div>
        <a href="<?= url('/agendamentos') ?>">Ver agenda</a>
    </div>

    <?php if (empty($proximosEventos)): ?>
    <p class="empty-state">Nenhum agendamento futuro encontrado.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table dash-table-v22">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Título</th>
                    <th>Paciente</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proximosEventos as $evento): ?>
                <tr>
                    <td><?= e(formatDate($evento['data_evento'] ?? '')) ?></td>
                    <td><?= e($evento['titulo'] ?? '') ?></td>
                    <td><?= e($evento['paciente_nome'] ?? '-') ?></td>
                    <td><span class="dash-status-pill"><?= e($evento['status'] ?? 'Pendente') ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<section class="dash-grid-v22 dash-grid-v22--two">
    <article class="panel dash-panel-v22">
        <div class="dash-panel-head-v22">
            <div>
                <h2>Alertas financeiros</h2>
                <p>Somente notificações. Valores ficam dentro do módulo Financeiro.</p>
            </div>
            <a href="<?= url('/financeiro') ?>">Abrir financeiro</a>
        </div>

        <div class="dash-alert-list-v22">
            <?php foreach ($alertasFinanceiros as $item): ?>
            <a class="dash-alert-v22 dash-alert-v22--<?= e($item['tipo'] ?? 'info') ?>"
                href="<?= url($item['rota'] ?? '/financeiro') ?>">
                <strong><?= (int)($item['valor'] ?? 0) ?></strong>
                <span>
                    <b><?= e($item['titulo'] ?? '') ?></b>
                    <small><?= e($item['descricao'] ?? '') ?></small>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel dash-panel-v22">
        <div class="dash-panel-head-v22">
            <div>
                <h2>Alertas operacionais</h2>
                <p>Itens que precisam de atenção no fluxo do home care.</p>
            </div>
        </div>

        <div class="dash-alert-list-v22">
            <?php foreach ($alertasOperacionais as $item): ?>
            <a class="dash-alert-v22 dash-alert-v22--<?= e($item['tipo'] ?? 'info') ?>"
                href="<?= url($item['rota'] ?? '#') ?>">
                <strong><?= (int)($item['valor'] ?? 0) ?></strong>
                <span>
                    <b><?= e($item['titulo'] ?? '') ?></b>
                    <small><?= e($item['descricao'] ?? '') ?></small>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </article>
</section>