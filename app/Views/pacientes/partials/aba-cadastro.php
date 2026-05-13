<?php if (in_array($routeBase, ['/pacientes', '/responsaveis', '/cuidadores'], true) && (($record['status'] ?? '') !== 'Inativo')): ?>
<section class="panel danger-panel">
    <h2>Inativar registro</h2>
    <p class="page-subtitle">Mantem o historico e remove o registro dos fluxos ativos.</p>
    <form class="inline-form" method="POST" action="<?= url($routeBase . '/' . (int) ($record['id'] ?? 0) . '/inativar') ?>"
        onsubmit="return confirm('Confirma inativar este paciente?')">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
        <input type="text" name="motivo_inativacao" placeholder="Motivo da inativacao">
        <button class="btn btn-danger" type="submit">Inativar</button>
    </form>
</section>
<?php endif; ?>

<section class="panel">
    <h2>Dados cadastrais</h2>
    <dl class="detail-list">
        <?php foreach ($fields as $field => $label): ?>
        <div>
            <dt><?= e($label) ?></dt>
            <dd><?= e($record[$field] ?? '-') ?></dd>
        </div>
        <?php endforeach; ?>
    </dl>
</section>
