<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Receitas pagas − custos com cuidadores (saídas pagas com cuidador) − despesas
            operacionais (saídas pagas sem cuidador).</p>
    </div>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <form class="search-form" method="GET" action="<?= url('/financeiro/relatorios/dre') ?>">
        <label>
            De
            <input type="date" name="di" value="<?= e($di ?? '') ?>" required>
        </label>
        <label>
            Até
            <input type="date" name="df" value="<?= e($df ?? '') ?>" required>
        </label>
        <button class="btn btn-primary" type="submit">Atualizar</button>
    </form>
</section>

<section class="panel">
    <?php $d = $dre ?? []; ?>
    <dl class="summary-list">
        <div>
            <dt>Receita líquida (entradas pagas)</dt>
            <dd><?= formatMoney((float) ($d['receita_bruta'] ?? 0)) ?></dd>
        </div>
        <div>
            <dt>Custos com cuidadores</dt>
            <dd><?= formatMoney((float) ($d['custos_cuidadores'] ?? 0)) ?></dd>
        </div>
        <div>
            <dt>Despesas operacionais</dt>
            <dd><?= formatMoney((float) ($d['despesas_operacionais'] ?? 0)) ?></dd>
        </div>
        <div>
            <dt>Resultado</dt>
            <dd><strong><?= formatMoney((float) ($d['resultado'] ?? 0)) ?></strong></dd>
        </div>
    </dl>
</section>