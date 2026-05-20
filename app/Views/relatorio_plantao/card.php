<?php

/**
 * app/Views/relatorio_plantao/card.php
 *
 * Card resumido do relatório de plantão com exibição
 * do resumo clínico completo.
 *
 * Variáveis esperadas:
 * - $relatorio (array)
 * - $expanded (bool)
 */

// ---------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------
if (!function_exists('rp_card_text')) {
    function rp_card_text(mixed $value, string $fallback = '—'): string
    {
        if ($value === null) {
            return $fallback;
        }

        $value = trim((string)$value);

        return $value !== ''
            ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            : $fallback;
    }
}

if (!function_exists('rp_card_decode_json_or_text')) {
    function rp_card_decode_json_or_text($value, string $fallback = 'Não informado'): string
    {
        if ($value === null || $value === '') {
            return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
        }

        $decoded = is_array($value)
            ? $value
            : json_decode((string) $value, true);

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
                    $via ? "via {$via}" : '',
                    $status ? "({$status})" : '',
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

        if ($text === '') {
            return '';
        }

        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text);
        $text = trim((string)$text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

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
    ? date('H:i', strtotime((string)$dataInicio))
    : '—';

$horaFim = $dataFim && strtotime((string)$dataFim) !== false
    ? date('H:i', strtotime((string)$dataFim))
    : null;

$faixaHorario = $horaInicio;
if ($horaFim) {
    $faixaHorario .= ' às ' . $horaFim;
}

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
// Links
// ---------------------------------------------------------------------
$visualizarLink = BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($plantaoId);
$editarLink     = BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode($plantaoId) . '/editar';

// ---------------------------------------------------------------------
// Sinais vitais
// ---------------------------------------------------------------------
$pa         = $relatorio['pa'] ?? null;
$fc         = $relatorio['fc'] ?? null;
$temperatura = $relatorio['temperatura'] ?? null;
$spo2       = $relatorio['spo2'] ?? null;
$hgt        = $relatorio['hgt'] ?? null;
$nivelDor   = $relatorio['nivel_dor'] ?? null;

// ---------------------------------------------------------------------
// Campos clínicos
// ---------------------------------------------------------------------
$evolucao       = $relatorio['evolucao_enfermagem'] ?? $relatorio['evolucao'] ?? null;
$estadoPaciente = $relatorio['estado_paciente'] ?? null;
$consciencia    = $relatorio['consciencia'] ?? null;
$alimentacao    = $relatorio['alimentacao'] ?? null;
$hidratacaoMl   = $relatorio['hidratacao_ml'] ?? null;
$eliminacoes    = $relatorio['eliminacoes'] ?? null;
$medicacoes     = $relatorio['medicacoes'] ?? $relatorio['medicamentos'] ?? null;
$intercorrencias = $relatorio['intercorrencias'] ?? null;
$higiene        = $relatorio['higiene'] ?? null;
$sono           = $relatorio['sono'] ?? null;
$decubito       = $relatorio['decubito'] ?? null;
$observacoes    = $relatorio['observacoes'] ?? $relatorio['observacoes_gerais'] ?? null;

// ---------------------------------------------------------------------
// Resumo curto (prioriza evolução)
// ---------------------------------------------------------------------
$resumo = rp_card_preview(
    $evolucao ?: $estadoPaciente ?: $observacoes
);

// ---------------------------------------------------------------------
// Indicadores rápidos
// ---------------------------------------------------------------------
$quickBadges = [];

if (!empty($consciencia)) {
    $quickBadges[] = '🧠 ' . rp_card_decode_json_or_text($consciencia);
}

if ($nivelDor !== null && $nivelDor !== '' && (int)$nivelDor > 0) {
    $quickBadges[] = '😣 Dor ' . (int)$nivelDor . '/10';
}

if (!empty($hidratacaoMl)) {
    $quickBadges[] = '💧 ' . (int)$hidratacaoMl . ' ml';
}

if (!empty($alimentacao)) {
    $quickBadges[] = '🍽️ Alimentação registrada';
}

if (!empty($eliminacoes)) {
    $quickBadges[] = '🚽 Eliminações registradas';
}

if (!empty($higiene)) {
    $quickBadges[] = '🛁 Higiene realizada';
}

if (!empty($sono)) {
    $quickBadges[] = '😴 Sono registrado';
}

if (!empty($decubito)) {
    $quickBadges[] = '↔️ Mudança de decúbito';
}
?>

<article class="rp-card <?= $expanded ? 'expanded' : '' ?>"
    data-url="<?= htmlspecialchars($visualizarLink, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Cabeçalho do Card -->
    <button type="button" class="rp-card-header">
        <div class="rp-card-main">
            <div class="rp-card-title">
                🕒 <?= safe_htmlspecialchars($faixaHorario) ?>
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
            <h3>Resumo do plantão</h3>
            <div class="rp-text-box">
                <?= nl2br(htmlspecialchars($resumo, ENT_QUOTES, 'UTF-8')) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Indicadores Clínicos -->
        <?php if (!empty($quickBadges)): ?>
        <section class="rp-section">
            <h3>Resumo clínico</h3>
            <div class="rp-text-box">
                <?php foreach ($quickBadges as $badge): ?>
                <div><?= htmlspecialchars($badge, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Sinais Vitais -->
        <section class="rp-section">
            <h3>Sinais vitais</h3>
            <div class="rp-vitals-grid">
                <div class="rp-vital">
                    <span class="rp-vital-label">PA</span>
                    <strong><?= safe_htmlspecialchars($pa) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">FC</span>
                    <strong><?= safe_htmlspecialchars($fc) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">Temperatura</span>
                    <strong><?= safe_htmlspecialchars($temperatura) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">SpO₂</span>
                    <strong><?= safe_htmlspecialchars($spo2) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">HGT</span>
                    <strong><?= safe_htmlspecialchars($hgt) ?></strong>
                </div>
                <div class="rp-vital">
                    <span class="rp-vital-label">Dor</span>
                    <strong><?= safe_htmlspecialchars($nivelDor) ?></strong>
                </div>
            </div>
        </section>

        <!-- Intercorrências -->
        <?php if (!empty(rp_card_decode_json_or_text($intercorrencias))): ?>
        <section class="rp-section">
            <h3>Intercorrências</h3>
            <div class="rp-text-box rp-text-box-warning">
                <?= rp_card_decode_json_or_text($intercorrencias) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Medicações -->
        <?php $medicacoesFormatadas = rp_card_decode_json_or_text($medicacoes, ''); ?>
        <?php if ($medicacoesFormatadas !== ''): ?>
        <section class="rp-section">
            <h3>Medicações / Condutas</h3>
            <div class="rp-text-box preserve-lines">
                <?= $medicacoesFormatadas ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Observações -->
        <?php if (!empty(rp_card_decode_json_or_text($observacoes))): ?>
        <section class="rp-section">
            <h3>Observações Gerais</h3>
            <div class="rp-text-box">
                <?= rp_card_decode_json_or_text($observacoes) ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Rodapé -->
        <footer class="rp-card-footer">
            <a href="<?= htmlspecialchars($visualizarLink, ENT_QUOTES, 'UTF-8') ?>"
                class="rp-btn rp-btn-secondary stop-propagation">
                Visualizar
            </a>

            <a href="<?= htmlspecialchars($editarLink, ENT_QUOTES, 'UTF-8') ?>"
                class="rp-btn rp-btn-primary stop-propagation">
                Editar
            </a>
        </footer>
    </div>
</article>