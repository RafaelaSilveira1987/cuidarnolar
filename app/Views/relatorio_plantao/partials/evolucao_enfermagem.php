<?php
/** @var array $turnoData */
$texto = (string) ($turnoData['evolucao'] ?? '');
?>
<section class="rp-secao" aria-labelledby="rp-evo-title">
    <h3 id="rp-evo-title" class="rp-secao__titulo">Evolução de enfermagem</h3>
    <p class="rp-soap-hint">Texto no formato <strong>SOAP</strong> (Subjetivo, Objetivo, Análise, Plano).</p>
    <label class="rp-sr-only" for="rp-evolucao-texto">Evolução do turno</label>
    <textarea id="rp-evolucao-texto" class="rp-textarea" rows="8" readonly><?= e($texto) ?></textarea>
</section>
