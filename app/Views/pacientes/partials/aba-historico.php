<?php
$pid = (int) ($record['id'] ?? 0);
$lista = $historicos ?? [];
?>
<section class="panel pac-panel-help">
    <h2>Histórico clínico</h2>
    <p class="page-subtitle">Linha do tempo de <strong>eventos marcantes</strong> — diferente do relatório de plantão (operacional/diário).</p>
    <ul class="pac-help-list">
        <li>Intercorrências graves, hospitalizações, quedas, cirurgias.</li>
        <li>Mudanças de diagnóstico ou conduta médica; visitas e condutas.</li>
        <li>Troca de cuidador com motivo; eventos familiares relevantes ao cuidado.</li>
    </ul>
    <p><a class="btn btn-primary" href="<?= url('/historicos/novo?paciente_id=' . $pid) ?>">Novo histórico</a>
        <a class="btn btn-secondary" href="<?= url('/historicos') ?>">Listagem global (admin)</a></p>
</section>

<section class="panel">
    <h2>Registros deste paciente</h2>
    <?php if ($lista === []): ?>
    <p class="empty-state">Nenhum histórico cadastrado para este paciente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Necessidades</th>
                    <th>Limitações</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $row): ?>
                <tr>
                    <td><?= (int) ($row['id'] ?? 0) ?></td>
                    <td><?= e(strlen((string) ($row['necessidades'] ?? '')) > 80 ? substr((string) ($row['necessidades'] ?? ''), 0, 80) . '…' : (string) ($row['necessidades'] ?? '')) ?></td>
                    <td><?= e(strlen((string) ($row['limitacoes'] ?? '')) > 80 ? substr((string) ($row['limitacoes'] ?? ''), 0, 80) . '…' : (string) ($row['limitacoes'] ?? '')) ?></td>
                    <td><?= e($row['status'] ?? '—') ?></td>
                    <td><a href="<?= url('/historicos/' . (int) ($row['id'] ?? 0)) ?>">Abrir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
