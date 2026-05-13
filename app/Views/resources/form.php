<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Preencha os campos do cadastro.</p>
    </div>
    <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
</section>

<section class="panel">
    <form class="form-grid" method="POST" action="<?= url($action) ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">

        <?php foreach ($fields as $name => $field): ?>
            <?php
                $type = $field['type'] ?? 'text';
                $label = $field['label'] ?? $name;
                $span = !empty($field['span']) ? ' span-2' : '';
                $value = $record[$name] ?? ($field['default'] ?? '');
                if (($field['type'] ?? '') === 'datetime-local' && is_string($value) && str_contains($value, ' ')) {
                    $value = str_replace(' ', 'T', substr($value, 0, 16));
                }
            ?>
            <label class="<?= trim($span) ?>">
                <?= e($label) ?>

                <?php if ($type === 'select'): ?>
                    <select name="<?= e($name) ?>">
                        <?php if (!empty($field['empty'])): ?>
                            <option value=""><?= e($field['empty']) ?></option>
                        <?php endif; ?>
                        <?php foreach (($field['options'] ?? $options[$name] ?? []) as $optionValue => $optionLabel): ?>
                            <?php
                                if (is_array($optionLabel)) {
                                    $optionValue = $optionLabel['id'];
                                    $optionLabel = $optionLabel['nome_completo'] ?? $optionLabel['titulo'] ?? $optionValue;
                                }
                            ?>
                            <option value="<?= e($optionValue) ?>" <?= (string) $value === (string) $optionValue ? 'selected' : '' ?>>
                                <?= e($optionLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($type === 'textarea'): ?>
                    <textarea name="<?= e($name) ?>" rows="<?= (int) ($field['rows'] ?? 3) ?>"><?= e($value) ?></textarea>
                <?php else: ?>
                    <input type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($value) ?>" <?= !empty($field['maxlength']) ? 'maxlength="' . (int) $field['maxlength'] . '"' : '' ?>>
                <?php endif; ?>

                <?php if (!empty($errors[$name])): ?>
                    <small class="field-error"><?= e($errors[$name]) ?></small>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <div class="form-actions span-2">
            <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar' ?></button>
            <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Cancelar</a>
        </div>
    </form>
</section>
