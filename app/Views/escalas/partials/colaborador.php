<?php
/**
 * Views/escalas/colaborador.php
 * Visão da agenda semanal de um cuidador específico.
 *
 * $col = [
 *   'id'          => 2,
 *   'nome'        => 'Ana Paula',
 *   'iniciais'    => 'AP',
 *   'cor_avatar'  => '#dbeafe',
 *   'cor_avatar_t'=> '#1e3a8a',
 *   'horas_semana'=> 48,
 *   'plantoes'    => [   // array de plantões da semana
 *     [
 *       'data'        => '2025-05-19',
 *       'dia_label'   => 'SEG',
 *       'paciente'    => 'João Silva',
 *       'paciente_id' => 1,
 *       'turno'       => 'Diurno',
 *       'inicio'      => '07:00',
 *       'fim'         => '19:00',
 *       'status'      => 'ok',
 *       'endereco'    => 'Av. Brasil, 420',
 *     ],
 *     ...
 *   ],
 *   'folgas' => ['2025-05-24', '2025-05-25'],
 * ]
 * $dias — 7 dias da semana (escopo pai)
 */
$pageTitle = 'Agenda — ' . ($col['nome'] ?? '');
$hoje = date('Y-m-d');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/escalas.css">

<div class="escala-shell">

    <!-- Breadcrumb -->
    <nav style="font-size:13px;color:#9ca3af;margin-bottom:.75rem">
        <a href="<?= BASE_URL ?>/escalas" style="color:#1d4ed8;text-decoration:none">Central de Cobertura</a>
        <span style="margin:0 6px">/</span>
        <?= htmlspecialchars($col['nome']) ?>
    </nav>

    <!-- Header cuidador -->
    <div class="colaborador-header">
        <div class="colaborador-avatar" style="background:<?= $col['cor_avatar'] ?>;color:<?= $col['cor_avatar_t'] ?>">
            <?= $col['iniciais'] ?>
        </div>
        <div>
            <div class="colaborador-name"><?= htmlspecialchars($col['nome']) ?></div>
            <div class="colaborador-sub">
                <?= count($col['plantoes']) ?> plantão(ões) esta semana
                · <?= $col['horas_semana'] ?>h previstas
            </div>
        </div>
        <div class="colaborador-stats">
            <div class="stat-val"><?= $col['horas_semana'] ?>h</div>
            <div class="stat-lbl">Horas na semana</div>
        </div>
    </div>

    <!-- Grade: linha por dia, coluna com info do paciente -->
    <div class="paciente-card" style="margin-top:1rem">
        <div class="grade-wrap">
            <table class="grade-table">
                <thead>
                    <tr>
                        <th class="col-turno">Dia</th>
                        <th style="text-align:left">Paciente</th>
                        <th style="text-align:left">Turno / Horário</th>
                        <th style="text-align:left">Endereço</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dias as $d):
                    // Filtra plantões do dia
                    $pts_dia = array_filter($col['plantoes'], fn($p) => $p['data'] === $d['date']);
                    $is_folga = in_array($d['date'], $col['folgas'] ?? []);
                    $is_hoje  = $d['date'] === $hoje;
                ?>
                    <?php if ($is_folga): ?>
                    <tr>
                        <td class="turno-label" style="font-weight:600;color:<?= $is_hoje ? '#1d4ed8' : '#374151' ?>">
                            <?= $d['label'] ?> <span
                                style="font-weight:400;color:#9ca3af;font-size:11px"><?= $d['num'] ?></span>
                        </td>
                        <td colspan="4">
                            <div class="plantao-cell plantao-cell--na"
                                style="text-align:center;font-size:12px;color:#9ca3af">
                                <i class="ti ti-beach" aria-hidden="true"></i> Folga programada
                            </div>
                        </td>
                    </tr>
                    <?php elseif (empty($pts_dia)): ?>
                    <tr>
                        <td class="turno-label" style="color:<?= $is_hoje ? '#1d4ed8' : '' ?>">
                            <?= $d['label'] ?> <span
                                style="font-weight:400;color:#9ca3af;font-size:11px"><?= $d['num'] ?></span>
                        </td>
                        <td colspan="4">
                            <div class="plantao-cell plantao-cell--na" style="font-size:12px;color:#9ca3af">Sem plantão
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($pts_dia as $idx => $pt): ?>
                    <tr>
                        <?php if ($idx === 0): ?>
                        <td class="turno-label" rowspan="<?= count($pts_dia) ?>"
                            style="font-weight:600;vertical-align:top;padding-top:8px;color:<?= $is_hoje ? '#1d4ed8' : '' ?>">
                            <?= $d['label'] ?>
                            <span style="font-weight:400;color:#9ca3af;font-size:11px"><?= $d['num'] ?></span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <div class="plantao-cell plantao-cell--<?= $pt['status'] ?>">
                                <span class="plantao-cell__nome"><?= htmlspecialchars($pt['paciente']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="plantao-cell plantao-cell--<?= $pt['status'] ?>">
                                <span class="plantao-cell__nome"><?= htmlspecialchars($pt['turno']) ?></span>
                                <span class="plantao-cell__hora"><?= $pt['inicio'] ?> – <?= $pt['fim'] ?></span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#6b7280;padding:4px 6px">
                            <?= htmlspecialchars($pt['endereco'] ?? '—') ?>
                        </td>
                        <td style="text-align:center;font-size:14px">
                            <?php if ($pt['status'] === 'ok'): ?>
                            <span style="color:#059669">✔</span>
                            <?php elseif ($pt['status'] === 'sub'): ?>
                            <span style="color:#d97706">↺</span>
                            <?php else: ?>
                            <span style="color:#dc2626">⚠</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include __DIR__ . '/modal_criar.php'; ?>
<?php include __DIR__ . '/modal_substituicao.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/escalas.js"></script>