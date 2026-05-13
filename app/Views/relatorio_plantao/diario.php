<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle"><?= e($paciente['nome_completo'] ?? '') ?></p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/relatorio-plantao') ?>">Voltar à lista</a>
</section>

<?php
$baseDiario = '/relatorio-plantao/paciente/' . (int) $paciente_id;
$pBase = static fn (string $data, string $turno): string => url($baseDiario . '?data=' . urlencode($data) . '&turno=' . urlencode($turno));
?>

<section class="panel rp-diario">
    <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/cabecalho_paciente.php'; ?>

    <?php if (!$temDadosNoDia): ?>
    <p class="rp-alert rp-alert--info">Não há relatórios mockados para esta data. Use <strong>13/05/2026</strong> para ver os três
        turnos de exemplo.</p>
    <?php endif; ?>

    <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/turno_selector.php'; ?>

    <?php if ($turnoData === null): ?>
    <p class="rp-muted">Nenhum relatório cadastrado para este turno nesta data.</p>
    <?php else: ?>
    <div class="rp-detalhe-turno">
        <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/sinais_vitais.php'; ?>
        <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/tabela_medicacoes.php'; ?>
        <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/evolucao_enfermagem.php'; ?>
        <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/intercorrencias.php'; ?>
        <?php include BASE_PATH . '/app/Views/relatorio_plantao/partials/rodape_plantonista.php'; ?>
    </div>
    <?php endif; ?>
</section>
