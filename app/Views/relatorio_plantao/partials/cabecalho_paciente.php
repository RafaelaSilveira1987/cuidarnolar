<div class="rp-cabecalho-paciente">
    <div class="rp-avatar rp-avatar--lg" aria-hidden="true"><?= e($paciente['iniciais'] ?? '?') ?></div>
    <div class="rp-cabecalho-paciente__info">
        <h2 class="rp-cabecalho-paciente__nome"><?= e($paciente['nome_completo'] ?? '') ?></h2>
        <p class="rp-cabecalho-paciente__linha">
            <span>Prontuário <strong><?= e((string) ($paciente['prontuario'] ?? '—')) ?></strong></span>
            <span>Idade <strong><?= (int) ($paciente['idade'] ?? 0) ?> anos</strong></span>
        </p>
        <?php if (!empty($paciente['diagnostico'])): ?>
        <p class="rp-cabecalho-paciente__diag"><strong>Diagnóstico:</strong> <?= e($paciente['diagnostico']) ?></p>
        <?php endif; ?>
    </div>
    <nav class="rp-nav-data" aria-label="Navegação por data">
        <a class="btn btn-secondary"
            href="<?= url('/relatorio-plantao/paciente/' . (int) $paciente_id . '?data=' . urlencode($dataAnterior) . '&turno=' . urlencode($turnoAtual ?? 'manha')) ?>">←
            Dia anterior</a>
        <span class="rp-nav-data__label"><?= e($dataLabelPt ?? '') ?></span>
        <a class="btn btn-secondary"
            href="<?= url('/relatorio-plantao/paciente/' . (int) $paciente_id . '?data=' . urlencode($dataProxima) . '&turno=' . urlencode($turnoAtual ?? 'manha')) ?>">Próximo
            dia →</a>
    </nav>
</div>
