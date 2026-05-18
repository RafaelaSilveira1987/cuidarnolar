<h1>Relatório de Plantão</h1>

<p>
    Paciente:
    <strong><?= htmlspecialchars($pacienteSelecionado['nome_completo'] ?? 'Selecionar no formulário') ?></strong>
</p>

<p>
    Responsável pelo registro:
    <?= htmlspecialchars($_user['nome'] ?? '') ?>
</p>

<?php require __DIR__ . '/form.php'; ?>