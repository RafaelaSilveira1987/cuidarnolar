<?php
/**
 * app/Views/relatorio_plantao/partials/periodo_selector.php
 *
 * Substitui o antigo turno_selector.php.
 * Renderiza cards de período livre (sem manhã/tarde/noite fixos).
 *
 * Variáveis herdadas do diario.php:
 *   $periodos      array  — todos os períodos do dia
 *   $periodoIdx    int    — índice do período selecionado
 *   $dataConsulta  string — "Y-m-d" para manter na URL
 *   $paciente_id   int
 */
?>

<div class="rp-periodos-header">
    <span class="rp-periodos-label">Períodos do dia</span>
    <a href="/relatorio-plantao/<?= $paciente_id ?>/adicionar-periodo?data=<?= urlencode($dataConsulta) ?>"
       class="btn-add-periodo">
        + Adicionar período
    </a>
</div>

<div class="rp-periodos">

    <?php if (empty($periodos)): ?>
        <p class="text-muted" style="padding: 12px 0; font-size: 13px;">
            Nenhum período cadastrado para esta data.
        </p>
    <?php else: ?>
        <?php foreach ($periodos as $idx => $periodo): ?>
            <?php
                $ativo   = ($idx === $periodoIdx);
                $partes  = explode(' ', $periodo['enfermeiro']);
                $iniciais = strtoupper(($partes[0][0] ?? '') . ($partes[1][0] ?? ''));
                $url     = '?data=' . urlencode($dataConsulta) . '&periodo=' . $idx;
            ?>
            <a href="<?= $url ?>"
               class="periodo-card <?= $ativo ? 'selected' : '' ?>"
               title="<?= htmlspecialchars($periodo['enfermeiro']) ?>">

                <div class="periodo-card__top">
                    <span class="periodo-card__horario">
                        <?= htmlspecialchars($periodo['hora_inicio']) ?>
                        &ndash;
                        <?= htmlspecialchars($periodo['hora_fim']) ?>
                    </span>
                    <span class="turno-card__badge badge--<?= htmlspecialchars($periodo['status']) ?>">
                        <?= htmlspecialchars($periodo['status_label']) ?>
                    </span>
                </div>

                <div class="periodo-card__duracao">
                    <?= htmlspecialchars($periodo['duracao_label']) ?>
                </div>

                <div class="periodo-card__enfermeiro">
                    <span class="periodo-card__enf-avatar"><?= htmlspecialchars($iniciais) ?></span>
                    <span class="periodo-card__enf-nome"><?= htmlspecialchars($periodo['enfermeiro']) ?></span>
                </div>

            </a>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Card de adicionar (atalho visual) -->
    <a href="/relatorio-plantao/<?= $paciente_id ?>/adicionar-periodo?data=<?= urlencode($dataConsulta) ?>"
       class="periodo-card periodo-card--add"
       title="Adicionar período">
        <div class="periodo-card__add-icon">+</div>
        <div class="periodo-card__add-text">Novo período</div>
    </a>

</div>
