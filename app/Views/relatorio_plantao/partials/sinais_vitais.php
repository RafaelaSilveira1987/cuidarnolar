<?php
/** @var array $turnoData */
$sinais = $turnoData['sinais_vitais'] ?? [];
?>
<section class="rp-secao" aria-labelledby="rp-sv-title">
    <h3 id="rp-sv-title" class="rp-secao__titulo">Sinais vitais</h3>
    <div class="rp-sv-grid">
        <?php foreach ($sinais as $s): ?>
        <?php
            $st = $s['status'] ?? 'normal';
            $tone = match ($st) {
                'critico' => 'rp-sv-card--red',
                'atencao' => 'rp-sv-card--yellow',
                default => 'rp-sv-card--green',
            };
            ?>
        <div class="rp-sv-card <?= $tone ?>">
            <div class="rp-sv-card__label"><?= e($s['label'] ?? '') ?></div>
            <div class="rp-sv-card__valor"><?= e((string) ($s['valor'] ?? '')) ?>
                <span class="rp-sv-card__un"><?= e($s['unidade'] ?? '') ?></span>
            </div>
            <div class="rp-sv-card__hint"><?= e($s['texto'] ?? '') ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
