<?php

/**
 * Views/escalas/partials/bloco_plantao.php
 *
 * Renderiza a tabela SEG→DOM para um paciente.
 * Usa: $pac['turnos'] e $dias (do escopo pai)
 *
 * Status possíveis de cada plantão:
 *   'ok'   — coberto (verde)
 *   'vago' — sem cuidador (vermelho)
 *   'sub'  — substituição ativa (amarelo)
 *   'na'   — não se aplica (turno fora do contrato)
 */

$hoje = date('Y-m-d');
?>

<table class="grade-table" role="grid" aria-label="Grade semanal — <?= htmlspecialchars($pac['nome']) ?>">
    <thead>
        <tr>
            <th class="col-turno" scope="col">Turno</th>
            <?php foreach ($dias as $d): ?>
            <th scope="col" data-date="<?= $d['date'] ?>" class="<?= $d['date'] === $hoje ? 'hoje' : '' ?>">
                <?= $d['label'] ?>
                <span class="dia-num"><?= $d['num'] ?></span>
            </th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($pac['turnos'] as $turno): ?>
        <tr class="turno-row">

            <!-- Label do turno -->
            <td class="turno-label">
                <i class="ti <?= htmlspecialchars($turno['icone']) ?>" aria-hidden="true"></i>
                <?= htmlspecialchars($turno['label']) ?>
            </td>

            <!-- Células de cada dia -->
            <?php foreach ($turno['plantoes'] as $p): ?>
            <?php
                    $status  = $p['status'] ?? 'na';
                    $esc_id  = $p['escala_id'] ?? '';
                    $col_id  = $p['colaborador_id'] ?? '';

                    // Ícone de status
                    $icone_status = match ($status) {
                        'ok'   => '✔',
                        'vago' => '⚠',
                        'sub'  => '↺',
                        default => '',
                    };

                    // Nome exibido
                    $nome_exibido = match ($status) {
                        'ok', 'sub' => htmlspecialchars($p['colaborador'] ?? '—'),
                        'vago'      => 'VAGO',
                        default     => '—',
                    };

                    // Tooltip para substituições
                    $title_attr = '';
                    if ($status === 'sub' && !empty($p['sub_nome'])) {
                        $title_attr = 'title="Substituindo: ' . htmlspecialchars($p['sub_nome']) . '"';
                    }
                    ?>
            <td>
                <div class="plantao-cell plantao-cell--<?= $status ?>" <?= $title_attr ?>
                    data-escala-id="<?= $esc_id ?>" data-paciente-id="<?= $pac['id'] ?>"
                    data-colaborador-id="<?= $col_id ?>" data-data-plantao="<?= htmlspecialchars($p['data']) ?>"
                    data-turno="<?= htmlspecialchars($turno['label']) ?>" data-status="<?= $status ?>"
                    <?= ($status !== 'na') ? 'tabindex="0" role="button"' : '' ?> aria-label="<?= ($status !== 'na')
                                            ? htmlspecialchars($nome_exibido . ' — ' . ($p['inicio'] ?? '') . ' às ' . ($p['fim'] ?? ''))
                                            : 'Sem plantão' ?>">

                    <?php if ($status !== 'na'): ?>
                    <span class="plantao-cell__nome">
                        <?= mb_strimwidth($nome_exibido, 0, 18, '...') ?>
                    </span>
                    <span class="plantao-cell__hora"><?= $p['inicio'] ?>–<?= $p['fim'] ?></span>
                    <span class="plantao-cell__status" aria-hidden="true"><?= $icone_status ?></span>

                    <!-- ✅ ADICIONAR ESTE BLOCO -->
                    <?php if ($esc_id): ?>
                    <div class="plantao-actions">

                        <button type="button" data-action="editar" title="Editar plantão">
                            <i class="ti ti-pencil"></i>
                        </button>

                        <button type="button" data-action="substituir" title="Solicitar substituição">
                            <i class="ti ti-refresh"></i>
                        </button>

                        <button type="button" data-action="excluir" data-escala-id="<?= $esc_id ?>"
                            title="Excluir plantão">
                            <i class="ti ti-trash"></i>
                        </button>

                    </div>
                    <?php endif; ?>


                    <?php else: ?>
                    <span class="plantao-cell__nome">—</span>
                    <?php endif; ?>

                </div>
            </td>
            <?php endforeach; ?>

        </tr>
        <?php endforeach; ?>
    </tbody>
</table>