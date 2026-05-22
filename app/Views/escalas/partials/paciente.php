<?php
/**
 * Views/escalas/paciente.php
 * Visão detalhada da escala de um único paciente — mês inteiro.
 *
 * $pac     — dados do paciente (mesmo formato de card_paciente.php)
 * $semanas — array de semanas do mês [{label, value, dias, turnos}]
 */
$pageTitle = 'Escala — ' . ($pac['nome'] ?? '');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/escalas.css">

<div class="escala-shell">

    <!-- Breadcrumb -->
    <nav style="font-size:13px;color:#9ca3af;margin-bottom:.75rem">
        <a href="<?= BASE_URL ?>/escalas" style="color:#1d4ed8;text-decoration:none">Central de Cobertura</a>
        <span style="margin:0 6px">/</span>
        <?= htmlspecialchars($pac['nome']) ?>
    </nav>

    <!-- Cabeçalho do colaborador -->
    <div class="colaborador-header" style="background:#fff">
        <div class="paciente-avatar"
            style="width:52px;height:52px;font-size:16px;font-weight:700;background:<?= $pac['cor_avatar'] ?>;color:<?= $pac['cor_avatar_t'] ?>">
            <?= $pac['iniciais'] ?>
        </div>
        <div>
            <div class="colaborador-name"><?= htmlspecialchars($pac['nome']) ?></div>
            <div class="colaborador-sub"><?= htmlspecialchars($pac['endereco'] ?? '') ?></div>
        </div>
        <div class="colaborador-stats">
            <div class="stat-val"><?= $pac['cobertura_pct'] ?>%</div>
            <div class="stat-lbl">Cobertura mensal</div>
        </div>
        <a href="<?= BASE_URL ?>/escalas?paciente_id=<?= $pac['id'] ?>" class="btn-escala-new" style="margin-left:auto">
            <i class="ti ti-layout-grid" aria-hidden="true"></i>
            Ver grade semanal
        </a>
    </div>

    <!-- Semanas do mês -->
    <?php foreach ($semanas as $semana):
        $cobertura = [$pac]; // Re-usa o mesmo partial, com $pac tendo $pac['turnos'] da semana
        $dias = $semana['dias'];
    ?>
    <div style="margin-bottom:1.5rem">
        <div
            style="font-size:12px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem">
            <?= htmlspecialchars($semana['label']) ?>
        </div>
        <div class="paciente-card">
            <div class="grade-wrap">
                <?php include __DIR__ . '/partials/bloco_plantao.php'; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<?php include __DIR__ . '/modal_criar.php'; ?>
<?php include __DIR__ . '/modal_substituicao.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/escalas.js"></script>