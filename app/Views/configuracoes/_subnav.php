<?php
$activeTab = $activeTab ?? 'empresa';
$tabs = [
    'empresa' => ['/configuracoes/empresa', 'Dados da empresa'],
    'plantoes' => ['/configuracoes/plantoes', 'Tabela de plantões'],
    'permissoes' => ['/configuracoes/permissoes', 'Permissões de usuários'],
];
?>
<nav class="cfg-subnav" aria-label="Configurações">
    <?php foreach ($tabs as $key => [$href, $label]): ?>
        <a class="cfg-subnav__link <?= $activeTab === $key ? 'is-active' : '' ?>" href="<?= url($href) ?>">
            <?= e($label) ?>
        </a>
    <?php endforeach; ?>
</nav>
