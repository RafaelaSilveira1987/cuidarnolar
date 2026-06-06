<section class="error-page">
    <h1>403</h1>
    <p><?= e($message ?: 'Acesso negado.') ?></p>
    <a href="<?= url('/dashboard') ?>">Voltar ao painel</a>
</section>
