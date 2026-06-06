<?php
$pacienteUuid = (string)($record['uuid'] ?? $paciente['uuid'] ?? '');
$lista = $anamneses ?? [];
?>
<section class="panel pac-panel-help">
    <h2>Anamnese</h2>
    <p class="page-subtitle">Dados clínicos de base (mudam pouco no dia a dia), distintos do relatório de plantão.</p>
    <ul class="pac-help-list">
        <li>Identificação: diagnóstico principal (CID-10), secundários, motivo do cuidado domiciliar.</li>
        <li>Saúde: crônicos, cirurgias/internações, alergias, medicação de uso contínuo (lista base).</li>
        <li>Funcional: dependência, mobilidade, dieta, esfíncteres, dispositivos (O₂, sonda, etc.).</li>
        <li>Familiar: com quem reside, responsável pelos cuidados, observações.</li>
    </ul>
    <p><a class="btn btn-primary" href="<?= url('/anamneses/novo?paciente_uuid=' . rawurlencode($pacienteUuid)) ?>">Nova anamnese</a>
        <a class="btn btn-secondary" href="<?= url('/anamneses') ?>">Listagem global (admin)</a></p>
</section>

<section class="panel">
    <h2>Registros deste paciente</h2>
    <?php if ($lista === []): ?>
    <p class="empty-state">Nenhuma anamnese cadastrada para este paciente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                                        <th>Data</th>
                    <th>Patologia</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista as $row): ?>
                <tr>
                                        <td><?= e(formatDate($row['data_anamnese'] ?? '')) ?></td>
                    <td><?= e($row['patologia'] ?? '—') ?></td>
                    <td><?= e($row['status'] ?? '—') ?></td>
                    <td><a href="<?= url('/anamneses/' . rawurlencode((string)($row['uuid'] ?? $row['id'] ?? ''))) ?>">Abrir</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
