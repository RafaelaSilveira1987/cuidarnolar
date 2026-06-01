<?php
/**
 * app/Views/escalas/partials/bloco_plantao.php
 * Bloco compacto do plantão dentro da grade semanal/mensal.
 * Recebe: $plantao, $turno, $pac, $dia.
 */

$plantao = is_array($plantao ?? null) ? $plantao : [];
$turno = is_array($turno ?? null) ? $turno : [];
$pac = is_array($pac ?? null) ? $pac : [];
$dia = is_array($dia ?? null) ? $dia : [];

$escalaId = $plantao['escala_id'] ?? null;
$pacienteUuid = (string)($plantao['paciente_uuid'] ?? $pac['uuid'] ?? '');
$cuidadorUuid = (string)($plantao['colaborador_uuid'] ?? '');
$cuidadorId = (string)($plantao['colaborador_id'] ?? '');
$nomeCuidador = trim((string)($plantao['colaborador'] ?? ''));
$dataPlantao = (string)($plantao['data'] ?? $dia['date'] ?? '');
$inicio = (string)($plantao['inicio'] ?? $turno['inicio'] ?? '');
$fim = (string)($plantao['fim'] ?? $turno['fim'] ?? '');
$status = (string)($plantao['status'] ?? 'vago');
$turnoCodigo = (string)($plantao['turno_codigo'] ?? $turno['codigo'] ?? 'diurno');
$corCuidador = trim((string)($plantao['colaborador_cor'] ?? ''));
$temCuidador = $nomeCuidador !== '' && $nomeCuidador !== '—';

$styleColor = $corCuidador !== '' ? ' style="--cuidador-cor:' . e($corCuidador) . '"' : '';
$nomeExibir = $temCuidador ? $nomeCuidador : 'Sem cuidador';
$statusLabel = match ($status) {
    'sub' => 'Substituído',
    'sugerido' => 'Prévia',
    'vago' => 'Vago',
    default => 'OK',
};
?>

<div class="plantao-cell plantao-cell--<?= e($status) ?>"
    <?= $styleColor ?>
    draggable="<?= $escalaId ? 'true' : 'false' ?>"
    data-escala-id="<?= e((string)$escalaId) ?>"
    data-paciente-uuid="<?= e($pacienteUuid) ?>"
    data-cuidador-uuid="<?= e($cuidadorUuid) ?>"
    data-cuidador-id="<?= e($cuidadorId) ?>"
    data-data="<?= e($dataPlantao) ?>"
    data-turno="<?= e($turnoCodigo) ?>"
    data-inicio="<?= e($inicio) ?>"
    data-fim="<?= e($fim) ?>"
    data-observacao="<?= e((string)($plantao['observacoes'] ?? '')) ?>">

    <div class="plantao-cell__head">
        <span class="plantao-cell__dot" aria-hidden="true"></span>
        <span class="plantao-cell__status"><?= e($statusLabel) ?></span>
    </div>

    <div class="plantao-cell__body">
        <strong class="plantao-cell__nome"><?= e($nomeExibir) ?></strong>
        <span class="plantao-cell__hora"><?= e($inicio) ?> - <?= e($fim) ?></span>
        <?php if (!empty($plantao['sub_nome'])): ?>
            <small class="plantao-cell__sub">Original: <?= e((string)$plantao['sub_nome']) ?></small>
        <?php endif; ?>
    </div>

    <div class="plantao-cell__actions">
        <?php if ($escalaId): ?>
            <button type="button" class="plantao-action js-edit-plantao" title="Editar plantão">Editar</button>

            <form method="POST" action="<?= BASE_URL ?>/escala/excluir" class="plantao-action-form js-delete-plantao">
                <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                <input type="hidden" name="escala_id" value="<?= e((string)$escalaId) ?>">
                <input type="hidden" name="semana" value="<?= e($dataPlantao) ?>">
                <button type="submit" class="plantao-action plantao-action--danger" title="Excluir plantão">Excluir</button>
            </form>

            <button type="button" class="plantao-action js-substituir-plantao" title="Substituir cuidador">Substituir</button>
        <?php else: ?>
            <button type="button" class="plantao-action js-open-criar-plantao" data-paciente-uuid="<?= e($pacienteUuid) ?>" data-data="<?= e($dataPlantao) ?>" data-turno="<?= e($turnoCodigo) ?>">Alocar</button>
        <?php endif; ?>
    </div>
</div>
