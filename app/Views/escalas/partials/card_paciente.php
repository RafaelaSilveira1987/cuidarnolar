<?php
/**
 * app/Views/escalas/partials/card_paciente.php
 * Card visual de escala por paciente, em formato de calendário semanal.
 */

$pac = $pac ?? [];
$dias = $dias ?? [];
$turnos = $pac['turnos'] ?? [];
$percentual = (int)($pac['cobertura_pct'] ?? 0);
$tipoContrato = $pac['tipo_contrato'] ?? '—';
$corAvatar = $pac['cor_avatar'] ?? '#dbeafe';
$corAvatarTexto = $pac['cor_avatar_t'] ?? '#1e3a8a';
$pacienteUuid = $pac['uuid'] ?? '';
?>

<article class="escala-paciente-card">
    <header class="escala-paciente-head">
        <div class="escala-paciente-identidade">
            <span class="escala-avatar" style="background: <?= htmlspecialchars($corAvatar) ?>; color: <?= htmlspecialchars($corAvatarTexto) ?>;">
                <?= htmlspecialchars($pac['iniciais'] ?? 'P') ?>
            </span>

            <div>
                <h2><?= htmlspecialchars($pac['nome'] ?? 'Paciente') ?></h2>
                <p class="escala-muted">
                    <i class="ti ti-map-pin" aria-hidden="true"></i>
                    <?= htmlspecialchars($pac['endereco'] ?: 'Endereço não informado') ?>
                </p>
                <p class="escala-muted">
                    Cuidador referência:
                    <strong><?= htmlspecialchars($pac['cuidador_referencia_nome'] ?? 'não definido') ?></strong>
                </p>
            </div>
        </div>

        <div class="escala-paciente-status">
            <span class="escala-contrato-badge">Contrato <?= htmlspecialchars($tipoContrato) ?></span>
            <span class="escala-progress-label">Cobertura</span>
            <span class="escala-progress"><span style="width: <?= $percentual ?>%"></span></span>
            <strong class="escala-percent <?= $percentual < 50 ? 'is-danger' : ($percentual < 100 ? 'is-warning' : 'is-ok') ?>">
                <?= $percentual ?>%
            </strong>
        </div>
    </header>

    <div class="escala-week-table" role="table" aria-label="Escala semanal de <?= htmlspecialchars($pac['nome'] ?? 'paciente') ?>">
        <div class="escala-week-row escala-week-row--head" role="row">
            <div class="escala-week-cell escala-week-cell--turno" role="columnheader">Turno</div>
            <?php foreach ($dias as $dia): ?>
                <div class="escala-week-cell escala-week-cell--day" role="columnheader">
                    <span><?= htmlspecialchars($dia['label'] ?? '') ?></span>
                    <strong><?= htmlspecialchars($dia['num'] ?? '') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($turnos)): ?>
            <div class="escala-empty-line">Nenhum turno configurado para este paciente.</div>
        <?php else: ?>
            <?php foreach ($turnos as $turno): ?>
                <div class="escala-week-row" role="row">
                    <div class="escala-week-cell escala-week-cell--turno" role="rowheader">
                        <i class="ti <?= htmlspecialchars($turno['icone'] ?? 'ti-clock') ?>" aria-hidden="true"></i>
                        <span><?= htmlspecialchars($turno['label'] ?? 'Turno') ?></span>
                    </div>

                    <?php foreach (($turno['plantoes'] ?? []) as $plantao): ?>
                        <div class="escala-week-cell" role="cell">
                            <?php
                            $turnoLabel = $turno['label'] ?? 'Turno';
                            include __DIR__ . '/bloco_plantao.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</article>
