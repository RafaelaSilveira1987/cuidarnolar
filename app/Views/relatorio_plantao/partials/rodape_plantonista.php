<?php
/** @var array $turnoData */
$assinado = !empty($turnoData['assinado']);
$nome = $turnoData['enfermeiro'] ?? '';
$coren = $turnoData['coren'] ?? '';

$iniPlanton = '';
$partsPl = preg_split('/\s+/', trim($nome), -1, PREG_SPLIT_NO_EMPTY);
if ($partsPl !== false && $partsPl !== []) {
    $fn = $partsPl[0];
    $sn = $partsPl[1] ?? '';
    $c0 = function_exists('mb_substr') ? mb_substr($fn, 0, 1, 'UTF-8') : substr($fn, 0, 1);
    $c1 = $sn !== '' ? (function_exists('mb_substr') ? mb_substr($sn, 0, 1, 'UTF-8') : substr($sn, 0, 1)) : '';
    $iniPlanton = strtoupper($c0 . $c1);
}
?>
<footer class="rp-rodape">
    <div class="rp-rodape__planton">
        <div class="rp-avatar" aria-hidden="true"><?= e($iniPlanton !== '' ? $iniPlanton : '?') ?></div>
        <div>
            <div class="rp-rodape__nome"><?= e($nome) ?></div>
            <div class="rp-rodape__coren"><?= e($coren) ?></div>
        </div>
    </div>
    <div class="rp-rodape__acao">
        <?php if ($assinado): ?>
        <span class="rp-tag-assinado">Relatório assinado</span>
        <?php else: ?>
        <button type="button" class="btn btn-primary" id="rp-btn-assinar">Assinar relatório</button>
        <script>
        (function () {
            var b = document.getElementById('rp-btn-assinar');
            if (b) b.addEventListener('click', function () {
                alert('Demonstração: a assinatura será gravada no banco quando o módulo estiver integrado.');
            });
        })();
        </script>
        <?php endif; ?>
    </div>
</footer>
