<?php
/**
 * app/Views/relatorio_plantao/paciente.php
 * Tela do paciente com histórico de relatórios agrupados por data.
 */

function rp_fmt_date(?string $date, string $fallback = '—'): string
{
    if (!$date) return $fallback;
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $fallback;
}

function rp_fmt_time(?string $date, string $fallback = '—'): string
{
    if (!$date) return $fallback;
    $ts = strtotime($date);
    return $ts ? date('H:i', $ts) : $fallback;
}

function rp_text(mixed $value, string $fallback = '—'): string
{
    if ($value === null) return $fallback;
    $value = trim((string)$value);
    return $value !== '' ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $fallback;
}

$paciente = isset($paciente) && is_array($paciente) ? $paciente : [];
$plantoes = isset($plantoes) && is_array($plantoes) ? $plantoes : [];

$nomePaciente = $paciente['nome_completo'] ?? $paciente['nome'] ?? 'Paciente';
$pacienteUuid = (string)($paciente['uuid'] ?? '');
$totalPlantoes = count($plantoes);

$agrupado = [];
foreach ($plantoes as $p) {
    $dataInicio = $p['data_inicio'] ?? null;
    if (!$dataInicio || strtotime((string)$dataInicio) === false) continue;
    $dia = date('Y-m-d', strtotime((string)$dataInicio));
    $agrupado[$dia][] = $p;
}

ksort($agrupado);
$datasDisponiveis = array_keys($agrupado);
$ultimaData = !empty($datasDisponiveis) ? end($datasDisponiveis) : date('Y-m-d');
$dataSelecionada = $_GET['date'] ?? null;

if (!$dataSelecionada || !isset($agrupado[$dataSelecionada])) {
    $dataSelecionada = $ultimaData;
}

$plantoesDia = $agrupado[$dataSelecionada] ?? [];
$totalDia = count($plantoesDia);

$indiceAtual = array_search($dataSelecionada, $datasDisponiveis, true);
$dataAnterior = $indiceAtual !== false && isset($datasDisponiveis[$indiceAtual - 1]) ? $datasDisponiveis[$indiceAtual - 1] : null;
$dataPosterior = $indiceAtual !== false && isset($datasDisponiveis[$indiceAtual + 1]) ? $datasDisponiveis[$indiceAtual + 1] : null;

$novoLink = BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid) . '/novo';

$partes = array_values(array_filter(explode(' ', trim($nomePaciente))));
$iniciais = '';
foreach (array_slice($partes, 0, 2) as $parte) {
    $iniciais .= mb_strtoupper(mb_substr($parte, 0, 1));
}
$iniciais = $iniciais ?: '?';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_erp.css">

<div class="rp-page">
    <header class="rp-header-card">
        <div class="rp-patient">
            <div class="rp-avatar"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></div>

            <div class="rp-patient-info">
                <h1><?= htmlspecialchars($nomePaciente, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="rp-patient-meta">
                    <?php if (!empty($paciente['prontuario'])): ?>
                        Prontuário #<?= htmlspecialchars((string)$paciente['prontuario'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                    <?php if (!empty($paciente['idade'])): ?>
                        • <?= (int)$paciente['idade'] ?> anos
                    <?php endif; ?>
                    <?php if (!empty($paciente['diagnostico'])): ?>
                        • <?= htmlspecialchars((string)$paciente['diagnostico'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="rp-header-actions">
            <a href="<?= BASE_URL ?>/relatorio-plantao" class="rp-btn rp-btn-secondary">Voltar</a>
            <?php if ($pacienteUuid !== ''): ?>
                <a href="<?= htmlspecialchars($novoLink, ENT_QUOTES, 'UTF-8') ?>" class="rp-btn rp-btn-primary">+ Novo relatório</a>
            <?php endif; ?>
        </div>
    </header>

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

    <?php if (!empty($datasDisponiveis)): ?>
        <div class="rp-date-selector">
            <?php if ($dataAnterior): ?>
                <a class="rp-date-arrow" href="?date=<?= urlencode($dataAnterior) ?>">← Anterior</a>
            <?php else: ?>
                <span class="rp-date-arrow disabled">← Anterior</span>
            <?php endif; ?>

            <form method="GET" class="rp-date-form">
                <label for="rp-date-select">Data do relatório</label>
                <select name="date" id="rp-date-select" onchange="this.form.submit()">
                    <?php foreach ($datasDisponiveis as $data): ?>
                        <option value="<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>" <?= $data === $dataSelecionada ? 'selected' : '' ?>>
                            <?= rp_fmt_date($data) ?> — <?= count($agrupado[$data] ?? []) ?> relatório(s)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($dataPosterior): ?>
                <a class="rp-date-arrow" href="?date=<?= urlencode($dataPosterior) ?>">Próxima →</a>
            <?php else: ?>
                <span class="rp-date-arrow disabled">Próxima →</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($plantoesDia)): ?>
        <div class="rp-empty">
            Nenhum relatório encontrado para <strong><?= rp_fmt_date($dataSelecionada) ?></strong>.
        </div>
    <?php else: ?>
        <section class="rp-cards rp-report-list">
            <?php foreach ($plantoesDia as $relatorio): ?>
                <?php
                    $expanded = false;
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
