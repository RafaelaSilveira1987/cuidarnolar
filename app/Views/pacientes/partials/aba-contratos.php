<?php
$resourceKey = (string)($paciente['uuid'] ?? ($paciente['id'] ?? $currentKey ?? ''));
$contratosPaciente = $contratosPaciente ?? [];
$contratoAtivo = $contratoAtivo ?? [];
$resumoFinanceiro = $contratoFinanceiroResumo ?? [];
$hoje = new DateTimeImmutable('today');
$periodoInicio = $hoje->modify('first day of this month')->format('Y-m-d');
$periodoFim = $hoje->modify('last day of this month')->format('Y-m-d');
$formatMoneyContrato = static function (mixed $valor): string {
    if (function_exists('formatMoney')) {
        return formatMoney((float)$valor);
    }
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
};
$fmtDateLocal = $fmtDate ?? static function (?string $date, string $fallback = '—'): string {
    if (!$date) {
        return $fallback;
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $fallback;
};
?>

<section class="panel contrato-paciente-panel">
    <div class="panel-header contrato-paciente-head">
        <div>
            <h2>Contrato do paciente</h2>
            <p class="page-subtitle">
                Aqui ficam vigência, assistência contratada, valores, vencimento e geração do financeiro.
                Cadastro de paciente de um lado, dinheiro do outro. Cada macaco no seu galho — funciona melhor assim.
            </p>
        </div>

        <a class="btn btn-primary" href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/contratos/novo') ?>">
            Novo contrato
        </a>
    </div>

    <?php if (!empty($contratoAtivo)): ?>
        <div class="contrato-active-card">
            <div class="contrato-active-main">
                <span class="ce-label">Contrato ativo</span>
                <h3><?= e($contratoAtivo['tipo_servico'] ?? 'Contrato home care') ?></h3>

                <div class="contrato-badges">
                    <span><?= e($contratoAtivo['escala_contratada'] ?? 'Escala não informada') ?></span>
                    <span><?= e($contratoAtivo['tipo_prazo'] ?? 'Indeterminado') ?></span>
                    <span><?= e($contratoAtivo['forma_pagamento'] ?? 'Pagamento não informado') ?></span>
                    <span>Vence dia <?= e((string)($contratoAtivo['dia_vencimento'] ?? '10')) ?></span>
                </div>

                <dl class="contrato-mini-grid">
                    <div>
                        <dt>Início</dt>
                        <dd><?= e($fmtDateLocal($contratoAtivo['vigencia_inicio'] ?? null)) ?></dd>
                    </div>
                    <div>
                        <dt>Término previsto</dt>
                        <dd><?= e($fmtDateLocal($contratoAtivo['vigencia_fim'] ?? null, 'Indeterminado')) ?></dd>
                    </div>
                    <div>
                        <dt>Valor da cobrança</dt>
                        <dd><?= e($contratoAtivo['valor_cobranca_fmt'] ?? $formatMoneyContrato($contratoAtivo['valor_mensal'] ?? 0)) ?></dd>
                    </div>
                    <div>
                        <dt>Responsável financeiro</dt>
                        <dd><?= e($contratoAtivo['responsavel_financeiro_nome'] ?? $contratoAtivo['responsavel_legal_nome'] ?? 'Não informado') ?></dd>
                    </div>
                </dl>

                <?php if (!empty($contratoAtivo['servicos_lista'])): ?>
                    <div class="contrato-service-list">
                        <?php foreach ($contratoAtivo['servicos_lista'] as $servico): ?>
                            <span><?= e($servico) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="contrato-finance-card">
                <span class="ce-label">Financeiro gerado</span>
                <strong><?= e((string)($resumoFinanceiro['total_lancamentos'] ?? 0)) ?> lançamento(s)</strong>
                <small>
                    Pendente: <?= e($formatMoneyContrato($resumoFinanceiro['total_pendente'] ?? 0)) ?> ·
                    Pago: <?= e($formatMoneyContrato($resumoFinanceiro['total_pago'] ?? 0)) ?>
                </small>

                <form method="POST"
                      action="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/contratos/' . rawurlencode((string)($contratoAtivo['uuid'] ?? $contratoAtivo['id'])) . '/gerar-financeiro') ?>"
                      class="contrato-generate-form"
                      onsubmit="return confirm('Gerar financeiro deste contrato no período informado? Lançamentos já existentes do mesmo mês serão ignorados.');">
                    <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                    <label>
                        Início
                        <input type="date" name="periodo_inicio" value="<?= e($periodoInicio) ?>">
                    </label>
                    <label>
                        Fim
                        <input type="date" name="periodo_fim" value="<?= e($periodoFim) ?>">
                    </label>
                    <button type="submit" class="btn btn-primary">Gerar financeiro</button>
                </form>
            </aside>
        </div>
    <?php else: ?>
        <div class="empty-state contrato-empty">
            <strong>Nenhum contrato ativo cadastrado.</strong>
            <p>Cadastre o contrato primeiro; depois gere o financeiro por período. Sem contrato, o financeiro vira chute. E chute é bom só no futebol.</p>
            <a class="btn btn-primary" href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/contratos/novo') ?>">Cadastrar contrato</a>
        </div>
    <?php endif; ?>
</section>

<section class="panel contrato-history-panel">
    <div class="panel-header">
        <div>
            <h2>Histórico de contratos</h2>
            <p class="page-subtitle">Mantém o rastro operacional: contrato ativo, suspenso, encerrado ou cancelado.</p>
        </div>
    </div>

    <?php if (!empty($contratosPaciente)): ?>
        <div class="table-responsive">
            <table class="data-table contrato-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Serviço</th>
                        <th>Escala</th>
                        <th>Vigência</th>
                        <th>Valor cobrança</th>
                        <th>Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contratosPaciente as $contrato): ?>
                        <tr>
                            <td><span class="status-badge status-<?= e(strtolower((string)($contrato['status'] ?? 'ativo'))) ?>"><?= e($contrato['status'] ?? 'Ativo') ?></span></td>
                            <td><?= e($contrato['tipo_servico'] ?? '—') ?></td>
                            <td><?= e($contrato['escala_contratada'] ?? '—') ?></td>
                            <td>
                                <?= e($fmtDateLocal($contrato['vigencia_inicio'] ?? null)) ?>
                                até
                                <?= e($fmtDateLocal($contrato['vigencia_fim'] ?? null, 'indeterminado')) ?>
                            </td>
                            <td><?= e($contrato['valor_cobranca_fmt'] ?? $formatMoneyContrato($contrato['valor_mensal'] ?? 0)) ?></td>
                            <td>Dia <?= e((string)($contrato['dia_vencimento'] ?? '10')) ?></td>
                            <td>
                                <a class="btn btn-secondary btn-sm" href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/contratos/' . rawurlencode((string)($contrato['uuid'] ?? $contrato['id'])) . '/editar') ?>">
                                    Editar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="empty-state">Ainda não existe histórico de contratos para este paciente.</p>
    <?php endif; ?>
</section>
