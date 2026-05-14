<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle"><?= (int) ($pagination['total'] ?? 0) ?> registros</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/financeiro/novo') ?>">Novo lançamento</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <?php if (!empty($tabs)): ?>
    <nav class="tabs" aria-label="Filtros">
        <?php foreach ($tabs as $tabValue => $tabLabel): ?>
        <?php $isActive = (($activeTab ?? '') === (string)$tabValue); ?>
        <a class="<?= $isActive ? 'active' : '' ?>"
            href="<?= url('/financeiro/lancamentos' . ($tabValue !== '' ? '?tipo=' . urlencode((string) $tabValue) : '')) ?>">
            <?= e($tabLabel) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <form class="search-form" method="GET" action="<?= url('/financeiro/lancamentos') ?>">
        <?php if (isset($activeTab) && $activeTab !== ''): ?>
        <input type="hidden" name="tipo" value="<?= e($activeTab) ?>">
        <?php endif; ?>
        <input type="search" name="busca" <?php $searchValue = $search ?? ''; ?> value="<?= e($searchValue) ?>"
            placeholder="Paciente, responsável ou cuidador...">
        <button class="btn btn-secondary" type="submit">Buscar</button>
    </form>

    <?php if (empty($rows)): ?>
    <p class="empty-state">Nenhum registro encontrado.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $label): ?>
                    <th><?= e($label) ?></th>
                    <?php endforeach; ?>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <?php foreach ($columns as $field => $label): ?>
                    <?php $cell = $row[$field] ?? '-'; ?>
                    <td><?= e($cell) ?></td>

                    <?php endforeach; ?>
                    <td class="actions">
                        <a href="<?= url('/financeiro/' . (int) $row['id']) ?>">Ver</a>
                        <a href="<?= url('/financeiro/' . (int) $row['id'] . '/editar') ?>">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
    <nav class="pagination" aria-label="Paginacao">
        <?php for ($page = 1; $page <= $pagination['last_page']; $page++): ?>

        <?php
                    $query = '/financeiro/lancamentos?page=' . $page;

                    if (!empty($search)) {
                        $query .= '&busca=' . urlencode($search);
                    }

                    if (!empty($activeTab)) {
                        $query .= '&tipo=' . urlencode($activeTab);
                    }
                    ?>

        <a class="<?= $page === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>" href="<?= url($query) ?>">
            <?= $page ?>
        </a>

        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</section>