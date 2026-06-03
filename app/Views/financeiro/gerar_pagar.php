<?php
$fmtData = static function (mixed $valor): string {
    $valor = trim((string)$valor);
    if ($valor === '') {
        return '-';
    }
    $data = substr($valor, 0, 10);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $data) ? date('d/m/Y', strtotime($data)) : $valor;
};

$fmtValorInput = static fn(mixed $valor): string => number_format((float)$valor, 2, ',', '.');
$totalSugerido = array_sum(array_map(static fn(array $row): float => (float)($row['valor_sugerido'] ?? 0), $rows ?? []));
?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Gere contas a pagar somente a partir dos plantões finalizados da escala.</p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/financeiro/contas-pagar') ?>">Voltar para contas a pagar</a>
</section>

<?php include BASE_PATH . '/app/Views/financeiro/_subnav.php'; ?>

<section class="panel">
    <h2>Período de fechamento</h2>
    <form method="GET" action="<?= url('/financeiro/contas-pagar/gerar') ?>" class="fin-paygen-filter">
        <label>
            Início
            <input type="date" name="data_inicio" value="<?= e($dataInicio) ?>">
        </label>
        <label>
            Fim
            <input type="date" name="data_fim" value="<?= e($dataFim) ?>">
        </label>
        <label>
            Vencimento sugerido
            <input type="date" name="data_vencimento" value="<?= e($dataVencimento) ?>">
        </label>
        <button type="submit" class="btn btn-primary">Buscar plantões</button>
    </form>

    <div class="fin-paygen-summary">
        <span class="fin-paygen-pill">Plantões disponíveis: <?= count($rows ?? []) ?></span>
        <span class="fin-paygen-pill">Total sugerido: <?= function_exists('formatMoney') ? formatMoney($totalSugerido) : 'R$ ' . number_format($totalSugerido, 2, ',', '.') ?></span>
        <span class="fin-paygen-pill">Período: <?= e($fmtData($dataInicio)) ?> até <?= e($fmtData($dataFim)) ?></span>
    </div>
</section>

<section class="panel">
    <?php if (empty($rows)): ?>
        <p class="empty-state">Nenhum plantão finalizado sem conta a pagar para o período informado.</p>
        <p class="page-subtitle">Dica: primeiro feche/finalize o período da escala. Depois volte aqui para gerar o financeiro dos cuidadores.</p>
    <?php else: ?>
        <form method="POST" action="<?= url('/financeiro/contas-pagar/gerar') ?>" onsubmit="return confirm('Gerar contas a pagar dos plantões selecionados?');">
            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
            <input type="hidden" name="data_inicio" value="<?= e($dataInicio) ?>">
            <input type="hidden" name="data_fim" value="<?= e($dataFim) ?>">

            <div class="fin-paygen-options">
                <label>
                    Vencimento das contas
                    <input type="date" name="data_vencimento" value="<?= e($dataVencimento) ?>" required>
                </label>
                <label>
                    Observação do fechamento
                    <textarea name="observacao_fechamento" rows="2" placeholder="Ex.: Fechamento de junho/2026, valores conferidos manualmente."></textarea>
                </label>
                <button type="submit" class="btn btn-primary">Gerar contas a pagar</button>
            </div>

            <div class="table-wrap">
                <table class="data-table fin-pay-table">
                    <thead>
                        <tr>
                            <th>Gerar</th>
                            <th>Cuidador</th>
                            <th>Paciente</th>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Tipo</th>
                            <th>Regra</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php $oid = (int)($row['ocorrencia_id'] ?? 0); ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="ocorrencias[]" value="<?= $oid ?>" checked>
                                </td>
                                <td><strong><?= e($row['cuidador_nome'] ?? '-') ?></strong></td>
                                <td><?= e($row['paciente_nome'] ?? '-') ?></td>
                                <td><?= e($row['data_exibicao'] ?? $fmtData($row['data_plantao'] ?? '')) ?></td>
                                <td><?= e($row['horario_exibicao'] ?? '-') ?></td>
                                <td>
                                    <span class="fin-pay-status"><?= e($row['tipo_plantao'] ?? '-') ?></span>
                                    <span class="fin-pay-rule"><?= e($row['periodo_calculado'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <?= e($row['regra_titulo'] ?? '-') ?>
                                    <span class="fin-pay-rule">Sugestão: <?= e($row['valor_sugerido_formatado'] ?? '-') ?></span>
                                </td>
                                <td>
                                    <input class="fin-pay-value" type="text" name="valores[<?= $oid ?>]" value="<?= e($fmtValorInput($row['valor_sugerido'] ?? 0)) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php endif; ?>
</section>
