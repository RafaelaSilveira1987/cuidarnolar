<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Agregação mensal na data do lançamento.</p>
    </div>
</section>

<section class="panel">
    <form class="search-form" method="GET" action="<?= url('/financeiro/relatorios/fluxo-caixa') ?>">
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
    <?php if (empty($meses)): ?>
    <p class="empty-state">Sem dados no período.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th>Entradas</th>
                    <th>Saídas</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meses as $m): ?>
                <?php
                    $e = (float) ($m['entradas'] ?? 0);
                    $s = (float) ($m['saidas'] ?? 0);
                    ?>
                <tr>
                    <td><?= e($m['mes'] ?? '') ?></td>
                    <td><?= formatMoney($e) ?></td>
                    <td><?= formatMoney($s) ?></td>
                    <td><?= formatMoney($e - $s) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
