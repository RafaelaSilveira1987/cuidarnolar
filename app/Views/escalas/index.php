<?php

/**
 * Views/escalas/index.php
 * Central de Cobertura — visão semanal por paciente
 */

$pageTitle = 'Central de Cobertura';

// Defaults defensivos — evita warnings se o controller ainda não passa tudo
$resumo       ??= ['cobertos' => 0, 'total_pac' => 0, 'vagos' => 0, 'substituicoes' => 0, 'ativos' => 0];
$cobertura    ??= [];
$dias         ??= [];
$pacientes    ??= [];
$colaboradores ??= [];
$semanas      ??= [];
$semana_ativa ??= date('Y-m-d');
$alertas      ??= [];
$filtros      ??= [];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/escala.css">

<div class="escala-shell">

    <!-- ══ Topbar ═══════════════════════════════════════════ -->
    <div class="escala-topbar">

        <div class="escala-topbar__title">
            <i class="ti ti-shield-check" aria-hidden="true"></i>
            Central de Cobertura
        </div>

        <div class="escala-topbar__filters">

            <select id="filtro-paciente" name="paciente_id">
                <option value="">Todos os pacientes</option>
                <?php foreach ($pacientes as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($filtros['paciente_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nome_completo']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select id="filtro-colaborador" name="colaborador_id">
                <option value="">Todos os cuidadores</option>
                <?php foreach ($colaboradores as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($filtros['colaborador_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nome_completo']) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select id="filtro-semana" name="semana">
                <?php if (empty($semanas)): ?>
                <option value="<?= date('Y-m-d') ?>">Semana atual</option>
                <?php else: ?>
                <?php foreach ($semanas as $s): ?>
                <option value="<?= $s['value'] ?>" <?= $semana_ativa === $s['value'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['label']) ?>
                </option>
                <?php endforeach; ?>
                <?php endif; ?>
            </select>

            <a href="<?= BASE_URL ?>/escalas/criar" class="btn-escala-new">
                <i class="ti ti-plus" aria-hidden="true"></i>
                Nova escala
            </a>

        </div>
    </div>

    <!-- ══ Resumo rápido ════════════════════════════════════ -->
    <div class="escala-summary" role="region" aria-label="Resumo da semana">

        <div class="summary-card summary-card--ok">
            <div class="summary-card__label">Pacientes cobertos</div>
            <div class="summary-card__value"><?= $resumo['cobertos'] ?> / <?= $resumo['total_pac'] ?></div>
        </div>

        <div class="summary-card <?= ($resumo['vagos'] ?? 0) > 0 ? 'summary-card--danger' : '' ?>">
            <div class="summary-card__label">Plantões vagos</div>
            <div class="summary-card__value"><?= $resumo['vagos'] ?></div>
        </div>

        <div class="summary-card <?= ($resumo['substituicoes'] ?? 0) > 0 ? 'summary-card--warn' : '' ?>">
            <div class="summary-card__label">Substituições</div>
            <div class="summary-card__value"><?= $resumo['substituicoes'] ?></div>
        </div>

        <div class="summary-card">
            <div class="summary-card__label">Cuidadores ativos</div>
            <div class="summary-card__value"><?= $resumo['ativos'] ?></div>
        </div>

    </div>

    <!-- ══ Grade + sidebar ══════════════════════════════════ -->
    <div class="escala-layout">

        <!-- Coluna principal: cards por paciente -->
        <div id="grade-pacientes">
            <?php foreach ($cobertura as $pac): ?>
            <?php include __DIR__ . '/partials/card_paciente.php'; ?>
            <?php endforeach; ?>

            <?php if (empty($cobertura)): ?>
            <div style="text-align:center;padding:3rem;color:#9ca3af;font-size:14px;">
                <i class="ti ti-calendar-off" style="font-size:36px;display:block;margin-bottom:.5rem"></i>
                Nenhuma escala encontrada para o período selecionado.
                <?php if (empty($pacientes)): ?>
                <br><small style="font-size:12px;margin-top:.5rem;display:block">
                    Nenhum paciente cadastrado — <a href="<?= BASE_URL ?>/pacientes/criar">cadastrar paciente</a>
                </small>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar: alertas + legenda -->
        <aside class="escala-sidebar" aria-label="Alertas e legenda">
            <?php include __DIR__ . '/partials/sidebar_alertas.php'; ?>
        </aside>

    </div>

</div>

<!-- ══ Modais ═══════════════════════════════════════════════ -->
<?php include __DIR__ . '/modal_criar.php'; ?>
<?php include __DIR__ . '/modal_substituicao.php'; ?>


<script src="<?= BASE_URL ?>/assets/js/escalas.js"></script>