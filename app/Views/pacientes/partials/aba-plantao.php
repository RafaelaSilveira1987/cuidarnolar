<?php $pid = (int) ($record['id'] ?? 0); ?>
<section class="panel pac-panel-help">
    <h2>Relatórios de plantão</h2>
    <p class="page-subtitle">Registros <strong>operacionais</strong> por período (quem está nos cuidados, sinais vitais,
        medicações do dia). A visão global por data permite evoluir para <strong>vários relatórios no mesmo dia</strong>
        (ex.: dois profissionais ou dois períodos).</p>
    <p>
        <a class="btn btn-primary" href="<?= url('/relatorio-plantao?paciente=' . $pid) ?>">Abrir plantão deste
            paciente</a>
        <a class="btn btn-secondary" href="<?= url('/relatorio-plantao') ?>">Lista de pacientes (plantão)</a>
        <a class="btn btn-secondary"
            href="<?= url('/financeiro/relatorios/extrato?paciente_id=' . $pid . '&di=' . urlencode(date('Y-m-01')) . '&df=' . urlencode(date('Y-m-t'))) ?>">Extrato
            financeiro</a>
    </p>
</section>