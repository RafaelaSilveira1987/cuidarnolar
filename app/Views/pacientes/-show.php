<?php
$pid = (int)($paciente['id'] ?? 0);
$tabUrl = static fn(string $a): string => url('/pacientes/' . $pid . '?aba=' . urlencode($a));

$abas = [
    'cadastro' => 'Dados cadastrais',
    'anamnese' => 'Anamnese',
    'medicacoes' => 'Medicações',
    'historico' => 'Histórico clínico',
    'plano' => 'Plano de cuidados',
    'plantao' => 'Relatórios de plantão',
];

$valor = static function ($v, string $fallback = '—') {
    $v = trim((string)$v);
    return $v !== '' ? $v : $fallback;
};

$badge = static function ($v): string {
    return trim((string)$v) === 'Sim'
        ? '<span class="badge bg-success">Sim</span>'
        : '<span class="badge bg-secondary">Não</span>';
};
?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?> — <?= e($paciente['nome_completo'] ?? '') ?></h1>
        <p class="page-subtitle">
            <strong>Prontuário:</strong>
            <?= e(trim((string)($paciente['prontuario'] ?? '')) !== '' ? (string)$paciente['prontuario'] : '—') ?>
        </p>
        <p class="page-subtitle">
            Ficha completa: cadastro, anamnese, medicações contínuas, plano e plantão.
        </p>
    </div>

    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url($routeBase . '/' . $pid . '/editar') ?>">Editar cadastro</a>
    </div>
</section>

<nav class="pac-tabs" aria-label="Seções da ficha do paciente">
    <?php foreach ($abas as $key => $label): ?>
    <a class="pac-tabs__link<?= ($aba ?? '') === $key ? ' pac-tabs__link--active' : '' ?>"
        href="<?= e($tabUrl($key)) ?>">
        <?= e($label) ?>
    </a>
    <?php endforeach; ?>
</nav>

<section class="panel">
    <div class="panel-header">
        <h2>Resumo clínico</h2>
        <p class="page-subtitle">Visão consolidada das principais informações assistenciais.</p>
    </div>

    <div class="form-grid">
        <div class="info-card span-2">
            <strong>Diagnóstico</strong><br>
            <?= e($valor($paciente['diagnostico'] ?? $paciente['diagnostico_principal'] ?? '')) ?>
        </div>

        <div class="info-card">
            <strong>CID Principal</strong><br>
            <?= e($valor($paciente['cid_principal'] ?? '')) ?>
        </div>

        <div class="info-card">
            <strong>Alergias</strong><br>
            <?= e($valor($paciente['alergias'] ?? '')) ?>
        </div>

        <div class="info-card">
            <strong>Mobilidade</strong><br>
            <?= e($valor($paciente['mobilidade'] ?? '')) ?>
        </div>

        <div class="info-card">
            <strong>Estado cognitivo base</strong><br>
            <?= e($valor($paciente['estado_cognitivo_base'] ?? '')) ?>
        </div>

        <div class="info-card">
            <strong>Oxigênio</strong><br>
            <?= $badge($paciente['usa_oxigenio'] ?? 'Não') ?>
        </div>

        <div class="info-card">
            <strong>Traqueostomia</strong><br>
            <?= $badge($paciente['traqueostomia'] ?? 'Não') ?>
        </div>

        <div class="info-card">
            <strong>GTT</strong><br>
            <?= $badge($paciente['gtt'] ?? $paciente['gastrostomia'] ?? 'Não') ?>
        </div>

        <div class="info-card">
            <strong>SNE</strong><br>
            <?= $badge($paciente['sne'] ?? 'Não') ?>
        </div>

        <div class="info-card span-2">
            <strong>Medicações contínuas</strong><br>
            <?php if (empty($medicacoes)): ?>
            <?= e($valor('')) ?>
            <?php else: ?>
            <ol class="pac-med-mini-list">
                <?php foreach ($medicacoes as $med): ?>
                <li>
                    <strong><?= e($med['nome_medicamento'] ?? '') ?></strong>
                    <?php if (!empty($med['dosagem'])): ?>
                    - <?= e($med['dosagem']) ?>
                    <?php endif; ?>
                    <?php if (!empty($med['horarios'])): ?>
                    <span>Horários: <?= e($med['horarios']) ?></span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
            <?php endif; ?>
        </div>

        <div class="info-card span-2">
            <strong>Observações clínicas</strong><br>
            <?= nl2br(e($valor($paciente['observacoes_clinicas'] ?? ''))) ?>
        </div>
    </div>
</section>

<?php if (($aba ?? 'cadastro') === 'cadastro'): ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-cadastro.php'; ?>
<?php elseif ($aba === 'anamnese'): ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-anamnese.php'; ?>
<?php elseif ($aba === 'medicacoes'): ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-medicacoes.php'; ?>
<?php elseif ($aba === 'historico'): ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-historico.php'; ?>
<?php elseif ($aba === 'plano'): ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-plano.php'; ?>
<?php else: ?>
<?php include BASE_PATH . '/app/Views/pacientes/partials/aba-plantao.php'; ?>
<?php endif; ?>