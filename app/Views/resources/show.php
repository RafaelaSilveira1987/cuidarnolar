<section class="page-header">
    <div>
        <h1><?= e($title) ?> #<?= e($record['id'] ?? '') ?></h1>
        <p class="page-subtitle">Dados migrados do legado para a nova estrutura MVC.</p>
    </div>
    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url($routeBase . '/' . $record['id'] . '/editar') ?>">Editar</a>
    </div>
</section>

<?php if ($routeBase === '/pacientes' && (($record['status'] ?? '') !== 'Inativo')): ?>
    <section class="panel danger-panel">
        <h2>Inativar paciente</h2>
        <p class="page-subtitle">Mantem o historico e remove o paciente dos fluxos ativos.</p>
        <form class="inline-form" method="POST" action="<?= url('/pacientes/' . $record['id'] . '/inativar') ?>" onsubmit="return confirm('Confirma inativar este paciente?')">
            <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
            <input type="text" name="motivo_inativacao" placeholder="Motivo da inativacao">
            <button class="btn btn-danger" type="submit">Inativar</button>
        </form>
    </section>
<?php endif; ?>

<section class="panel">
    <dl class="detail-list">
        <?php foreach ($fields as $field => $label): ?>
            <div>
                <dt><?= e($label) ?></dt>
                <dd><?= e($record[$field] ?? '-') ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</section>
