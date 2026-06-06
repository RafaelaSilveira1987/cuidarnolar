<?php
$errors = $errors ?? [];
$forcado = !empty($forcado);
$err = static fn(string $key): string => !empty($errors[$key]) ? '<small class="field-error">' . e($errors[$key]) . '</small>' : '';
?>

<section class="auth-password-page">
    <div class="panel auth-password-card">
        <h1>Alterar senha</h1>
        <?php if ($forcado): ?>
            <p class="page-subtitle">Sua senha precisa ser alterada antes de continuar usando o sistema.</p>
        <?php else: ?>
            <p class="page-subtitle">Atualize sua senha de acesso com segurança.</p>
        <?php endif; ?>

        <?php if (!empty($errors['geral'])): ?>
            <div class="alert alert-error"><?= e($errors['geral']) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= url('/minha-senha') ?>" class="secure-password-form">
            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

            <label>
                Senha atual
                <input type="password" name="senha_atual" required autocomplete="current-password">
                <?= $err('senha_atual') ?>
            </label>

            <label>
                Nova senha
                <input type="password" name="nova_senha" required autocomplete="new-password">
                <?= $err('nova_senha') ?>
            </label>

            <label>
                Confirmar nova senha
                <input type="password" name="senha_confirmacao" required autocomplete="new-password">
                <?= $err('senha_confirmacao') ?>
            </label>

            <div class="password-rules">
                A senha deve ter pelo menos 8 caracteres, com letra maiúscula, minúscula e número.
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Salvar nova senha</button>
                <?php if (!$forcado): ?>
                    <a class="btn btn-secondary" href="<?= url('/dashboard') ?>">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</section>
