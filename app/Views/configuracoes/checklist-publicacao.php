<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/checklist_publicacao_v30.css">

<?php include __DIR__ . '/_subnav.php'; ?>

<section class="page-header cfg-check-header">
    <div>
        <h1>Checklist de publicação</h1>
        <p class="page-subtitle">Revisão final antes de colocar o sistema na web.</p>
    </div>
    <span class="cfg-check-generated">Gerado em <?= e($checklist['generated_at'] ?? '') ?></span>
</section>

<?php
$routes = $checklist['routes'] ?? [];
$summary = $routes['summary'] ?? ['total' => 0, 'warnings' => 0, 'errors' => 0];
?>

<section class="cfg-check-metrics">
    <article>
        <span>Rotas auditadas</span>
        <strong><?= (int)($summary['total'] ?? 0) ?></strong>
    </article>
    <article class="<?= ((int)($summary['errors'] ?? 0) > 0) ? 'is-danger' : 'is-ok' ?>">
        <span>Erros críticos</span>
        <strong><?= (int)($summary['errors'] ?? 0) ?></strong>
    </article>
    <article class="<?= ((int)($summary['warnings'] ?? 0) > 0) ? 'is-warning' : 'is-ok' ?>">
        <span>Avisos</span>
        <strong><?= (int)($summary['warnings'] ?? 0) ?></strong>
    </article>
</section>

<section class="cfg-check-grid">
    <article class="panel cfg-check-panel">
        <h2>Ambiente</h2>
        <div class="cfg-check-list">
            <?php foreach (($checklist['environment'] ?? []) as $item): ?>
                <div class="cfg-check-row <?= !empty($item['ok']) ? 'is-ok' : 'is-danger' ?>">
                    <div>
                        <strong><?= e($item['label'] ?? '') ?></strong>
                        <small>Atual: <?= e($item['current'] ?? '') ?> | Esperado: <?= e($item['expected'] ?? '') ?></small>
                        <p><?= e($item['hint'] ?? '') ?></p>
                    </div>
                    <span><?= !empty($item['ok']) ? 'OK' : 'Ajustar' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="panel cfg-check-panel">
        <h2>Arquivos de proteção</h2>
        <div class="cfg-check-list">
            <?php foreach (($checklist['files'] ?? []) as $item): ?>
                <div class="cfg-check-row <?= !empty($item['ok']) ? 'is-ok' : (!empty($item['required']) ? 'is-danger' : 'is-warning') ?>">
                    <div>
                        <strong><?= e($item['path'] ?? '') ?></strong>
                        <p><?= e($item['hint'] ?? '') ?></p>
                    </div>
                    <span><?= !empty($item['ok']) ? 'OK' : 'Verificar' ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="panel cfg-check-panel">
    <h2>Achados nas rotas</h2>

    <?php if (empty($routes['issues'])): ?>
        <p class="empty-state">Nenhum erro ou aviso encontrado nas rotas auditadas.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table cfg-check-table">
                <thead>
                    <tr>
                        <th>Nível</th>
                        <th>Método</th>
                        <th>Rota</th>
                        <th>Controller</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($routes['issues'] as $issue): ?>
                        <tr>
                            <td><span class="cfg-level <?= e($issue['level'] ?? '') ?>"><?= e($issue['level'] ?? '') ?></span></td>
                            <td><?= e($issue['method'] ?? '') ?></td>
                            <td><code><?= e($issue['path'] ?? '') ?></code></td>
                            <td><?= e($issue['handler'] ?? '') ?></td>
                            <td><?= e($issue['message'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel cfg-check-panel">
    <h2>Checklist operacional</h2>
    <div class="cfg-check-columns">
        <?php foreach (($checklist['publication'] ?? []) as $group => $items): ?>
            <article class="cfg-check-box">
                <h3><?= e((string)$group) ?></h3>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li><?= e((string)$item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>
