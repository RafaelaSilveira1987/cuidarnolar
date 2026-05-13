<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Quatro camadas: contrato → lançamentos (caixa) → contas a pagar/receber → relatórios.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/financeiro/lancamentos') ?>">Ir para lançamentos</a>
</section>

<section class="panel fin-camadas">
    <h2>Visão geral</h2>
    <ol class="fin-camadas__lista">
        <li><strong>Contrato / plano</strong> — valor mensal, tipo de serviço, vencimento; base para gerar parcelas (próxima etapa).</li>
        <li><strong>Lançamentos</strong> — entradas e saídas efetivas ou cadastradas (o que o legado já registra em <code>tb_financeiro</code>).</li>
        <li><strong>Contas a receber / a pagar</strong> — pendências com vencimento; alimenta alertas do painel.</li>
        <li><strong>Relatórios</strong> — extrato por paciente, fluxo de caixa, inadimplência e DRE simplificado.</li>
    </ol>
    <p class="page-subtitle">Execute o script SQL em <code>database/sql/financeiro_homecare.sql</code> para criar categorias, contratos e campos de vencimento/pagamento.</p>
</section>

<section class="content-grid">
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/lancamentos') ?>">
        <strong>Lançamentos</strong>
        <span>Caixa — entradas e saídas com filtros</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/contas-receber') ?>">
        <strong>Contas a receber</strong>
        <span>Entradas pendentes de liquidação</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/contas-pagar') ?>">
        <strong>Contas a pagar</strong>
        <span>Saídas pendentes (cuidador, insumos…)</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/contratos') ?>">
        <strong>Contratos</strong>
        <span>Plano por paciente (vigência e valor mensal)</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/relatorios/extrato') ?>">
        <strong>Extrato por paciente</strong>
        <span>Demonstrativo para família</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/relatorios/fluxo-caixa') ?>">
        <strong>Fluxo de caixa</strong>
        <span>Entradas vs saídas por mês</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/relatorios/inadimplencia') ?>">
        <strong>Inadimplência</strong>
        <span>Receitas vencidas</span>
    </a>
    <a class="metric-card fin-hub-card" href="<?= url('/financeiro/relatorios/dre') ?>">
        <strong>DRE simplificado</strong>
        <span>Receita − custos de cuidadores − operacional</span>
    </a>
</section>
