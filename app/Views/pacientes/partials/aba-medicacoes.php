<?php
$pid = (int)($record['id'] ?? 0);
$lista = $medicacoes ?? [];
?>

<section class="panel">
    <div class="panel-header med-tab-header">
        <div>
            <h2>Medicamentos de uso contínuo</h2>
            <p class="page-subtitle">
                Lista base usada no relatório de plantão para checagem de administração.
            </p>
        </div>
        <a class="btn btn-primary" href="<?= url('/pacientes/' . $pid . '/editar') ?>">
            Editar lista
        </a>
    </div>

    <?php if ($lista === []): ?>
        <p class="empty-state">Nenhuma medicação contínua cadastrada para este paciente.</p>
    <?php else: ?>
        <ol class="med-enumerada">
            <?php foreach ($lista as $med): ?>
                <li>
                    <div class="med-enumerada__main">
                        <strong><?= e($med['nome_medicamento'] ?? '') ?></strong>
                        <?php if (!empty($med['status']) && $med['status'] !== 'Ativo'): ?>
                            <span class="badge bg-secondary"><?= e($med['status']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="med-enumerada__meta">
                        <?php if (!empty($med['dosagem'])): ?>
                            <span>Dosagem: <?= e($med['dosagem']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($med['horarios'])): ?>
                            <span>Horários: <?= e($med['horarios']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($med['via'])): ?>
                            <span>Via: <?= e($med['via']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($med['frequencia'])): ?>
                            <span>Frequência: <?= e($med['frequencia']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($med['observacoes'])): ?>
                        <p><?= e($med['observacoes']) ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</section>
