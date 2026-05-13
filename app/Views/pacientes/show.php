<?php
$pid = (int) ($record['id'] ?? 0);
$tabUrl = static fn (string $a): string => url('/pacientes/' . $pid . '?aba=' . urlencode($a));
$abas = [
    'cadastro' => 'Dados cadastrais',
    'anamnese' => 'Anamnese',
    'historico' => 'Histórico clínico',
    'plano' => 'Plano de cuidados',
    'plantao' => 'Relatórios de plantão',
];
?>
<section class="page-header">
    <div>
        <h1><?= e($title) ?> #<?= e((string) $pid) ?> — <?= e($record['nome_completo'] ?? '') ?></h1>
        <p class="page-subtitle">Ficha completa: cadastro, anamnese, linha do tempo e plantão.</p>
    </div>
    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url($routeBase . '/' . $pid . '/editar') ?>">Editar cadastro</a>
    </div>
</section>

<nav class="pac-tabs" aria-label="Seções da ficha do paciente">
    <?php foreach ($abas as $key => $label): ?>
    <a class="pac-tabs__link<?= ($aba ?? '') === $key ? ' pac-tabs__link--active' : '' ?>"
        href="<?= e($tabUrl($key)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>

<?php if (($aba ?? 'cadastro') === 'cadastro'): ?>
    <?php include BASE_PATH . '/app/Views/pacientes/partials/aba-cadastro.php'; ?>
<?php elseif ($aba === 'anamnese'): ?>
    <?php include BASE_PATH . '/app/Views/pacientes/partials/aba-anamnese.php'; ?>
<?php elseif ($aba === 'historico'): ?>
    <?php include BASE_PATH . '/app/Views/pacientes/partials/aba-historico.php'; ?>
<?php elseif ($aba === 'plano'): ?>
    <?php include BASE_PATH . '/app/Views/pacientes/partials/aba-plano.php'; ?>
<?php else: ?>
    <?php include BASE_PATH . '/app/Views/pacientes/partials/aba-plantao.php'; ?>
<?php endif; ?>
