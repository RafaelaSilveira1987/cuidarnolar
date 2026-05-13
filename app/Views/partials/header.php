<header class="topbar">
    <strong>Cuidar no Lar</strong>
    <?php if (!empty($_user)): ?>
        <form method="POST" action="<?= url('/logout') ?>">
            <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
            <button type="submit" class="btn btn-secondary">Sair</button>
        </form>
    <?php endif; ?>
</header>
