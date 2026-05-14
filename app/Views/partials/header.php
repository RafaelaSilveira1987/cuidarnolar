<header class="topbar">
    <div class="topbar-left">
        <strong>Cuidar no Lar</strong>
    </div>

    <div class="topbar-center">
        <span>Gestão de Cuidadores Sociais</span>
    </div>

    <div class="topbar-right">
        <?php if (!empty($_user)): ?>
        <form method="POST" action="<?= url('/logout') ?>">
            <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">
            <button type="submit" class="btn btn-secondary">Sair</button>
        </form>
        <?php endif; ?>
    </div>
</header>