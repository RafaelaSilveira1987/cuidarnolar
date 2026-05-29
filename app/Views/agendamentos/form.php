<?php
$record = isset($record) && is_array($record) ? $record : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$options = isset($options) && is_array($options) ? $options : [];
$isEdit = !empty($isEdit);

function agenda_form_value(array $record, string $key, mixed $default = ''): string
{
    $value = $record[$key] ?? $default;

    if (in_array($key, ['data_inicio', 'data_fim'], true) && is_string($value) && str_contains($value, ' ')) {
        return str_replace(' ', 'T', substr($value, 0, 16));
    }

    return (string)$value;
}
?>

<section class="agenda-page-header">
    <div>
        <span class="agenda-eyebrow">Agenda</span>
        <h1><?= e($title ?? 'Compromisso') ?></h1>
        <p class="page-subtitle">Registre visitas, entrevistas, reuniões e demandas da operação.</p>
    </div>

    <a class="btn btn-secondary" href="<?= url('/agendamentos') ?>">Voltar</a>
</section>

<section class="panel agenda-form-panel">
    <form class="form-grid" method="POST" action="<?= url($action) ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">

        <label>
            Tipo do compromisso
            <select name="tipo_evento">
                <?php foreach (($options['tipo_evento'] ?? []) as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= agenda_form_value($record, 'tipo_evento', 'Outro') === (string)$value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['tipo_evento'])): ?><small class="field-error"><?= e($errors['tipo_evento']) ?></small><?php endif; ?>
        </label>

        <label>
            Status
            <select name="status">
                <?php foreach (($options['status'] ?? []) as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= agenda_form_value($record, 'status', 'Pendente') === (string)$value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="span-2">
            Título
            <input type="text" name="titulo" maxlength="255" value="<?= e(agenda_form_value($record, 'titulo')) ?>" placeholder="Ex: Visita domiciliar - avaliação inicial">
            <?php if (!empty($errors['titulo'])): ?><small class="field-error"><?= e($errors['titulo']) ?></small><?php endif; ?>
        </label>

        <label>
            Data e hora inicial
            <input type="datetime-local" name="data_inicio" value="<?= e(agenda_form_value($record, 'data_inicio')) ?>">
            <?php if (!empty($errors['data_inicio'])): ?><small class="field-error"><?= e($errors['data_inicio']) ?></small><?php endif; ?>
        </label>

        <label>
            Data e hora final
            <input type="datetime-local" name="data_fim" value="<?= e(agenda_form_value($record, 'data_fim')) ?>">
            <?php if (!empty($errors['data_fim'])): ?><small class="field-error"><?= e($errors['data_fim']) ?></small><?php endif; ?>
        </label>

        <label>
            Paciente
            <select name="paciente_id">
                <option value="">Sem paciente vinculado</option>
                <?php foreach (($options['paciente_id'] ?? []) as $paciente): ?>
                    <option value="<?= e($paciente['id'] ?? '') ?>" <?= (string)agenda_form_value($record, 'paciente_id') === (string)($paciente['id'] ?? '') ? 'selected' : '' ?>>
                        <?= e($paciente['nome_completo'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Cuidador / profissional
            <select name="cuidador_id">
                <option value="">Sem cuidador vinculado</option>
                <?php foreach (($options['cuidador_id'] ?? []) as $cuidador): ?>
                    <option value="<?= e($cuidador['id'] ?? '') ?>" <?= (string)agenda_form_value($record, 'cuidador_id') === (string)($cuidador['id'] ?? '') ? 'selected' : '' ?>>
                        <?= e($cuidador['nome_completo'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Prioridade
            <select name="prioridade">
                <?php foreach (($options['prioridade'] ?? []) as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= agenda_form_value($record, 'prioridade', 'Normal') === (string)$value ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Local
            <input type="text" name="local" value="<?= e(agenda_form_value($record, 'local')) ?>" placeholder="Domicílio, hospital, escritório...">
        </label>

        <label class="span-2">
            Descrição / observações
            <textarea name="descricao" rows="5" placeholder="Detalhes do compromisso, orientação, contato ou demanda."><?= e(agenda_form_value($record, 'descricao')) ?></textarea>
        </label>

        <div class="form-actions span-2">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar compromisso' ?></button>
            <a class="btn btn-secondary" href="<?= url('/agendamentos') ?>">Cancelar</a>
        </div>
    </form>
</section>
