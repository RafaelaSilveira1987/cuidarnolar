<section class="page-header finance-receive-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Informe somente os dados da baixa. Os dados do contrato e da cobrança ficam preservados.</p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/financeiro/contas-receber') ?>">Voltar</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel finance-receive-page">
    <?php if (!empty($errors['geral'])): ?>
        <div class="alert alert-danger"><?= e($errors['geral']) ?></div>
    <?php endif; ?>

    <div class="finance-readonly-card">
        <div class="finance-readonly-card__title">
            <span>Dados da cobrança</span>
            <strong><?= e($record['valor_formatado'] ?? formatMoney((float)($record['valor'] ?? 0))) ?></strong>
        </div>

        <div class="finance-readonly-grid">
            <div class="finance-info-item">
                <span>Paciente</span>
                <strong><?= e($record['paciente_nome'] ?? '-') ?></strong>
            </div>
            <div class="finance-info-item">
                <span>Responsável</span>
                <strong><?= e($record['responsavel_nome'] ?? '-') ?></strong>
            </div>
            <div class="finance-info-item finance-info-item--wide">
                <span>Descrição</span>
                <strong><?= e($record['descricao'] ?? $record['observacoes'] ?? '-') ?></strong>
            </div>
            <div class="finance-info-item">
                <span>Vencimento</span>
                <strong><?= e($record['vencimento_exibicao'] ?? '-') ?></strong>
            </div>
            <div class="finance-info-item">
                <span>Mês referência</span>
                <strong><?= e($record['mes_referencia'] ?? '-') ?></strong>
            </div>
            <div class="finance-info-item">
                <span>Status atual</span>
                <strong><?= e($record['status'] ?? '-') ?></strong>
            </div>
        </div>
    </div>

    <form method="POST" action="<?= url('/financeiro/' . (int)($record['id'] ?? 0) . '/receber') ?>" class="resource-form finance-receive-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="form-section-title">Dados do recebimento</div>

        <div class="form-grid finance-receive-grid">
            <label>
                Data do recebimento
                <input type="date" name="data_pagamento" value="<?= e($old['data_pagamento'] ?? date('Y-m-d')) ?>" required>
                <?php if (!empty($errors['data_pagamento'])): ?><small class="field-error"><?= e($errors['data_pagamento']) ?></small><?php endif; ?>
            </label>

            <label>
                Forma de pagamento
                <select name="moeda" required>
                    <option value="">Selecione</option>
                    <?php foreach (($formasPagamento ?? []) as $valor => $label): ?>
                        <option value="<?= e($valor) ?>" <?= (($old['moeda'] ?? ($record['moeda'] ?? '')) === $valor) ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['moeda'])): ?><small class="field-error"><?= e($errors['moeda']) ?></small><?php endif; ?>
            </label>

            <label>
                Valor recebido
                <input type="text" name="valor_recebido" value="<?= e($old['valor_recebido'] ?? number_format((float)($record['valor'] ?? 0), 2, ',', '.')) ?>" required>
                <?php if (!empty($errors['valor_recebido'])): ?><small class="field-error"><?= e($errors['valor_recebido']) ?></small><?php endif; ?>
            </label>

            <label class="span-3">
                Observação da baixa
                <textarea name="observacao_baixa" rows="4" placeholder="Ex.: Recebido via PIX. Comprovante enviado pelo responsável."><?= e($old['observacao_baixa'] ?? '') ?></textarea>
            </label>
        </div>

        <div class="form-actions finance-receive-actions">
            <a class="btn btn-secondary" href="<?= url('/financeiro/contas-receber') ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit">Confirmar recebimento</button>
        </div>
    </form>
</section>
