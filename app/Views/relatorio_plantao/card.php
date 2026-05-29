<?php

/**
 * app/Views/relatorio_plantao/card.php
 *
 * Item em lista/accordion do relatório de plantão.
 * Usado em dias com mais de um plantão (ex.: plantão da manhã + tarde).
 *
 * Variáveis esperadas:
 * - $relatorio (array)
 * - $expanded  (bool)
 *
 * Nota: o botão "Visualizar" foi removido.
 * O card agora existe apenas como resumo rápido dentro do show.php
 * quando há múltiplos plantões no mesmo dia.
 */

// ---------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------
if (!function_exists('rp_card_text')) {
    function rp_card_text(mixed $value, string $fallback = '—'): string
    {
        if ($value === null) return $fallback;
        $value = trim((string)$value);
        return $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $fallback;
    }
}

if (!function_exists('rp_card_decode_json_or_text')) {
    function rp_card_decode_json_or_text($value, string $fallback = 'Não informado'): string
    {
        if ($value === null || $value === '') {
            return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
        }

        $decoded = is_array($value) ? $value : json_decode((string) $value, true);

        if (!is_array($decoded)) {
            return nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }

        $linhas = [];
        foreach ($decoded as $index => $item) {
            if (is_array($item)) {
                $medicamento = trim($item['medicamento'] ?? '');
                $horario     = trim($item['horario'] ?? '');
                $via         = trim($item['via'] ?? '');
                $status      = trim($item['status'] ?? '');
                $partes = array_filter([
                    $medicamento,
                    $horario ? "às {$horario}" : '',
                    $via     ? "via {$via}"    : '',
                    $status  ? "({$status})"   : '',
                ]);
                $linhas[] = htmlspecialchars(
                    ($index + 1) . ' - ' . implode(' ', $partes),
                    ENT_QUOTES,
                    'UTF-8'
                );
            } else {
                $linhas[] = htmlspecialchars((string) $item, ENT_QUOTES, 'UTF-8');
            }
        }

        return !empty($linhas)
            ? implode("\n", $linhas)
            : htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('rp_card_preview')) {
    function rp_card_preview(mixed $value, int $limit = 180): string
    {
        $text = rp_card_decode_json_or_text($value);
        if ($text === '') return '';
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string)$text);
        if (mb_strlen($text) <= $limit) return $text;
        return mb_substr($text, 0, $limit) . '...';
    }
}

