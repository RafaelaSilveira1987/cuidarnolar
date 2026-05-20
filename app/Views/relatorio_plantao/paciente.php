<?php
/**
 * app/Views/relatorio_plantao/paciente.php
 *
 * Layout ERP profissional para visualização dos relatórios de plantão.
 * Funcionalidades:
 * - Header clínico do paciente
 * - Filtro de data no topo
 * - Exibição somente dos plantões da data selecionada
 * - Cards por faixa de horário
 * - Links para visualizar e editar
 *
 * Variáveis esperadas:
 * - $paciente (array)
 * - $plantoes (array)
 */

// ======================================================
// Funções auxiliares
// ======================================================

function rp_fmt_date(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $fallback;
}

function rp_fmt_time(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);
    return $ts ? date('H:i', $ts) : $fallback;
}

function rp_text(mixed $value, string $fallback = '—'): string
{
    if ($value === null) {
        return $fallback;
    }

    $value = trim((string)$value);
    return $value !== '' ? htmlspecialchars($value) : $fallback;
}

// ======================================================
// Normalização de dados
// ======================================================

$paciente = isset($paciente) && is_array($paciente) ? $paciente : [];
$plantoes = isset($plantoes) && is_array($plantoes) ? $plantoes : [];

$nomePaciente = $paciente['nome_completo']
    ?? $paciente['nome']
    ?? 'Paciente';

$pacienteUuid = (string)($paciente['uuid'] ?? '');
$totalPlantoes = count($plantoes);

// ======================================================
// Agrupamento por data
// ======================================================

$agrupado = [];

foreach ($plantoes as $p) {
    $dataInicio = $p['data_inicio'] ?? null;

    if (!$dataInicio) {
        continue;
    }

    $dia = date('Y-m-d', strtotime($dataInicio));
    $agrupado[$dia][] = $p;
}

ksort($agrupado);

$datasDisponiveis = array_keys($agrupado);
$ultimaData = !empty($datasDisponiveis)
    ? end($datasDisponiveis)
    : date('Y-m-d');

// ======================================================
// Data selecionada via GET (?date=YYYY-MM-DD)
// ======================================================

$dataSelecionada = $_GET['date'] ?? null;

if (!$dataSelecionada || !isset($agrupado[$dataSelecionada])) {
    $dataSelecionada = $ultimaData;
}

$plantoesDia = $agrupado[$dataSelecionada] ?? [];
$totalDia = count($plantoesDia);

// ======================================================
// Links
// ======================================================

$novoLink = BASE_URL
    . '/relatorio-plantao/paciente/'
    . rawurlencode($pacienteUuid)
    . '/novo';

// ======================================================
// Avatar
// ======================================================

$partes = array_values(array_filter(explode(' ', trim($nomePaciente))));
$iniciais = '';

foreach (array_slice($partes, 0, 2) as $parte) {
    $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
}

$iniciais = $iniciais ?: '?';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_erp.css">

<div class="rp-page">

    <!-- ==================================================
         Header do paciente
    =================================================== -->
    <header class="rp-header-card">
        <div class="rp-patient">
            <div class="rp-avatar">
                <?= htmlspecialchars($iniciais) ?>
            </div>

            <div class="rp-patient-info">
                <h1><?= htmlspecialchars($nomePaciente) ?></h1>

                <p class="rp-patient-meta">
                    <?php if (!empty($paciente['prontuario'])): ?>
                    Prontuário #<?= htmlspecialchars((string)$paciente['prontuario']) ?>
                    <?php endif; ?>

                    <?php if (!empty($paciente['idade'])): ?>
                    • <?= (int)$paciente['idade'] ?> anos
                    <?php endif; ?>

                    <?php if (!empty($paciente['diagnostico'])): ?>
                    • <?= htmlspecialchars((string)$paciente['diagnostico']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="rp-header-actions">
            <a href="<?= BASE_URL ?>/relatorio-plantao" class="rp-btn rp-btn-secondary">
                Voltar
            </a>

            <a href="<?= htmlspecialchars($novoLink) ?>" class="rp-btn rp-btn-primary">
                + Novo relatório
            </a>
        </div>
    </header>

    <!-- ==================================================
         Barra de informações
    =================================================== -->
    <div class="rp-stats">
        <div class="rp-stat">
            <span class="rp-stat-label">Total de relatórios</span>
            <strong><?= $totalPlantoes ?></strong>
        </div>

        <div class="rp-stat">
            <span class="rp-stat-label">Data selecionada</span>
            <strong><?= rp_fmt_date($dataSelecionada) ?></strong>
        </div>

        <div class="rp-stat">
            <span class="rp-stat-label">Plantões no dia</span>
            <strong><?= $totalDia ?></strong>
        </div>
    </div>

    <!-- ==================================================
         Filtro de datas
    =================================================== -->
    <?php if (!empty($datasDisponiveis)): ?>
    <nav class="rp-date-nav">
        <?php foreach ($datasDisponiveis as $data): ?>
        <a href="?date=<?= urlencode($data) ?>" class="rp-date-pill <?= $data === $dataSelecionada ? 'active' : '' ?>">
            <?= rp_fmt_date($data) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <!-- ==================================================
         Lista de plantões da data selecionada
    =================================================== -->
    <?php if (empty($plantoesDia)): ?>
    <div class="rp-empty">
        Nenhum relatório encontrado para
        <strong><?= rp_fmt_date($dataSelecionada) ?></strong>.
    </div>
    <?php else: ?>
    <section class="rp-cards">
        <?php foreach ($plantoesDia as $i => $relatorio): ?>
        <?php
                // Primeiro card aberto por padrão
                $expanded = ($i === 0);

                // Inclui o card individual
                include __DIR__ . '/card.php';
                ?>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

</div>

<script>
window.RELATORIO_DATA = {
    baseUrl: <?= json_encode(BASE_URL) ?>,
    pacienteUuid: <?= json_encode($pacienteUuid) ?>,
    dataSelecionada: <?= json_encode($dataSelecionada) ?>
};
</script>

<script src="<?= BASE_URL ?>/assets/js/relatorio_plantao_paciente.js"></script>