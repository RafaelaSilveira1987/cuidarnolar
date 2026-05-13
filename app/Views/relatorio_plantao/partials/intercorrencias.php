<?php
/** @var array $turnoData */
$lista = $turnoData['intercorrencias'] ?? [];
?>
<section class="rp-secao" aria-labelledby="rp-int-title">
    <h3 id="rp-int-title" class="rp-secao__titulo">Intercorrências</h3>
    <?php if ($lista === []): ?>
    <p class="rp-inter-vazio">Nenhuma intercorrência neste turno.</p>
    <?php else: ?>
    <ul class="rp-inter-lista">
        <?php foreach ($lista as $it): ?>
        <li class="rp-inter-card">
            <span class="rp-inter-ico" aria-hidden="true">⚠</span>
            <div>
                <p class="rp-inter-desc"><?= e($it['descricao'] ?? '') ?></p>
                <p class="rp-inter-hora"><?= e($it['horario'] ?? '') ?></p>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</section>
