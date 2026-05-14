<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Entradas pendentes com vencimento anterior a hoje (use data de vencimento após migrar o
            SQL).</p>
    </div>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <?php if (empty($linhas)): ?>
    <p class="empty-state">Nenhuma conta vencida pendente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Observações</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($linhas as $r): ?>
                <?php $v = $r['data_vencimento'] ?? (substr((string) ($r['data'] ?? ''), 0, 10)); ?>
                <tr>
                    <td><?= (int) ($r['id'] ?? 0) ?></td>
                    <td><?= e($r['paciente_nome'] ?? '—') ?></td>
                    <td><?= e($v !== '' && $v !== null ? formatDate((string) $v) : '—') ?></td>
                    <td><?= formatMoney((float) ($r['valor'] ?? 0)) ?></td>
                    <td><?= e(strlen((string) ($r['observacoes'] ?? '')) > 60 ? substr((string) ($r['observacoes'] ?? ''), 0, 60) . '…' : (string) ($r['observacoes'] ?? '')) ?>
                    </td>
                    <td><a href="<?= url('/financeiro/' . (int) ($r['id'] ?? 0) . '/editar') ?>">Registrar pagamento</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>