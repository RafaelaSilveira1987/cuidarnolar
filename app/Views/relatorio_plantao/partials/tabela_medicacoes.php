<?php
/** @var array $turnoData */
$meds = $turnoData['medicacoes'] ?? [];
?>
<section class="rp-secao" aria-labelledby="rp-med-title">
    <h3 id="rp-med-title" class="rp-secao__titulo">Medicações</h3>
    <div class="rp-table-wrap">
        <table class="rp-table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Via</th>
                    <th>Horário previsto</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meds as $m): ?>
                <tr>
                    <td><?= e($m['nome'] ?? '') ?></td>
                    <td><?= e($m['via'] ?? '') ?></td>
                    <td><?= e($m['horario'] ?? '') ?></td>
                    <td>
                        <?php if (($m['status'] ?? '') === 'administrado'): ?>
                        <span class="rp-med-status rp-med-status--ok"><span aria-hidden="true">✓</span> Administrado</span>
                        <?php else: ?>
                        <span class="rp-med-status rp-med-status--pend"><span aria-hidden="true">🕐</span> Pendente</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
