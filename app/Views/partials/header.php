<header class="topbar">
    <div class="topbar-left">
        <img src="<?= BASE_URL ?>/assets/images/logo_interna.png" alt="Logo Cuidar no Lar">
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