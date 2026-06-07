<?php
$activeTab = (string)($activeTab ?? '');

$tabs = [
    [
        'key' => 'empresa',
        'label' => 'Dados da empresa',
        'url' => '/configuracoes/empresa',
    ],
    [
        'key' => 'plantoes',
        'label' => 'Tabela de plantões',
        'url' => '/configuracoes/plantoes',
    ],
    [
        'key' => 'permissoes',
        'label' => 'Permissões de usuários',
        'url' => '/configuracoes/permissoes',
    ],
    [
        'key' => 'usuarios',
        'label' => 'Usuários',
        'url' => '/configuracoes/usuarios',
    ],
    [
        'key' => 'backups',
        'label' => 'Backups',
        'url' => '/configuracoes/backups',
    ],
    [
        'key' => 'checklist-publicacao',
        'label' => 'Checklist de publicação',
        'url' => '/configuracoes/checklist-publicacao',
    ],
];
?>

<nav class="cfg-subnav" aria-label="Configurações">
    <?php foreach ($tabs as $tab): ?>
    <?php
        $key = (string)($tab['key'] ?? '');
        $label = (string)($tab['label'] ?? '');
        $path = (string)($tab['url'] ?? '');

        if ($key === '' || $label === '' || $path === '') {
            continue;
        }

        $isActive = $activeTab === $key;
        ?>

    <a class="cfg-subnav__link <?= $isActive ? 'is-active' : '' ?>" href="<?= url($path) ?>">
        <?= e($label) ?>
    </a>
    <?php endforeach; ?>
</nav>