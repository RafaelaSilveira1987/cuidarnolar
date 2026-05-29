<?php
/**
 * app/Views/pacientes/medicacoes/form.php
 */

$paciente = isset($paciente) && is_array($paciente)
    ? $paciente
    : [];

$medicacao = isset($medicacao) && is_array($medicacao)
    ? $medicacao
    : [];

$errors = isset($errors) && is_array($errors)
    ? $errors
    : [];

$options = isset($options) && is_array($options)
    ? $options
    : [];

$vias = $options['vias'] ?? [
    'VO' => 'VO',
    'EV' => 'EV',
    'IM' => 'IM',
    'SC' => 'SC',
    'SL' => 'SL',
    'INALATORIA' => 'Inalatória',
    'TOPICA' => 'Tópica',
    'OUTRA' => 'Outra',
];

$statusOptions = $options['status'] ?? [
    'Ativo' => 'Ativo',
    'Suspenso' => 'Suspenso',
    'Finalizado' => 'Finalizado',
];

$isEdit = !empty($isEdit);

$action = (string)($action ?? '');

$pacienteUuid = (string)($paciente['uuid'] ?? '');
$pacienteNome = (string)($paciente['nome_completo'] ?? 'Paciente');

function med_value(array $data, string $key, string $default = ''): string
{
    return htmlspecialchars((string)($data[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

function med_selected(array $data, string $key, string $value): string
{
    return ((string)($data[$key] ?? '') === $value) ? 'selected' : '';
}
?>

<section class="page-header">
    <div>
        <h1><?= e($title ?? ($isEdit ? 'Editar medicação' : 'Nova medicação')) ?></h1>
        <p class="page-subtitle">
            Paciente: <strong><?= e($pacienteNome) ?></strong>
            <?php if (!empty($paciente['prontuario'])): ?>
            — Prontuário: <?= e($paciente['prontuario']) ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if ($pacienteUuid !== ''): ?>
    <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode($pacienteUuid) . '?aba=medicacoes') ?>">
        Voltar
    </a>
    <?php endif; ?>
</section>

<section class="panel">

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Corrija os campos abaixo:</strong>

        <ul>
            <?php foreach ($errors as $error): ?>
            <li><?= e((string)$error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url($action) ?>" class="form-grid">

        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="form-group">
            <label for="nome_medicamento">Nome do medicamento *</label>
            <input type="text" id="nome_medicamento" name="nome_medicamento"
                value="<?= med_value($medicacao, 'nome_medicamento') ?>" required>
        </div>

        <div class="form-group">
            <label for="dosagem">Dosagem</label>
            <input type="text" id="dosagem" name="dosagem" value="<?= med_value($medicacao, 'dosagem') ?>"
                placeholder="Ex: 20mg, 5ml, 10 gotas">
        </div>

        <div class="form-group">
            <label for="horarios">Horários</label>
            <input type="text" id="horarios" name="horarios" value="<?= med_value($medicacao, 'horarios') ?>"
                placeholder="Ex: 08:00 / 14:00 / 20:00">
        </div>

        <div class="form-group">
            <label for="frequencia">Frequência</label>
            <input type="text" id="frequencia" name="frequencia" value="<?= med_value($medicacao, 'frequencia') ?>"
                placeholder="Ex: 8/8h, 12/12h, 1x ao dia">
        </div>

        <div class="form-group">
            <label for="via">Via</label>
            <select id="via" name="via">
                <option value="">Selecione</option>

                <?php foreach ($vias as $value => $label): ?>
                <option value="<?= e((string)$value) ?>" <?= med_selected($medicacao, 'via', (string)$value) ?>>
                    <?= e((string)$label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php foreach ($statusOptions as $value => $label): ?>
                <option value="<?= e((string)$value) ?>" <?= med_selected($medicacao, 'status', (string)$value) ?>>
                    <?= e((string)$label) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group full">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" name="observacoes" rows="4"
                placeholder="Ex: administrar após alimentação, observar sinais de reação, etc."><?= med_value($medicacao, 'observacoes') ?></textarea>
        </div>

        <div class="form-actions full">
            <?php if ($pacienteUuid !== ''): ?>
            <a class="btn btn-secondary"
                href="<?= url('/pacientes/' . rawurlencode($pacienteUuid) . '?aba=medicacoes') ?>">
                Cancelar
            </a>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Salvar alterações' : 'Cadastrar medicação' ?>
            </button>
        </div>

    </form>
</section>