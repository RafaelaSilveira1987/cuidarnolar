<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle"><?= (int) ($pagination['total'] ?? 0) ?> registros encontrados</p>
    </div>
    <a class="btn btn-primary" href="<?= url($routeBase . '/novo') ?>">Novo</a>
</section>

<section class="panel">
    <?php if (!empty($tabs)): ?>
        <nav class="tabs" aria-label="Filtros">
            <?php foreach ($tabs as $tabValue => $tabLabel): ?>
                <a class="<?= ($activeTab ?? '') === (string) $tabValue ? 'active' : '' ?>" href="<?= url($routeBase . ($tabValue !== '' ? '?tipo=' . urlencode((string) $tabValue) : '')) ?>">
                    <?= e($tabLabel) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <form class="search-form" method="GET" action="<?= url($routeBase) ?>">
        <?php if (isset($activeTab) && $activeTab !== ''): ?>
            <input type="hidden" name="tipo" value="<?= e($activeTab) ?>">
        <?php endif; ?>
        <input type="search" name="busca" value="<?= e($search ?? '') ?>" placeholder="Buscar...">
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
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $field => $label): ?>
                                <td><?= e($row[$field] ?? '-') ?></td>
                            <?php endforeach; ?>
                            <td class="actions">
                                <a href="<?= url($routeBase . '/' . $row['id']) ?>">Ver</a>
                                <a href="<?= url($routeBase . '/' . $row['id'] . '/editar') ?>">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (($pagination['last_page'] ?? 1) > 1): ?>
            <nav class="pagination" aria-label="Paginacao">
                <?php for ($page = 1; $page <= $pagination['last_page']; $page++): ?>
                    <a class="<?= $page === $pagination['current_page'] ? 'active' : '' ?>" href="<?= url($routeBase . '?page=' . $page . ($search ? '&busca=' . urlencode($search) : '') . (!empty($activeTab) ? '&tipo=' . urlencode($activeTab) : '')) ?>">
                        <?= $page ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