if (!function_exists('safe_htmlspecialchars')) {
    function safe_htmlspecialchars($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// ---------------------------------------------------------------------
// Normalização
// ---------------------------------------------------------------------
$relatorio = isset($relatorio) && is_array($relatorio) ? $relatorio : [];
$expanded  = !empty($expanded);

// ---------------------------------------------------------------------
// Dados básicos
// ---------------------------------------------------------------------
$status      = strtolower((string)($relatorio['status'] ?? 'rascunho'));
$dataInicio  = $relatorio['data_inicio'] ?? null;
$dataFim     = $relatorio['data_fim'] ?? null;

$horaInicio = $dataInicio && strtotime((string)$dataInicio) !== false
    ? date('H:i', strtotime((string)$dataInicio)) : '—';

$horaFim = $dataFim && strtotime((string)$dataFim) !== false
    ? date('H:i', strtotime((string)$dataFim)) : null;

$faixaHorario = $horaInicio;
if ($horaFim) $faixaHorario .= ' às ' . $horaFim;

$profissional = $relatorio['profissional_nome']
    ?? $relatorio['responsavel_nome']
    ?? $relatorio['nome_profissional']
    ?? 'Responsável não informado';

$plantaoId = (string)($relatorio['uuid'] ?? $relatorio['id'] ?? '');

// ---------------------------------------------------------------------
// Status
// ---------------------------------------------------------------------
$statusLabel = match ($status) {
    'assinado'   => 'Assinado',
    'finalizado' => 'Finalizado',
    'rascunho'   => 'Rascunho',
    default      => 'Registrado',
};

$statusClass = match ($status) {
    'assinado'   => 'success',
    'finalizado' => 'primary',
    'rascunho'   => 'warning',
    default      => 'secondary',
};

// ---------------------------------------------------------------------
// Links — apenas editar
// ---------------------------------------------------------------------
$editarLink = BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($plantaoId) . '/editar';
$pdfLink = $plantaoId !== ''
    ? BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($plantaoId) . '/pdf'
    : '#';
$pacienteUuidForNovo = (string)($relatorio['paciente_uuid'] ?? ($pacienteUuid ?? ''));
$novoLink = $pacienteUuidForNovo !== ''
    ? BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuidForNovo) . '/novo'
    : '#';

// ---------------------------------------------------------------------
// Sinais vitais
// ---------------------------------------------------------------------
$pa          = $relatorio['pa'] ?? null;
$fc          = $relatorio['fc'] ?? null;
$temperatura = $relatorio['temperatura'] ?? null;
$spo2        = $relatorio['spo2'] ?? null;
$hgt         = $relatorio['hgt'] ?? null;
$fr          = $relatorio['frequencia_respiratoria'] ?? null;
$nivelDor    = $relatorio['nivel_dor'] ?? null;

// ---------------------------------------------------------------------
// Campos clínicos
// ---------------------------------------------------------------------
$evolucao        = $relatorio['evolucao_enfermagem'] ?? $relatorio['evolucao'] ?? null;
$estadoPaciente  = $relatorio['estado_paciente'] ?? null;
$consciencia     = $relatorio['consciencia'] ?? null;
$alimentacao     = $relatorio['alimentacao'] ?? null;
$eliminacoes     = $relatorio['eliminacoes'] ?? null;
$medicacoes      = $relatorio['medicacoes'] ?? $relatorio['medicamentos'] ?? null;
$intercorrencias = $relatorio['intercorrencias'] ?? null;
$higiene         = $relatorio['higiene'] ?? null;
$sono            = $relatorio['sono'] ?? null;
$decubito        = $relatorio['decubito'] ?? null;
$observacoes     = $relatorio['observacoes'] ?? $relatorio['observacoes_gerais'] ?? null;
$visita          = $relatorio['visita_medica'] ?? null;
$passagem        = $relatorio['plantao_entregue_para'] ?? null;

// Hidratação
$hidratacaoRegistros = [];
if (!empty($relatorio['hidratacao_registros'])) {
    $dec = is_array($relatorio['hidratacao_registros'])
        ? $relatorio['hidratacao_registros']
        : json_decode((string)$relatorio['hidratacao_registros'], true);
    if (is_array($dec)) $hidratacaoRegistros = $dec;
}
$totalHidratacao = array_sum(array_column($hidratacaoRegistros, 'ml'));

// Urina / Fezes
$urinaHorarios = [];
if (!empty($relatorio['urina_horarios'])) {
    $dec = is_array($relatorio['urina_horarios'])
        ? $relatorio['urina_horarios']
        : json_decode((string)$relatorio['urina_horarios'], true);
    if (is_array($dec)) $urinaHorarios = array_filter($dec);
}
$fezesHorarios = [];
if (!empty($relatorio['fezes_horarios'])) {
    $dec = is_array($relatorio['fezes_horarios'])
        ? $relatorio['fezes_horarios']
        : json_decode((string)$relatorio['fezes_horarios'], true);
    if (is_array($dec)) $fezesHorarios = array_filter($dec);
}

// Resumo curto
$resumo = rp_card_preview($evolucao ?: $estadoPaciente ?: $observacoes);

// ---------------------------------------------------------------------
// Indicadores rápidos
// ---------------------------------------------------------------------
$quickBadges = [];

if (!empty($consciencia)) {
    $quickBadges[] = ['icon' => 'ti-brain',              'label' => rp_card_decode_json_or_text($consciencia)];
}
if ($nivelDor !== null && $nivelDor !== '' && (int)$nivelDor > 0) {
    $quickBadges[] = ['icon' => 'ti-mood-sad',           'label' => 'Dor ' . (int)$nivelDor . '/10'];
}
if ($totalHidratacao > 0) {
    $quickBadges[] = ['icon' => 'ti-droplet',            'label' => $totalHidratacao . ' ml'];
}
if (!empty($alimentacao)) {
    $quickBadges[] = ['icon' => 'ti-salad',              'label' => 'Alimentação registrada'];
}
if (!empty($urinaHorarios)) {
    $quickBadges[] = ['icon' => 'ti-droplet-filled',     'label' => 'Urina: ' . implode(', ', $urinaHorarios)];
}
if (!empty($fezesHorarios)) {
    $quickBadges[] = ['icon' => 'ti-replace',            'label' => 'Fezes: ' . implode(', ', $fezesHorarios)];
}
if (!empty($higiene)) {
    $quickBadges[] = ['icon' => 'ti-bath',               'label' => htmlspecialchars((string)$higiene, ENT_QUOTES, 'UTF-8')];
}
if (!empty($sono)) {
    $quickBadges[] = ['icon' => 'ti-moon',               'label' => 'Sono: ' . htmlspecialchars((string)$sono, ENT_QUOTES, 'UTF-8')];
}
if (!empty($decubito)) {
    $quickBadges[] = ['icon' => 'ti-arrows-transfer-up', 'label' => 'Mudança de decúbito'];
}
if (!empty($passagem)) {
    $quickBadges[] = ['icon' => 'ti-transfer',           'label' => 'Entregue para: ' . htmlspecialchars((string)$passagem, ENT_QUOTES, 'UTF-8')];
}
?>

<article class="rp-card rp-report-item <?= $expanded ? 'expanded' : '' ?>">

    <!-- Cabeçalho do Card -->
    <button type="button" class="rp-card-header" aria-expanded="<?= $expanded ? 'true' : 'false' ?>">
        <div class="rp-card-main">
            <div class="rp-card-title">
                <i class="ti ti-clock" aria-hidden="true"></i>
                <?= safe_htmlspecialchars($faixaHorario) ?>
            </div>
            <div class="rp-card-subtitle">
                <?= safe_htmlspecialchars($profissional) ?>
            </div>
        </div>
        <div class="rp-card-right">
            <span class="rp-badge rp-badge-<?= safe_htmlspecialchars($statusClass) ?>">
                <?= safe_htmlspecialchars($statusLabel) ?>
            </span>
        </div>
    </button>

    <!-- Conteúdo do Card -->
    <div class="rp-card-body">

        <!-- Resumo do Plantão -->
        <?php if ($resumo !== ''): ?>
        <section class="rp-section">
            <h3><i class="ti ti-notes" aria-hidden="true"></i> Resumo do Plantão</h3>
            <div class="rp-text-box">
                <?= nl2br(htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Indicadores Clínicos -->
        <?php if (!empty($quickBadges)): ?>
        <section class="rp-section">
            <h3><i class="ti ti-heart-rate-monitor" aria-hidden="true"></i> Resumo Clínico</h3>
            <div class="rp-text-box rp-clinical-badges">
                <?php foreach ($quickBadges as $badge): ?>
                <div class="rp-clinical-badge-row">
                    <i class="ti <?= htmlspecialchars($badge['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                    <span><?= $badge['label'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Sinais Vitais -->
        <section class="rp-section">
            <h3><i class="ti ti-activity" aria-hidden="true"></i> Sinais Vitais</h3>
            <div class="rp-vitals-grid">
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-thermometer" aria-hidden="true"></i> Temp.
                    </span>
                    <strong><?= safe_htmlspecialchars($temperatura) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-stethoscope" aria-hidden="true"></i> PA
                    </span>
                    <strong><?= safe_htmlspecialchars($pa) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-heart-rate-monitor" aria-hidden="true"></i> FC
                    </span>
                    <strong><?= safe_htmlspecialchars($fc) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-lungs" aria-hidden="true"></i> SpO₂
                    </span>
                    <strong><?= safe_htmlspecialchars($spo2) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-wind" aria-hidden="true"></i> FR
                    </span>
                    <strong><?= safe_htmlspecialchars($fr) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-droplet-half" aria-hidden="true"></i> HGT
                    </span>
                    <strong><?= safe_htmlspecialchars($hgt) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">
                        <i class="ti ti-mood-sad" aria-hidden="true"></i> Dor
                    </span>
                    <strong><?= safe_htmlspecialchars($nivelDor) ?></strong>
                </div>
            </div>
        </section>

        <!-- Visita Médica -->
        <?php if (!empty($visita)): ?>
        <section class="rp-section">
            <h3><i class="ti ti-user-check" aria-hidden="true"></i> Visita Médica</h3>
            <div class="rp-text-box">
                <?= nl2br(htmlspecialchars((string)$visita, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Intercorrências -->
        <?php if (!empty(rp_card_decode_json_or_text($intercorrencias))): ?>
        <section class="rp-section">
            <h3><i class="ti ti-alert-triangle" aria-hidden="true"></i> Intercorrências</h3>
            <div class="rp-text-box rp-text-box-warning">
                <?= rp_card_decode_json_or_text($intercorrencias) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Medicações -->
        <?php $medicacoesFormatadas = rp_card_decode_json_or_text($medicacoes, ''); ?>
        <?php if ($medicacoesFormatadas !== ''): ?>
        <section class="rp-section">
            <h3><i class="ti ti-pill" aria-hidden="true"></i> Medicações / Condutas</h3>
            <div class="rp-text-box preserve-lines">
                <?= $medicacoesFormatadas ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Passagem de Plantão -->
        <?php if (!empty($passagem)): ?>
        <section class="rp-section">
            <h3><i class="ti ti-transfer" aria-hidden="true"></i> Passagem de Plantão</h3>
            <div class="rp-text-box">
                Entregue para: <?= htmlspecialchars((string)$passagem, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Observações -->
        <?php if (!empty(rp_card_decode_json_or_text($observacoes))): ?>
        <section class="rp-section">
            <h3><i class="ti ti-pencil" aria-hidden="true"></i> Observações Gerais</h3>
            <div class="rp-text-box">
                <?= rp_card_decode_json_or_text($observacoes) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Rodapé — apenas Editar -->
        <footer class="rp-card-footer">
            <a href="<?= htmlspecialchars($pdfLink, ENT_QUOTES, 'UTF-8') ?>"
                class="rp-btn rp-btn-secondary stop-propagation" target="_blank" rel="noopener">
                <i class="ti ti-printer" aria-hidden="true"></i> PDF
            </a>
            <a href="<?= htmlspecialchars($editarLink, ENT_QUOTES, 'UTF-8') ?>"
                class="rp-btn rp-btn-primary stop-propagation">
                <i class="ti ti-pencil" aria-hidden="true"></i> Editar
            </a>
            <?php if ($novoLink !== '#'): ?>
            <a href="<?= htmlspecialchars($novoLink, ENT_QUOTES, 'UTF-8') ?>"
                class="rp-btn rp-btn-secondary stop-propagation">
                <i class="ti ti-plus" aria-hidden="true"></i> Novo
            </a>
            <?php endif; ?>
        </footer>

    </div>
</article>