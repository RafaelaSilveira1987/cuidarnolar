<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Entradas com status <strong>Pendente</strong> — use data de vencimento para
            inadimplência.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/financeiro/novo') ?>">Novo lançamento</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <?php if (empty($rows)): ?>
    <p class="empty-state">Nenhuma conta a receber pendente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table finance-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $label): ?>
                    <th><?= e($label) ?></th>
                    <?php endforeach; ?>
                    <th>Venc.</th>
                    <th>Mês ref.</th>
                    <th>Origem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr class="<?= !empty($row['atrasado']) ? 'is-overdue' : '' ?>">
                    <?php foreach ($columns as $field => $label): ?>
                    <?php
                        $valorCampo = $row[$field] ?? '-';
                        if ($field === 'data') {
                            $valorCampo = $row['data_exibicao'] ?? '-';
                        }
                    ?>
                    <td><?= e($valorCampo) ?></td>
                    <?php endforeach; ?>
                    <td><?= e($row['vencimento_exibicao'] ?? '-') ?></td>
                    <td><?= e($row['mes_referencia'] ?? '-') ?></td>
                    <td><?= e($row['origem'] ?? 'manual') ?></td>
                    <td class="actions finance-actions">
                        <a class="btn-table btn-table--ghost" href="<?= url('/financeiro/' . rawurlencode((string)($row['uuid'] ?? $row['id']))) ?>">Ver</a>
                        <a class="btn-table btn-table--primary" href="<?= url('/financeiro/' . rawurlencode((string)($row['uuid'] ?? $row['id'])) . '/receber') ?>">Receber</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
