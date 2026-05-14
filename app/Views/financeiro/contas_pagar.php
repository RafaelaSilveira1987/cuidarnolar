<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Saídas pendentes — cuidador, insumos, transporte etc.</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/financeiro/novo') ?>">Novo lançamento</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <?php if (empty($rows)): ?>
    <p class="empty-state">Nenhuma conta a pagar pendente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $label): ?>
                    <th><?= e($label) ?></th>
                    <?php endforeach; ?>
                    <th>Venc.</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($columns as $field => $label): ?>
                    <td><?= e($row[$field] ?? '-') ?></td>
                    <?php endforeach; ?>
                    <td><?= e($row['vencimento_exibicao'] ?? '-') ?></td>
                    <td class="actions">
                        <a href="<?= url('/financeiro/' . (int) $row['id']) ?>">Ver</a>
                        <a href="<?= url('/financeiro/' . (int) $row['id'] . '/editar') ?>">Pagar / editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>