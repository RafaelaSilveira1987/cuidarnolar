<?php $pageTitle = 'Login'; ?>

<section class="auth-card">
    <h1>Cuidar no Lar</h1>
    <p>Acesse o painel administrativo.</p>

    <?php include BASE_PATH . '/app/Views/partials/alerts.php'; ?>

    <form method="POST" action="<?= url('/login') ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
        <input type="hidden" name="redirect" value="<?= e($redirect ?? '/dashboard') ?>">

        <label>
            Usuario
            <input type="text" name="username" autocomplete="username" required autofocus>
        </label>

        <label>
            Senha
            <input type="password" name="senha" autocomplete="current-password" required>
        </label>

        <button type="submit" class="btn btn-primary">Entrar</button>
    </form>
</section>
