<?php

/**
 * app/Views/relatorio_plantao/index.php
 * Lista de pacientes com relatórios de plantão.
 * Variáveis: $pacientes (array), $_user (array)
 */
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_pages.css">

<div class="rp-wrapper">

    <div class="rp-page-header">
        <div>
            <h1>Relatórios de Plantão</h1>
            <p>Pacientes com evoluções clínicas registradas</p>
        </div>
        <div class="page-actions" style="margin:0">
            <a href="<?= BASE_URL ?>/relatorio-plantao/novo" class="btn-primary">
                <i class="ti ti-plus" aria-hidden="true"></i> Novo Relatório
            </a>
        </div>
    </div>

    <section class="rp-pacientes-grid" style="margin-top:20px">

        <?php if (!empty($pacientes)): ?>

        <?php foreach ($pacientes as $paciente):
                $status = strtolower($paciente['status'] ?? 'ativo');
            ?>
        <a href="<?= BASE_URL ?>/relatorio-plantao/paciente/<?= htmlspecialchars((string)($paciente['uuid'] ?? $paciente['id'])) ?>"
            class="rp-paciente-card">

            <div class="rp-avatar">
                <?= htmlspecialchars(
                            strtoupper(substr($paciente['nome_completo'] ?? '', 0, 1))
                                . strtoupper(substr(strstr($paciente['nome_completo'] ?? ' ', ' '), 1, 1))
                        ) ?>
            </div>

            <div class="rp-paciente-content">
                <h3><?= htmlspecialchars($paciente['nome_completo'] ?? 'Paciente') ?></h3>
                <div class="rp-meta">
                    <?php if (!empty($paciente['prontuario'])): ?>
                    <span>Prontuário #<?= htmlspecialchars($paciente['prontuario']) ?></span>
                    <?php endif ?>
                    <?php if (!empty($paciente['idade'])): ?>
                    <span><?= (int)$paciente['idade'] ?> anos</span>
                    <?php endif ?>
                    <?php if (!empty($paciente['diagnostico'])): ?>
                    <span><?= htmlspecialchars($paciente['diagnostico']) ?></span>
                    <?php endif ?>
                </div>
            </div>

            <div class="rp-status" data-status="<?= htmlspecialchars($status) ?>">
                <?= htmlspecialchars($paciente['status'] ?? 'Ativo') ?>
            </div>

        </a>
        <?php endforeach ?>

        <?php else: ?>

        <div class="rp-empty-state">
            <i class="ti ti-clipboard-list" aria-hidden="true"></i>
            Nenhum paciente com relatório encontrado.
        </div>

        <?php endif ?>

    </section>

</div>