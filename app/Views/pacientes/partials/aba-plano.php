<?php
$paciente = isset($paciente) && is_array($paciente) ? $paciente : ($record ?? []);
$resourceKey = (string)($paciente['uuid'] ?? $paciente['id'] ?? '');
$planoAtivo = isset($planoCuidadoAtivo) && is_array($planoCuidadoAtivo) ? $planoCuidadoAtivo : [];
$historicoPlanos = isset($planosCuidadoHistorico) && is_array($planosCuidadoHistorico) ? $planosCuidadoHistorico : [];
$modelosPlano = isset($planosCuidadoModelos) && is_array($planosCuidadoModelos) ? $planosCuidadoModelos : [];

$fmtDataPlano = static function (?string $data): string {
    if (!$data) {
        return '—';
    }
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '—';
};

$planoTexto = static function (mixed $texto): string {
    return trim((string)$texto);
};

$secoesPlano = [
    'objetivos' => 'Objetivos do cuidado',
    'monitoramento' => 'Monitoramento',
    'oxigenoterapia' => 'Oxigenoterapia',
    'nebulizacao' => 'Nebulização',
    'controle_ambiental' => 'Controle ambiental',
    'alimentacao_hidratacao' => 'Via alimentar e hidratação',
    'atividade_repouso' => 'Atividade física e repouso',
    'medicamentos' => 'Medicamentos',
    'comunicacao_familia' => 'Comunicação com a família',
    'sinais_alerta' => 'Sinais de alerta',
];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/plano_cuidado.css">

<section class="panel plano-care-panel">
    <div class="panel-header plano-care-header">
        <div>
            <h2>Plano de cuidados</h2>
            <p class="page-subtitle">Plano assistencial por versões, gerado a partir da avaliação do paciente e validado
                antes de ficar ativo.</p>
        </div>

        <div class="button-row plano-care-actions">
            <a class="btn btn-secondary"
                href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/novo') ?>">Novo plano</a>
            <a class="btn btn-primary"
                href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/novo?gerar=1') ?>">Gerar
                rascunho</a>
        </div>
    </div>

    <?php if (!$planoAtivo): ?>
    <div class="plano-empty-card">
        <h3>Nenhum plano ativo</h3>
        <p>Gere um rascunho com base nos dados do paciente, revise as seções e ative o plano quando estiver validado.
        </p>

        <?php if ($modelosPlano): ?>
        <div class="plano-modelos-grid">
            <?php foreach ($modelosPlano as $modelo): ?>
            <a class="plano-modelo-card"
                href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/novo?gerar=1&modelo=' . urlencode((string)($modelo['chave'] ?? ''))) ?>">
                <strong><?= e($modelo['nome'] ?? 'Modelo') ?></strong>
                <span><?= e($modelo['descricao'] ?? 'Gerar rascunho a partir deste modelo.') ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <article class="plano-active-card">
        <div class="plano-active-top">
            <div>
                <span class="plano-status plano-status-ativo">Ativo</span>
                <h3><?= e($planoAtivo['titulo'] ?? 'Plano de Cuidados Home Care') ?></h3>
                <p><?= e($planoAtivo['subtitulo'] ?? '') ?></p>
            </div>

            <div class="plano-active-meta">
                <span>Versão <?= (int)($planoAtivo['versao'] ?? 1) ?></span>
                <span>Início: <?= e($fmtDataPlano($planoAtivo['data_inicio'] ?? null)) ?></span>
                <span>Revisão: <?= e($fmtDataPlano($planoAtivo['data_revisao'] ?? null)) ?></span>
            </div>
        </div>

        <?php if ($planoTexto($planoAtivo['resumo_clinico'] ?? '') !== ''): ?>
        <div class="plano-section plano-section-muted">
            <h4>Resumo clínico do plano</h4>
            <p><?= nl2br(e($planoAtivo['resumo_clinico'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="plano-section-list">
            <?php foreach ($secoesPlano as $campo => $titulo): ?>
            <?php $texto = $planoTexto($planoAtivo[$campo] ?? ''); ?>
            <?php if ($texto !== ''): ?>
            <section class="plano-section">
                <h4><?= e($titulo) ?></h4>
                <p><?= nl2br(e($texto)) ?></p>
            </section>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if ($planoTexto($planoAtivo['observacoes'] ?? '') !== ''): ?>
        <div class="plano-section plano-section-muted">
            <h4>Observações</h4>
            <p><?= nl2br(e($planoAtivo['observacoes'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="button-row plano-active-buttons">
            <a class="btn btn-primary btn-plano"
                href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($planoAtivo['uuid'] ?? $planoAtivo['id'])) . '/editar') ?>">Editar
                plano ativo</a>
            <a class="btn btn-secondary btn-plano" target="_blank"
                href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($planoAtivo['uuid'] ?? $planoAtivo['id'])) . '/pdf') ?>">Gerar
                PDF</a>
            <form class="btn-plano" method="POST"
                action="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($planoAtivo['uuid'] ?? $planoAtivo['id'])) . '/arquivar') ?>"
                onsubmit="return confirm('Arquivar este plano ativo?')">
                <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                <button class="btn btn-secondary btn-plano" type="submit">Arquivar</button>
            </form>
        </div>
    </article>
    <?php endif; ?>
</section>

<section class="panel plano-history-panel">
    <div class="panel-header">
        <div>
            <h2>Histórico de planos</h2>
            <p class="page-subtitle">Cada revisão fica registrada como versão para manter rastreabilidade assistencial.
            </p>
        </div>
    </div>

    <?php if (!$historicoPlanos): ?>
    <p class="empty-state">Nenhum plano de cuidados cadastrado para este paciente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table plano-history-table">
            <thead>
                <tr>
                    <th>Versão</th>
                    <th>Título</th>
                    <th>Modelo</th>
                    <th>Início</th>
                    <th>Revisão</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historicoPlanos as $plano): ?>
                <tr>
                    <td><?= (int)($plano['versao'] ?? 1) ?></td>
                    <td><?= e($plano['titulo'] ?? '-') ?></td>
                    <td><?= e($plano['modelo_chave'] ?? 'manual') ?></td>
                    <td><?= e($fmtDataPlano($plano['data_inicio'] ?? null)) ?></td>
                    <td><?= e($fmtDataPlano($plano['data_revisao'] ?? null)) ?></td>
                    <td><span
                            class="plano-status plano-status-<?= e(strtolower((string)($plano['status'] ?? 'rascunho'))) ?>"><?= e($plano['status'] ?? 'Rascunho') ?></span>
                    </td>
                    <td class="actions plano-history-actions">
                        <a
                            href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($plano['uuid'] ?? $plano['id'])) . '/editar') ?>">Editar</a>
                        <a target="_blank"
                            href="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($plano['uuid'] ?? $plano['id'])) . '/pdf') ?>">PDF</a>

                        <?php if (($plano['status'] ?? '') !== 'Ativo'): ?>
                        <form method="POST"
                            action="<?= url('/pacientes/' . rawurlencode($resourceKey) . '/planos/' . rawurlencode((string)($plano['uuid'] ?? $plano['id'])) . '/ativar') ?>"
                            onsubmit="return confirm('Ativar este plano de cuidados?')">
                            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                            <button type="submit">Ativar</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>