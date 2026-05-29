<?php
/**
 * app/Views/escalas/partials/bloco_plantao.php
 * Célula de plantão com cor por cuidador e ações rápidas.
 */

$plantao = $plantao ?? [];
$turnoLabel = $turnoLabel ?? 'Turno';
$status = (string)($plantao['status'] ?? 'vago');
$temCuidador = !empty($plantao['colaborador']);
$isVago = $status === 'vago' || !$temCuidador;
$isSub = $status === 'sub';
$isSugerido = $status === 'sugerido';

if (!function_exists('escala_cor_cuidador')) {
    function escala_cor_cuidador(string $chave): array
    {
        $hash = abs(crc32($chave ?: 'sem-cuidador'));
        $hue = $hash % 360;
        return [
            "hsl({$hue} 88% 94%)",
            "hsl({$hue} 72% 76%)",
            "hsl({$hue} 64% 26%)",
        ];
    }
}

$chaveCor = (string)($plantao['colaborador_uuid'] ?? $plantao['colaborador_id'] ?? $plantao['colaborador'] ?? '');
[$bg, $border, $text] = escala_cor_cuidador($chaveCor);
$badge = $isVago ? 'VAGO' : ($isSub ? 'SUB' : 'OK');
$classe = $isVago ? 'is-vago' : ($isSub ? 'is-sub' : 'is-ok');
$titulo = trim(($plantao['colaborador'] ?? '') . ' ' . ($plantao['inicio'] ?? '') . '-' . ($plantao['fim'] ?? ''));
?>

<div class="plantao-cell <?= $classe ?>"
    role="button"
    tabindex="0"
    title="<?= htmlspecialchars($titulo ?: 'Plantão em aberto') ?>"
    style="<?= !$isVago ? '--care-bg:' . htmlspecialchars($bg) . ';--care-border:' . htmlspecialchars($border) . ';--care-text:' . htmlspecialchars($text) . ';' : '' ?>"
    data-escala-id="<?= htmlspecialchars((string)($plantao['escala_id'] ?? '')) ?>"
    data-paciente-uuid="<?= htmlspecialchars((string)($plantao['paciente_uuid'] ?? $pacienteUuid ?? '')) ?>"
    data-colaborador-uuid="<?= htmlspecialchars((string)($plantao['colaborador_uuid'] ?? '')) ?>"
    data-colaborador-id="<?= htmlspecialchars((string)($plantao['colaborador_id'] ?? '')) ?>"
    data-data-plantao="<?= htmlspecialchars((string)($plantao['data'] ?? '')) ?>"
    data-turno="<?= htmlspecialchars((string)($plantao['turno_codigo'] ?? 'diurno')) ?>"
    data-inicio="<?= htmlspecialchars((string)($plantao['inicio'] ?? '07:00')) ?>"
    data-fim="<?= htmlspecialchars((string)($plantao['fim'] ?? '19:00')) ?>">

    <div class="plantao-cell__top">
        <span class="plantao-cell__nome">
            <?= htmlspecialchars($isVago ? 'Sem cuidador' : ($plantao['colaborador'] ?? 'Cuidador')) ?>
        </span>
        <span class="plantao-cell__badge"><?= $badge ?></span>
    </div>

    <div class="plantao-cell__hora">
        <?= htmlspecialchars((string)($plantao['inicio'] ?? '--:--')) ?>–<?= htmlspecialchars((string)($plantao['fim'] ?? '--:--')) ?>
        <?php if ($isSugerido): ?>
            <span class="plantao-cell__hint">prévia</span>
        <?php endif; ?>
    </div>

    <?php if ($isSub && !empty($plantao['sub_nome'])): ?>
        <div class="plantao-cell__sub">Substitui <?= htmlspecialchars($plantao['sub_nome']) ?></div>
    <?php endif; ?>

    <div class="plantao-cell__actions" aria-label="Ações do plantão">
        <button type="button" data-action="editar" title="Editar/alocar plantão">
            <i class="ti ti-pencil" aria-hidden="true"></i>
        </button>

        <?php if (!empty($plantao['escala_id'])): ?>
            <button type="button" data-action="substituir" title="Substituir cuidador">
                <i class="ti ti-arrows-exchange" aria-hidden="true"></i>
            </button>
            <button type="button" data-action="excluir" data-escala-id="<?= htmlspecialchars((string)$plantao['escala_id']) ?>" title="Excluir plantão">
                <i class="ti ti-trash" aria-hidden="true"></i>
            </button>
        <?php endif; ?>
    </div>
</div>
