<?php
/**
 * Views/escalas/partials/sidebar_alertas.php
 *
 * $alertas = [
 *   ['tipo' => 'danger', 'texto' => '...', 'subtexto' => '...'],
 *   ...
 * ]
 */
?>

<!-- Painel de alertas -->
<div class="alertas-panel">
    <div class="alertas-panel__title">
        <i class="ti ti-alert-triangle" aria-hidden="true"></i>
        Alertas da semana
    </div>

    <?php if (empty($alertas)): ?>
    <p style="font-size:12px;color:#9ca3af;text-align:center;padding:.5rem 0">
        <i class="ti ti-circle-check" style="font-size:20px;display:block;margin-bottom:4px;color:#059669"></i>
        Nenhum alerta — semana 100% coberta!
    </p>
    <?php else: ?>
    <?php foreach ($alertas as $a): ?>
    <div class="alerta-item">
        <div class="alerta-dot alerta-dot--<?= $a['tipo'] ?>"></div>
        <div class="alerta-item__text">
            <?= htmlspecialchars($a['texto']) ?>
            <?php if (!empty($a['subtexto'])): ?>
            <span class="alerta-item__sub"><?= htmlspecialchars($a['subtexto']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Legenda -->
<div class="legenda-panel">
    <div class="legenda-panel__title">
        <i class="ti ti-palette" aria-hidden="true"></i>
        Legenda
    </div>

    <div class="legenda-item">
        <div class="legenda-swatch" style="background:#d1fae5;border:1px solid #6ee7b7"></div>
        <span>Coberto — plantão confirmado ✔</span>
    </div>
    <div class="legenda-item">
        <div class="legenda-swatch" style="background:#fee2e2;border:1px solid #fca5a5"></div>
        <span>Vago — sem cuidador alocado ⚠</span>
    </div>
    <div class="legenda-item">
        <div class="legenda-swatch" style="background:#fef3c7;border:1px solid #fcd34d"></div>
        <span>Substituição em andamento ↺</span>
    </div>
    <div class="legenda-item">
        <div class="legenda-swatch" style="background:#f3f4f6;border:1px solid #e5e7eb"></div>
        <span>Turno fora do contrato</span>
    </div>
</div>