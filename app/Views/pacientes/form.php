<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Cadastro base usado por anamnese, diário, agenda e financeiro.</p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/pacientes') ?>">Voltar</a>
</section>

<section class="panel">
    <form class="form-grid" method="POST" action="<?= url($action) ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">

        <label class="span-2">
            Nome completo
            <input type="text" name="nome_completo" value="<?= e($paciente['nome_completo'] ?? '') ?>" maxlength="100"
                required>
            <?php if (!empty($errors['nome_completo'])): ?>
            <small class="field-error"><?= e($errors['nome_completo']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Data de nascimento
            <input type="date" name="data_nascimento" value="<?= e($paciente['data_nascimento'] ?? '') ?>">
            <?php if (!empty($errors['data_nascimento'])): ?>
            <small class="field-error"><?= e($errors['data_nascimento']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            CPF
            <input type="text" name="cpf" value="<?= e($paciente['cpf'] ?? '') ?>" maxlength="14">
        </label>

        <label>
            RG
            <input type="text" name="rg" value="<?= e($paciente['rg'] ?? '') ?>">
        </label>

        <label>
            Cartão SUS
            <input type="text" name="cartao_nac_sus" value="<?= e($paciente['cartao_nac_sus'] ?? '') ?>">
        </label>

        <label>
            Plano de saúde
            <input type="text" name="plano_saude" value="<?= e($paciente['plano_saude'] ?? '') ?>">
        </label>

        <label>
            Responsável
            <select name="responsavel_id">
                <option value="">Sem vínculo</option>
                <?php foreach ($responsaveis as $responsavel): ?>
                <option value="<?= (int) $responsavel['id'] ?>"
                    <?= (string) ($paciente['responsavel_id'] ?? '') === (string) $responsavel['id'] ? 'selected' : '' ?>>
                    <?= e($responsavel['nome_completo']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Cuidador
            <select name="cuidador_id">
                <option value="">Sem vínculo</option>
                <?php foreach ($cuidadores as $cuidador): ?>
                <option value="<?= (int) $cuidador['id'] ?>"
                    <?= (string) ($paciente['cuidador_id'] ?? '') === (string) $cuidador['id'] ? 'selected' : '' ?>>
                    <?= e($cuidador['nome_completo']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Status
            <select name="status">
                <option value="Ativo" <?= ($paciente['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo
                </option>
                <option value="Inativo" <?= ($paciente['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo
                </option>
            </select>
            <?php if (!empty($errors['status'])): ?>
            <small class="field-error"><?= e($errors['status']) ?></small>
            <?php endif; ?>
        </label>

        <label class="span-2">
            Motivo de inativacao
            <textarea name="motivo_inativacao" rows="3"><?= e($paciente['motivo_inativacao'] ?? '') ?></textarea>
        </label>

        <div class="form-actions span-2">
            <button class="btn btn-primary"
                type="submit"><?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar paciente' ?></button>
            <a class="btn btn-secondary" href="<?= url('/pacientes') ?>">Cancelar</a>
        </div>
    </form>
</section>