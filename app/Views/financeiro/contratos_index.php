<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Camada 1 — valor mensal e vigência (parcelas automáticas: próxima implementação).</p>
    </div>
    <a class="btn btn-primary" href="<?= url('/financeiro/contratos/novo') ?>">Novo contrato</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <form class="search-form" method="GET" action="<?= url('/financeiro/contratos') ?>">
        <input type="search" name="busca" value="<?= e($search ?? '') ?>" placeholder="Paciente ou tipo de serviço...">
        <button class="btn btn-secondary" type="submit">Buscar</button>
    </form>

    <?php if (empty($rows)): ?>
    <p class="empty-state">Nenhum contrato cadastrado. Após rodar o SQL, cadastre o primeiro contrato aqui.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Serviço</th>
                    <th>Valor mensal</th>
                    <th>Venc. (dia)</th>
                    <th>Vigência</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= (int) ($row['id'] ?? 0) ?></td>
                    <td><?= e($row['paciente_nome'] ?? '') ?></td>
                    <td><?= e($row['tipo_servico'] ?? '') ?></td>
                    <td><?= e($row['valor_mensal_fmt'] ?? '') ?></td>
                    <td><?= (int) ($row['dia_vencimento'] ?? 0) ?></td>
                    <td><?= e(formatDate($row['vigencia_inicio'] ?? '')) ?>
                        <?php if (!empty($row['vigencia_fim'])): ?> —
                        <?= e(formatDate($row['vigencia_fim'])) ?><?php endif; ?>
                    </td>
                    <td><?= e($row['status'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (($pagination['last_page'] ?? 1) > 1): ?>
    <nav class="pagination" aria-label="Paginacao">
        <?php for ($page = 1; $page <= $pagination['last_page']; $page++): ?>
        <a class="<?= $page === $pagination['current_page'] ? 'active' : '' ?>"
            href="<?= url('/financeiro/contratos?page=' . $page . ($search ? '&busca=' . urlencode($search) : '')) ?>">
            <?= $page ?>
        </a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</section>