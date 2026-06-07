<?php
$backups = isset($backups) && is_array($backups) ? $backups : [];
$status = isset($status) && is_array($status) ? $status : [];
$logs = isset($logs) && is_array($logs) ? $logs : ($status['logs'] ?? []);
$activeTab = 'backups';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/backups_v31.css">

<?php
$subnav = BASE_PATH . '/app/Views/configuracoes/_subnav.php';
if (is_file($subnav)) {
    include $subnav;
}
?>

<section class="page-header backup-header">
    <div>
        <h1>Backups e manutenção</h1>
        <p class="page-subtitle">Rotina de segurança para cópia do banco, logs e recuperação do sistema.</p>
    </div>

    <form method="POST" action="<?= url('/configuracoes/backups/gerar') ?>" onsubmit="return confirm('Gerar backup do banco agora?');">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
        <button type="submit" class="btn btn-primary">Gerar backup agora</button>
    </form>
</section>

<section class="backup-metrics">
    <article>
        <span>Total de backups</span>
        <strong><?= (int)($status['total_backups'] ?? count($backups)) ?></strong>
    </article>
    <article>
        <span>Último backup</span>
        <strong><?= e($status['ultimo_backup']['data'] ?? 'Nenhum') ?></strong>
    </article>
    <article>
        <span>Arquivos de log</span>
        <strong><?= (int)($logs['arquivos'] ?? 0) ?></strong>
    </article>
    <article>
        <span>Tamanho dos logs</span>
        <strong><?= e($logs['tamanho'] ?? '0 B') ?></strong>
    </article>
</section>

<section class="panel backup-panel">
    <div class="panel-header">
        <h2>Backups disponíveis</h2>
        <p class="page-subtitle">Arquivos salvos em <code>storage/backups</code>. Essa pasta deve ficar protegida do acesso público.</p>
    </div>

    <?php if ($backups === []): ?>
        <p class="empty-state">Nenhum backup gerado ainda.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table backup-table">
                <thead>
                    <tr>
                        <th>Arquivo</th>
                        <th>Data</th>
                        <th>Tamanho</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $backup): ?>
                        <tr>
                            <td><code><?= e($backup['filename'] ?? '') ?></code></td>
                            <td><?= e($backup['data'] ?? '-') ?></td>
                            <td><?= e($backup['tamanho'] ?? '-') ?></td>
                            <td class="actions backup-actions">
                                <a class="btn btn-secondary btn-sm" href="<?= url('/configuracoes/backups/download/' . rawurlencode((string)($backup['filename'] ?? ''))) ?>">Baixar</a>

                                <form method="POST" action="<?= url('/configuracoes/backups/excluir/' . rawurlencode((string)($backup['filename'] ?? ''))) ?>" onsubmit="return confirm('Excluir este backup?');">
                                    <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="backup-grid">
    <article class="panel backup-panel">
        <h2>Status dos logs</h2>
        <dl class="backup-dl">
            <div>
                <dt>Pasta</dt>
                <dd><code><?= e($logs['path'] ?? 'storage/logs') ?></code></dd>
            </div>
            <div>
                <dt>Existe?</dt>
                <dd><?= !empty($logs['existe']) ? 'Sim' : 'Não' ?></dd>
            </div>
            <div>
                <dt>Arquivos</dt>
                <dd><?= (int)($logs['arquivos'] ?? 0) ?></dd>
            </div>
            <div>
                <dt>Tamanho</dt>
                <dd><?= e($logs['tamanho'] ?? '0 B') ?></dd>
            </div>
        </dl>
    </article>

    <article class="panel backup-panel">
        <h2>Rotina recomendada</h2>
        <ul class="backup-list">
            <li>Gerar backup manual antes de atualizações grandes.</li>
            <li>Agendar backup automático diário ou, no mínimo, semanal.</li>
            <li>Guardar uma cópia fora do servidor.</li>
            <li>Testar restauração periodicamente. Backup não testado é promessa, não garantia.</li>
        </ul>
    </article>
</section>
