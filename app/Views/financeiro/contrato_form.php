<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Vincule o plano de atendimento ao paciente.</p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/financeiro/contratos') ?>">Voltar</a>
</section>

<section class="panel">
    <form class="form-grid" method="POST" action="<?= url('/financeiro/contratos') ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error span-2">
            <?php foreach ($errors as $msg): ?>
            <div><?= e($msg) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <label>
            Paciente *
            <select name="paciente_id" required>
                <option value="">Selecione</option>
                <?php foreach (($options['paciente_id'] ?? []) as $opt): ?>
                <option value="<?= (int) ($opt['id'] ?? 0) ?>" <?= (string) ($record['paciente_id'] ?? '') === (string) ($opt['id'] ?? '') ? 'selected' : '' ?>>
                    <?= e($opt['nome_completo'] ?? '') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Tipo de serviço *
            <input type="text" name="tipo_servico" value="<?= e($record['tipo_servico'] ?? '') ?>"
                placeholder="Ex.: Cuidador 24h, Técnico de enfermagem" required>
        </label>

        <label>
            Valor mensal (R$) *
            <input type="number" step="0.01" min="0" name="valor_mensal" value="<?= e((string) ($record['valor_mensal'] ?? '')) ?>" required>
        </label>

        <label>
            Dia de vencimento (1–31) *
            <input type="number" min="1" max="31" name="dia_vencimento" value="<?= e((string) ($record['dia_vencimento'] ?? '10')) ?>" required>
        </label>

        <label>
            Forma de pagamento
            <select name="forma_pagamento">
                <option value="">—</option>
                <option value="PIX" <?= ($record['forma_pagamento'] ?? '') === 'PIX' ? 'selected' : '' ?>>PIX</option>
                <option value="Boleto" <?= ($record['forma_pagamento'] ?? '') === 'Boleto' ? 'selected' : '' ?>>Boleto</option>
                <option value="Transferência" <?= ($record['forma_pagamento'] ?? '') === 'Transferência' ? 'selected' : '' ?>>Transferência</option>
            </select>
        </label>

        <label>
            Início da vigência *
            <input type="date" name="vigencia_inicio" value="<?= e($record['vigencia_inicio'] ?? '') ?>" required>
        </label>

        <label>
            Fim da vigência (opcional)
            <input type="date" name="vigencia_fim" value="<?= e($record['vigencia_fim'] ?? '') ?>">
        </label>

        <label>
            Status
            <select name="status">
                <option value="Ativo" <?= ($record['status'] ?? 'Ativo') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="Encerrado" <?= ($record['status'] ?? '') === 'Encerrado' ? 'selected' : '' ?>>Encerrado</option>
            </select>
        </label>

        <label class="span-2">
            Observações
            <textarea name="observacoes" rows="3"><?= e($record['observacoes'] ?? '') ?></textarea>
        </label>

        <div class="span-2">
            <button class="btn btn-primary" type="submit">Salvar contrato</button>
        </div>
    </form>
</section>
