<section class="error-page">
    <h1>404</h1>
    <p><?= e($message ?: 'Pagina nao encontrada.') ?></p>
    <a href="<?= url('/dashboard') ?>">Voltar ao painel</a>
</section>
