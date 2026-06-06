<section class="page-header">
    <div>
        <h1><?= e($title ?? 'Registros') ?></h1>
        <p class="page-subtitle">
            <?= (int) ($pagination['total'] ?? 0) ?> registros encontrados
        </p>
    </div>

    <a class="btn btn-primary" href="<?= url($routeBase . '/novo') ?>">
        Novo
    </a>
</section>

<section class="panel">

    <?php if (!empty($tabs)): ?>
    <nav class="tabs" aria-label="Filtros">
        <?php foreach ($tabs as $tabValue => $tabLabel): ?>
        <a class="<?= ($activeTab ?? '') === (string) $tabValue ? 'active' : '' ?>"
            href="<?= url($routeBase . ($tabValue !== '' ? '?tipo=' . urlencode((string) $tabValue) : '')) ?>">
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

        <button class="btn btn-secondary" type="submit">
            Buscar
        </button>
    </form>

    <?php if (empty($rows)): ?>

    <p class="empty-state">Nenhum registro encontrado.</p>

    <?php else: ?>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($columns as $field => $label): ?>
                    <?php if ($field === 'id') {
                                continue;
                            } ?>

                    <?php
                            $isPacientesList = (($routeBase ?? '') === '/pacientes');
                            $isResponsavelField = in_array($field, [
                                'responsavel_nome',
                                'responsavel_nome_texto',
                                'responsavel',
                                'responsavel_legal',
                                'responsavel_financeiro',
                            ], true);

                            $thClass = ($isPacientesList && $isResponsavelField) ? 'col-responsavel' : '';
                            ?>

                    <th class="<?= e($thClass) ?>"><?= e($label) ?></th>

                    <?php endforeach; ?>

                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $row): ?>

                <?php
                        $resourceKey = $row['uuid'] ?? $row['id'] ?? '';
                        ?>

                <tr class="<?= !empty($row['atrasado']) ? 'row-danger' : '' ?>">

                    <?php foreach ($columns as $field => $label): ?>
                    <?php if ($field === 'id') {
                                    continue;
                                } ?>

                    <?php
                                $isPacientesList = (($routeBase ?? '') === '/pacientes');
                                $isResponsavelField = in_array($field, [
                                    'responsavel_nome',
                                    'responsavel_nome_texto',
                                    'responsavel',
                                    'responsavel_legal',
                                    'responsavel_financeiro',
                                ], true);

                                $cellClass = ($isPacientesList && $isResponsavelField) ? 'col-responsavel' : '';
                                $cellValue = (string)($row[$field] ?? '-');
                                ?>

                    <td class="<?= e($cellClass) ?>">
                        <?php if ($isPacientesList && $isResponsavelField): ?>
                        <span class="cell-ellipsis" title="<?= e($cellValue) ?>" data-full="<?= e($cellValue) ?>">
                            <?= e($cellValue) ?>
                        </span>
                        <?php else: ?>
                        <?= e($cellValue) ?>
                        <?php endif; ?>

                        <?php if ($field === 'status' && !empty($row['atrasado'])): ?>
                        <span class="badge-danger">
                            ● Em atraso
                        </span>
                        <?php endif; ?>
                    </td>

                    <?php endforeach; ?>
                    <td class="actions">
                        <a href="<?= url($routeBase . '/' . rawurlencode((string) $resourceKey)) ?>">
                            Ver
                        </a>

                        <a href="<?= url($routeBase . '/' . rawurlencode((string) $resourceKey) . '/editar') ?>">
                            Editar
                        </a>
                    </td>

                </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
    <nav class="pagination" aria-label="Paginacao">
        <?php for ($page = 1; $page <= $pagination['last_page']; $page++): ?>
        <a class="<?= $page === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>"
            href="<?= url($routeBase . '?page=' . $page . (!empty($search) ? '&busca=' . urlencode((string)$search) : '') . (!empty($activeTab) ? '&tipo=' . urlencode((string)$activeTab) : '')) ?>">
            <?= $page ?>
        </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>

    <?php endif; ?>

</section>