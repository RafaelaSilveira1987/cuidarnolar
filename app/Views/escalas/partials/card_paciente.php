<?php
/**
 * Views/escalas/partials/card_paciente.php
 *
 * Variável esperada: $pac (array com a estrutura abaixo)
 *
 * $pac = [
 *   'id'           => 1,
 *   'nome'         => 'João Silva',
 *   'iniciais'     => 'JS',
 *   'cor_avatar'   => '#d1fae5',   // bg
 *   'cor_avatar_t' => '#064e3b',   // texto
 *   'endereco'     => 'Av. Brasil, 420 — Copacabana',
 *   'tipo_contrato'=> '24h',       // '24h' | '12h' | 'diurno'
 *   'cobertura_pct'=> 100,
 *   'turnos'       => [            // array de turnos do contrato
 *     [
 *       'label' => '07h → 19h',
 *       'icone' => 'ti-sun',
 *       'plantoes' => [            // 7 itens, um por dia da semana
 *         [
 *           'data'           => '2025-05-19',
 *           'escala_id'      => 12,
 *           'colaborador_id' => 3,
 *           'colaborador'    => 'Ana Paula',
 *           'inicio'         => '07:00',
 *           'fim'            => '19:00',
 *           'status'         => 'ok',   // 'ok' | 'vago' | 'sub' | 'na'
 *           'sub_nome'       => null,   // nome original se for substituição
 *         ],
 *         ...
 *       ]
 *     ],
 *     ...
 *   ]
 * ];
 *
 * $dias = array de 7 dias [{date, label, num}, ...]  (vem do escopo pai)
 */

// Determina classe e texto da barra de cobertura
$pct = (int)($pac['cobertura_pct'] ?? 0);
$cob_cls = $pct >= 95 ? 'ok' : ($pct >= 70 ? 'warn' : 'danger');

$tipo  = $pac['tipo_contrato'] ?? '12h';
$badge_cls = match($tipo) {
    '24h'    => 'badge-contrato--24h',
    'diurno' => 'badge-contrato--diurno',
    default  => 'badge-contrato--12h',
};
$badge_txt = match($tipo) {
    '24h'    => 'Plantão 24h',
    'diurno' => 'Somente diurno',
    default  => 'Plantão 12h',
};
?>

<div class="paciente-card" id="pac-<?= $pac['id'] ?>">

    <!-- Cabeçalho do card -->
    <div class="paciente-card__header">

        <div class="paciente-card__info">
            <div class="paciente-avatar"
                style="background:<?= htmlspecialchars($pac['cor_avatar']) ?>;color:<?= htmlspecialchars($pac['cor_avatar_t']) ?>">
                <?= htmlspecialchars($pac['iniciais']) ?>
            </div>
            <div>
                <div class="paciente-card__name"><?= htmlspecialchars($pac['nome']) ?></div>
                <div class="paciente-card__address">
                    <i class="ti ti-map-pin" aria-hidden="true"></i>
                    <?= htmlspecialchars($pac['endereco'] ?? '—') ?>
                </div>
            </div>
        </div>

        <div class="paciente-card__meta">
            <span class="badge-contrato <?= $badge_cls ?>"><?= $badge_txt ?></span>
            <div class="cobertura-wrap" id="cob-<?= $pac['id'] ?>">
                <span class="cobertura-label">Cobertura</span>
                <div class="cobertura-bar">
                    <div class="cobertura-bar__fill cobertura-bar__fill--<?= $cob_cls ?>"
                        style="width:<?= min($pct, 100) ?>%"></div>
                </div>
                <span class="cobertura-pct cobertura-pct--<?= $cob_cls ?>"><?= $pct ?>%</span>
            </div>
        </div>

    </div>

    <!-- Grade semanal -->
    <div class="grade-wrap">
        <?php include __DIR__ . '/bloco_plantao.php'; ?>
    </div>

</div>