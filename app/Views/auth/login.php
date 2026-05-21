<?php $pageTitle = 'Login'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">

<div class="auth-shell">

    <!-- Cabeçalho -->
    <div class="auth-header">
        <h1>Acesse sua Gestão de Cuidados Sociais</h1>
        <!-- <p>Acesse sua Gestão de Cuidados Sociais.</p> -->
    </div>

    <!-- Card -->
    <section class="auth-card">

        <!-- Lado esquerdo -->
        <div class="image-container">

            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo Cuidar no Lar">

            <div class="image-content">
                <h2>Cuidado com excelência.</h2>

                <p>
                    Gestão inteligente para equipes,
                    cuidadores e pacientes.
                </p>
            </div>

        </div>

        <!-- Lado direito -->
        <div class="form-container">

            <div class="form-header">

            </div>

            <?php include BASE_PATH . '/app/Views/partials/alerts.php'; ?>

            <form method="POST" action="<?= url('/login') ?>" class="login-form">
                <h2>Entrar</h2>

                <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
                <input type="hidden" name="redirect" value="<?= e($redirect ?? '/dashboard') ?>">

                <label>
                    Usuário
                    <input type="text" name="username" autocomplete="username" required autofocus>
                </label>

                <label>
                    Senha
                    <input type="password" name="senha" autocomplete="current-password" required>
                </label>

                <div class="form-links">
                    <a href="<?= url('/esqueci-senha') ?>">
                        Esqueci minha senha
                    </a>
                </div>

                <button type="submit" class="btn btn-primary">
                    Entrar
                </button>

                <div class="register-link">
                    Não possui conta?
                    <a href="<?= url('/cadastro') ?>">
                        Cadastre-se aqui
                    </a>
                </div>

            </form>
        </div>

    </section>

</div>