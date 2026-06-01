<?php
$paciente = $paciente ?? [];
$medicacoes = $medicacoes ?? [];
$pacienteUuid = (string)($paciente['uuid'] ?? '');
?>

<section class="page-header">
    <div>
        <h1>Medicações</h1>
        <p class="page-subtitle">Paciente: <strong><?= e($paciente['nome_completo'] ?? 'Paciente') ?></strong></p>
    </div>
    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode($pacienteUuid) . '?aba=medicacoes') ?>">Voltar ao paciente</a>
        <a class="btn btn-primary" href="<?= url('/pacientes/' . rawurlencode($pacienteUuid) . '/medicacoes/novo') ?>">Nova medicação</a>
    </div>
</section>

<section class="panel">
    <?php if (empty($medicacoes)): ?>
        <p class="page-subtitle">Nenhuma medicação cadastrada para este paciente.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Dosagem</th>
                        <th>Via</th>
                        <th>Horários</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicacoes as $medicacao): ?>
                        <tr>
                            <td><?= e($medicacao['nome_medicamento'] ?? '') ?></td>
                            <td><?= e($medicacao['dosagem'] ?? '') ?></td>
                            <td><?= e($medicacao['via'] ?? '') ?></td>
                            <td><?= e($medicacao['horarios'] ?? '') ?></td>
                            <td><?= e($medicacao['status'] ?? '') ?></td>
                            <td>
                                <div class="button-row">
                                    <a class="btn btn-secondary btn-sm" href="<?= url('/medicacoes/' . (int)($medicacao['id'] ?? 0) . '/editar') ?>">Editar</a>
                                    <form method="POST" action="<?= url('/medicacoes/' . (int)($medicacao['id'] ?? 0) . '/inativar') ?>" onsubmit="return confirm('Inativar esta medicação?')">
                                        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                                        <button type="submit" class="btn btn-secondary btn-sm">Inativar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
