<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">Agrupado por paciente — cada paciente lista os relatórios de plantão inseridos</p>
    </div>
</section>

<section class="panel rp-index">
    <?php if (empty($grupos)): ?>
    <p class="rp-muted">Nenhum relatório encontrado.</p>
    <?php else: ?>
    <?php foreach ($grupos as $grupo): ?>
    <?php
        $p = $grupo['paciente'] ?? [];
        $pid = (int) ($grupo['paciente_id'] ?? 0);
        $dataRef = $grupo['data_plantao'] ?? date('Y-m-d');
        $turnos = $grupo['turnos'] ?? [];
        ?>
    <article class="rp-grupo">
        <header class="rp-grupo__head">
            <div class="rp-avatar" aria-hidden="true"><?= e($p['iniciais'] ?? '?') ?></div>
            <div class="rp-grupo__meta">
                <h2 class="rp-grupo__nome"><?= e($p['nome_completo'] ?? 'Paciente') ?></h2>
                <p class="rp-grupo__sub">
                    Prontuário <?= e((string) ($p['prontuario'] ?? '—')) ?>
                    · <?= (int) ($p['idade'] ?? 0) ?> anos
                    <?php if (!empty($p['diagnostico'])): ?>
                    · <?= e($p['diagnostico']) ?>
                    <?php endif; ?>
                </p>
            </div>
            <a class="btn btn-primary"
                href="<?= url('/relatorio-plantao/paciente/' . $pid . '?data=' . urlencode($dataRef) . '&turno=manha') ?>">Abrir
                plantão</a>
        </header>

        <h3 class="rp-grupo__sec">Relatórios inseridos (<?= e(date('d/m/Y', strtotime($dataRef))) ?>)</h3>
        <ul class="rp-lista-relatorios">
            <?php foreach (['manha' => 'Manhã 07–13h', 'tarde' => 'Tarde 13–19h', 'noite' => 'Noite 19–07h'] as $tk => $titulo): ?>
            <?php $t = $turnos[$tk] ?? null; ?>
            <li class="rp-linha-relatorio">
                <div>
                    <strong><?= e($titulo) ?></strong>
                    <?php if ($t): ?>
                    <span class="rp-muted"> — <?= e($t['enfermeiro'] ?? '') ?></span>
                    <span class="rp-badge rp-badge--<?= e($t['status'] ?? 'andamento') ?>"><?= e($t['status_label'] ?? '') ?></span>
                    <?php if (!empty($t['assinado'])): ?>
                    <span class="rp-pill rp-pill--ok">Assinado</span>
                    <?php else: ?>
                    <span class="rp-pill rp-pill--pend">Pendente assinatura</span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="rp-muted"> — sem registro</span>
                    <?php endif; ?>
                </div>
                <?php if ($t): ?>
                <a class="btn btn-secondary"
                    href="<?= url('/relatorio-plantao/paciente/' . $pid . '?data=' . urlencode($dataRef) . '&turno=' . urlencode($tk)) ?>">Ver
                    turno</a>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </article>
    <?php endforeach; ?>
    <?php endif; ?>
</section>
