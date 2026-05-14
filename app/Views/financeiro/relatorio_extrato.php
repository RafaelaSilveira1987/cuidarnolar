<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Entradas e saídas ligadas ao paciente no período (centro de custo).</p>
    </div>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <form class="search-form" method="GET" action="<?= url('/financeiro/relatorios/extrato') ?>">
        <label>
            Paciente
            <select name="paciente_id" required>
                <option value="">Selecione</option>
                <?php foreach (($pacientes ?? []) as $opt): ?>
                <option value="<?= (int) ($opt['id'] ?? 0) ?>"
                    <?= (int) ($paciente_id ?? 0) === (int) ($opt['id'] ?? 0) ? 'selected' : '' ?>>
                    <?= e($opt['nome_completo'] ?? '') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            De
            <input type="date" name="di" value="<?= e($di ?? '') ?>" required>
        </label>
        <label>
            Até
            <input type="date" name="df" value="<?= e($df ?? '') ?>" required>
        </label>
        <button class="btn btn-primary" type="submit">Gerar extrato</button>
    </form>
</section>

<?php if (($paciente_id ?? 0) > 0 && !empty($linhas)): ?>
<section class="panel">
    <h2><?= e($nomePaciente ?? 'Paciente') ?></h2>
    <p class="page-subtitle">Período: <?= e(formatDate($di ?? '')) ?> a <?= e(formatDate($df ?? '')) ?></p>

    <h3>Entradas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($linhas as $r): ?>
            <?php if (($r['tipo_transacao'] ?? '') === 'Entrada'): ?>
            <tr>
                <td><?= e(formatDate(substr((string) ($r['data'] ?? ''), 0, 10))) ?></td>
                <td><?= e($r['observacoes'] ?? '') ?></td>
                <td><?= formatMoney((float) ($r['valor'] ?? 0)) ?></td>
                <td><?= e($r['status'] ?? '') ?></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Saídas</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Valor</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($linhas as $r): ?>
            <?php if (($r['tipo_transacao'] ?? '') !== 'Entrada'): ?>
            <tr>
                <td><?= e(formatDate(substr((string) ($r['data'] ?? ''), 0, 10))) ?></td>
                <td><?= e($r['observacoes'] ?? '') ?></td>
                <td><?= formatMoney((float) ($r['valor'] ?? 0)) ?></td>
                <td><?= e($r['status'] ?? '') ?></td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <dl class="summary-list" style="margin-top:16px;">
        <div>
            <dt>Total entradas</dt>
            <dd><?= formatMoney($totEntradas ?? 0) ?></dd>
        </div>
        <div>
            <dt>Total saídas</dt>
            <dd><?= formatMoney($totSaidas ?? 0) ?></dd>
        </div>
        <div>
            <dt>Resultado no período</dt>
            <dd><strong><?= formatMoney($resultado ?? 0) ?></strong></dd>
        </div>
    </dl>
</section>
<?php elseif (($paciente_id ?? 0) > 0): ?>
<p class="empty-state">Nenhum lançamento encontrado para este paciente no período.</p>
<?php endif; ?>