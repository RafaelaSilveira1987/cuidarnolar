<?php
/**
 * app/Views/relatorio_plantao/index.php
 * Lista de pacientes com relatórios de plantão.
 */

$pacientes = isset($pacientes) && is_array($pacientes) ? $pacientes : [];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_erp.css">

<div class="rp-page">
    <header class="rp-header-card">
        <div class="rp-patient-info">
            <h1>Relatórios de Plantão</h1>
            <p class="rp-patient-meta">Selecione um paciente para consultar o histórico de relatórios.</p>
        </div>
    </header>

    <?php if (!empty($pacientes)): ?>
    <section class="rp-patient-list" aria-label="Pacientes com relatórios de plantão">
        <div class="rp-patient-list-head">
            <span>Paciente</span>
            <span>Prontuário</span>
            <!-- <span>Total</span> -->
            <span>Último relatório</span>
            <span>Ação</span>
        </div>

        <?php foreach ($pacientes as $paciente): ?>
        <?php
                    $nome = (string)($paciente['nome_completo'] ?? 'Paciente');
                    $uuid = (string)($paciente['uuid'] ?? '');
                    $href = BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($uuid);
                    $total = (int)($paciente['total_relatorios'] ?? 0);
                    $ultimo = !empty($paciente['ultimo_relatorio_data']) && strtotime((string)$paciente['ultimo_relatorio_data']) !== false
                        ? date('d/m/Y', strtotime((string)$paciente['ultimo_relatorio_data']))
                        : '—';
                ?>
        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="rp-patient-row">
            <strong><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></strong>
            <span><?= !empty($paciente['prontuario']) ? '#' . htmlspecialchars((string)$paciente['prontuario'], ENT_QUOTES, 'UTF-8') : '—' ?></span>
            <!-- <span><?= $total ?> relatório(s)</span> -->
            <span><?= htmlspecialchars($ultimo, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="rp-row-action">Abrir</span>
        </a>
        <?php endforeach; ?>
    </section>
    <?php else: ?>
    <div class="rp-empty">
        Nenhum paciente com relatório encontrado.
    </div>
    <?php endif; ?>
</div>