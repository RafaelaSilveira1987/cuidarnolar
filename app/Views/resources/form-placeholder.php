<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle"><?= e($message) ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
</section>

<section class="panel">
    <p>Esta tela ja esta roteada e protegida por login. O proximo passo e migrar os campos, validacoes e POST do arquivo legado correspondente.</p>

    <?php if (!empty($record)): ?>
        <dl class="detail-list">
            <?php foreach ($record as $field => $value): ?>
                <div>
                    <dt><?= e($field) ?></dt>
                    <dd><?= e($value ?? '-') ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>
</section>
