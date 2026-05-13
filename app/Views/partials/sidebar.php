<aside class="sidebar">
    <nav>
        <?php
        $staticLinks = [
            ['label' => 'Dashboard', 'route' => '/dashboard'],
            ['label' => 'Pacientes', 'route' => '/pacientes'],
            ['label' => 'Cuidadores', 'route' => '/cuidadores'],
            ['label' => 'Responsaveis', 'route' => '/responsaveis'],
            ['label' => 'Agendamentos', 'route' => '/agendamentos'],
            ['label' => 'Financeiro', 'route' => '/financeiro'],
            ['label' => 'Relatório de Plantão', 'route' => '/relatorio-plantao'],
        ];
        ?>

        <?php foreach ($staticLinks as $lnk): ?>
        <a href="<?= url($lnk['route']) ?>">
            <?php if (!empty($lnk['icon'])): ?><?= htmlspecialchars($lnk['icon']) ?>
            <?php endif; ?><?= htmlspecialchars($lnk['label']) ?>
        </a>
        <?php endforeach; ?>
    </nav>
</aside>