<?php
$record = isset($record) && is_array($record) ? $record : [];
$fields = isset($fields) && is_array($fields) ? $fields : [];
$routeBase = (string)($routeBase ?? '');
$resourceKey = $resourceKey ?? ($record['uuid'] ?? $record['id'] ?? '');
$status = (string)($record['status'] ?? '');
$nome = (string)($record['nome_completo'] ?? $record['nome'] ?? $title ?? 'Registro');
$subtitulo = trim(implode(' • ', array_filter([
    $record['especialidade'] ?? null,
    $record['contrato_horas'] ?? null,
    $status ? 'Status: ' . $status : null,
])));
$canInativar = in_array($routeBase, ['/responsaveis', '/cuidadores'], true) && $status !== 'Inativo';
?>

<section class="page-header">
    <div>
        <h1><?= e($title ?? 'Registro') ?></h1>
        <p class="page-subtitle">Visualização detalhada do cadastro.</p>
    </div>

    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>

        <?php if ($resourceKey !== ''): ?>
            <a class="btn btn-primary" href="<?= url($routeBase . '/' . rawurlencode((string)$resourceKey) . '/editar') ?>">
                Editar
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <div class="record-summary">
        <div>
            <span class="page-subtitle">Cadastro</span>
            <h2><?= e($nome) ?></h2>
            <?php if ($subtitulo !== ''): ?>
                <p class="page-subtitle"><?= e($subtitulo) ?></p>
            <?php endif; ?>
        </div>

        <?php if (!empty($record['uuid'])): ?>
            <strong class="record-public-key">UUID <?= e((string)$record['uuid']) ?></strong>
        <?php endif; ?>
    </div>
</section>

<section class="panel">
    <dl class="detail-list">
        <?php foreach ($fields as $field => $label): ?>
            <?php if ($field === 'id') { continue; } ?>
            <div>
                <dt><?= e($label) ?></dt>
                <dd><?= e($record[$field] ?? '-') ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>

<?php if ($canInativar && $resourceKey !== ''): ?>
    <section class="panel danger-panel">
        <h2>Inativar registro</h2>
        <p class="page-subtitle">Mantém o histórico e remove o registro dos fluxos ativos.</p>

        <form class="inline-form"
              method="POST"
              action="<?= url($routeBase . '/' . rawurlencode((string)$resourceKey) . '/inativar') ?>"
              onsubmit="return confirm('Confirma inativar este registro?')">
            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
            <input type="text" name="motivo_inativacao" placeholder="Motivo da inativação">
            <button class="btn btn-danger" type="submit">Inativar</button>
        </form>
    </section>
<?php endif; ?>
