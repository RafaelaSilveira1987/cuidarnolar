<aside class="sidebar">
    <nav>
        <?php
        $staticLinks = [
            ['label' => 'Dashboard', 'route' => '/dashboard', 'permission' => 'dashboard.ver'],
            ['label' => 'Pacientes', 'route' => '/pacientes', 'permission' => 'pacientes.ver'],
            ['label' => 'Cuidadores', 'route' => '/cuidadores', 'permission' => 'cuidadores.ver'],
            ['label' => 'Agenda', 'route' => '/agendamentos', 'permission' => 'agenda.ver'],
            ['label' => 'Escalas', 'route' => '/escala', 'permission' => 'escala.ver'],
            ['label' => 'Financeiro', 'route' => '/financeiro', 'permission' => 'financeiro.ver'],
            ['label' => 'Relatório de Plantão', 'route' => '/relatorio-plantao', 'permission' => 'relatorios.ver'],
            ['label' => 'Configurações', 'route' => '/configuracoes', 'permission' => 'configuracoes.ver'],
        ];
        ?>

        <?php foreach ($staticLinks as $lnk): ?>
            <?php if (!function_exists('can') || can($lnk['permission'])): ?>
                <a href="<?= url($lnk['route']) ?>">
                    <?php if (!empty($lnk['icon'])): ?><?= htmlspecialchars($lnk['icon']) ?><?php endif; ?><?= htmlspecialchars($lnk['label']) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>
