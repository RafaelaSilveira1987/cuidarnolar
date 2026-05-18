<?php

/**
 * app/Views/relatorio_plantao/paciente.php
 *
 * Variáveis esperadas do Controller:
 *   $paciente  (array)  — dados do paciente
 *   $plantoes  (array)  — todos os plantões (tb_relatorio_plantao)
 *   $cuidadores (array) — mapa cuidador_id → {nome, registro}
 *   $_user     (array)  — usuário logado
 */

function getTurnoKey(string $dt): string
{
    $h = (int) date('H', strtotime($dt));
    if ($h >= 7  && $h < 13) return 'manha';
    if ($h >= 13 && $h < 19) return 'tarde';
    return 'noite';
}

function jsonDecode(mixed $val): mixed
{
    if (is_array($val)) return $val;
    if (!$val) return [];
    $r = json_decode($val, true);
    return is_array($r) ? $r : [];
}

/* Agrupa: data → turno → [plantão] */
$agrupado = [];
foreach ($plantoes ?? [] as $p) {
    $dia = date('Y-m-d', strtotime($p['data_inicio']));
    $agrupado[$dia][] = $p;
}
ksort($agrupado);

$datas     = array_keys($agrupado);
$dataAtual = !empty($datas) ? end($datas) : date('Y-m-d');
$cuidadores = $cuidadores ?? [];

/* Iniciais do nome */
function iniciais(string $nome): string
{
    $p = explode(' ', trim($nome));
    return strtoupper(substr($p[0] ?? '', 0, 1)) . strtoupper(substr($p[1] ?? '', 0, 1));
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_pages.css">

<div class="pac-view">

    <!-- ══ Top bar ══════════════════════════════════════════════ -->
    <div class="pac-topbar">
        <div class="pac-topbar__patient">
            <?php
            $nome   = $paciente['nome_completo'] ?? 'Paciente';
            $av     = iniciais($nome);
            ?>
            <div class="pac-avatar"><?= htmlspecialchars($av) ?></div>
            <div>
                <h1 class="pac-topbar__nome"><?= htmlspecialchars($nome) ?></h1>
                <p class="pac-topbar__meta">
                    <?php if (!empty($paciente['prontuario'])): ?>
                    <i class="ti ti-id-badge-2"></i>
                    Prontuário #<?= htmlspecialchars($paciente['prontuario']) ?>
                    <?php endif ?>
                    <?php if (!empty($paciente['idade'])): ?>
                    · <?= (int)$paciente['idade'] ?> anos
                    <?php endif ?>
                    <?php if (!empty($paciente['diagnostico'])): ?>
                    · <?= htmlspecialchars($paciente['diagnostico']) ?>
                    <?php endif ?>
                </p>
            </div>
        </div>

        <div class="pac-topbar__nav">
            <button class="pac-nav-btn" id="btnDiaAnterior" aria-label="Dia anterior">
                <i class="ti ti-chevron-left"></i>
            </button>
            <span class="pac-nav-label" id="labelData">—</span>
            <button class="pac-nav-btn" id="btnProximoDia" aria-label="Próximo dia">
                <i class="ti ti-chevron-right"></i>
            </button>
        </div>
    </div>

    <!-- ══ Tabs ═════════════════════════════════════════════════ -->
    <div class="pac-tabs">
        <button class="pac-tab active"><i class="ti ti-clipboard-list"></i> Relatório de plantão</button>
        <button class="pac-tab"><i class="ti ti-history"></i> Histórico</button>
        <a href="<?= BASE_URL ?>/relatorio-plantao/paciente/<?= htmlspecialchars($paciente['uuid']) ?>/novo"
            class="pac-tab-action"><i class="ti ti-plus"></i> Novo relatório</a>
    </div>

    <!-- ══ Seletor de turno ═══════════════════════════════════════ -->
    <div class="turnos-row" id="turnosRow"></div>

    <!-- ══ Detalhe ════════════════════════════════════════════════ -->
    <div id="turnoDetalhe"></div>

</div>

<!-- ══ Dados PHP → JS ════════════════════════════════════════════ -->
<script>
window.RELATORIO_DATA = {
    agrupado: <?= json_encode($agrupado, JSON_UNESCAPED_UNICODE) ?>,
    datas: <?= json_encode($datas, JSON_UNESCAPED_UNICODE) ?>,
    cuidadores: <?= json_encode($cuidadores, JSON_UNESCAPED_UNICODE) ?>,
    pacienteUuid: <?= json_encode($paciente['uuid'] ?? '') ?>,
    baseUrl: <?= json_encode(BASE_URL) ?>
};
</script>

<script src="<?= BASE_URL ?>/assets/js/relatorio_plantao_paciente.js"></script>