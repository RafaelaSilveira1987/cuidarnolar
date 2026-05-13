<?php
/** @var array<string, array|null> $turnosDoDia */
/** @var string $turnoAtual */
/** @var callable $pBase */
$ordem = [
    'manha' => ['titulo' => 'Manhã', 'faixa' => '07–13h'],
    'tarde' => ['titulo' => 'Tarde', 'faixa' => '13–19h'],
    'noite' => ['titulo' => 'Noite', 'faixa' => '19–07h'],
];
?>
<div class="rp-turnos-grid" role="tablist" aria-label="Seleção de turno">
    <?php foreach ($ordem as $key => $meta): ?>
    <?php
        $d = $turnosDoDia[$key] ?? null;
        $ativo = ($turnoAtual === $key);
        $href = $pBase($dataConsulta, $key);
        $status = $d['status'] ?? 'andamento';
        $badgeClass = match ($status) {
            'concluido' => 'rp-turno-card__badge--ok',
            'intercorrencia' => 'rp-turno-card__badge--warn',
            default => 'rp-turno-card__badge--run',
        };
        ?>
    <a class="rp-turno-card<?= $ativo ? ' rp-turno-card--ativo' : '' ?>" href="<?= e($href) ?>" <?= $ativo ? 'aria-current="true"' : '' ?>>
        <div class="rp-turno-card__top">
            <span class="rp-turno-card__icone"><?= $d ? e($d['icone']) : '—' ?></span>
            <div>
                <div class="rp-turno-card__titulo"><?= e($meta['titulo']) ?></div>
                <div class="rp-turno-card__faixa"><?= e($meta['faixa']) ?></div>
            </div>
        </div>
        <?php if ($d): ?>
        <div class="rp-turno-card__planton"><?= e($d['enfermeiro']) ?></div>
        <div class="rp-turno-card__horario"><?= e($d['horario']) ?></div>
        <span class="rp-turno-card__badge <?= $badgeClass ?>"><?= e($d['status_label']) ?></span>
        <?php else: ?>
        <div class="rp-turno-card__planton rp-muted">Sem relatório</div>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>
