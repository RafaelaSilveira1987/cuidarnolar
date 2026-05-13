<section class="page-header">
    <h1>Dashboard</h1>
</section>

<section class="metric-grid">
    <article class="metric-card">
        <span>Pacientes</span>
        <strong><?= (int) ($resumo['pacientes'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span>Responsaveis</span>
        <strong><?= (int) ($resumo['responsaveis'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span>Cuidadores</span>
        <strong><?= (int) ($resumo['cuidadores'] ?? 0) ?></strong>
    </article>
    <article class="metric-card">
        <span>Financeiro pendente</span>
        <strong><?= (int) ($resumo['financeiro_pendente'] ?? 0) ?></strong>
    </article>
</section>

<section class="content-grid">
    <article class="panel">
        <h2>Notificacoes administrativas</h2>
        <div class="notification-list">
            <?php foreach ($notificacoes as $item): ?>
            <div class="notification-item">
                <strong><?= (int) $item['valor'] ?></strong>
                <div>
                    <span><?= e($item['titulo']) ?></span>
                    <small><?= e($item['descricao']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel">
        <h2>Resumo financeiro</h2>
        <dl class="summary-list">
            <div>
                <dt>Entradas</dt>
                <dd><?= formatMoney((float) ($financeiro['entradas'] ?? 0)) ?></dd>
            </div>
            <div>
                <dt>Saidas</dt>
                <dd><?= formatMoney((float) ($financeiro['saidas'] ?? 0)) ?></dd>
            </div>
            <div>
                <dt>Pendencias</dt>
                <dd><?= (int) ($financeiro['pendentes'] ?? 0) ?></dd>
            </div>
        </dl>
    </article>
</section>

<section class="panel">
    <div class="section-header">
        <h2>Proximos agendamentos</h2>
        <a href="<?= url('/agendamentos') ?>">Ver agenda</a>
    </div>

    <?php if (empty($proximosEventos)): ?>
    <p class="empty-state">Nenhum agendamento futuro encontrado.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Titulo</th>
                <th>Paciente</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proximosEventos as $evento): ?>
            <tr>
                <td><?= e(formatDate($evento['data_evento'] ?? '')) ?></td>
                <td><?= e($evento['titulo'] ?? '') ?></td>
                <td><?= e($evento['paciente_nome'] ?? '-') ?></td>
                <td><?= e($evento['status'] ?? 'Pendente') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<section class="panel">
    <h2 class="section-header" style="margin-top:0;">Acesso rápido</h2>
    <p class="page-subtitle" style="margin-top:-8px;">Atalhos operacionais (o layout principal já inclui o CSS em todas as páginas).</p>
    <div class="metric-grid">
        <?php $menuRel = include BASE_PATH . '/app/Config/MenuRelatorios.php'; ?>
        <?php foreach ($menuRel as $key => $m): ?>
        <div class="metric-card">
            <div style="font-size:20px;"><?= htmlspecialchars($m['icon'] ?? '') ?></div>
            <strong><?= htmlspecialchars($m['label'] ?? $key) ?></strong>
            <p style="color:var(--muted);font-size:14px;"><?= htmlspecialchars($m['description'] ?? '') ?></p>
            <a href="<?= url($m['route'] ?? '#') ?>" class="btn btn-link">Abrir</a>
        </div>
        <?php endforeach; ?>
    </div>
</section>
