<?php

/** @var array $eventosDia */
/** @var array $proximos */
/** @var array $pendentes */

$nomesMeses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro',
];

function agenda_data_br(?string $value, string $fallback = '—'): string
{
    if (!$value) {
        return $fallback;
    }

    $ts = strtotime($value);
    return $ts ? date('d/m/Y', $ts) : $fallback;
}

function agenda_hora(?string $value, string $fallback = '—'): string
{
    if (!$value) {
        return $fallback;
    }

    $ts = strtotime($value);
    return $ts ? date('H:i', $ts) : $fallback;
}

function agenda_nome_alvo(array $evento): string
{
    if (!empty($evento['paciente_nome'])) {
        return (string)$evento['paciente_nome'];
    }

    if (!empty($evento['cuidador_nome'])) {
        return (string)$evento['cuidador_nome'];
    }

    return 'Sem vínculo';
}

$eventosDia = $eventosDia ?? [];
$proximos = $proximos ?? [];
$pendentes = $pendentes ?? [];
$resumoStatus = $resumoStatus ?? [];
$diasComEventos = $diasComEventos ?? [];
$dataSelecionada = $dataSelecionada ?? date('Y-m-d');
$anoCalendario = (int)($anoCalendario ?? date('Y'));
$mesCalendario = (int)($mesCalendario ?? date('m'));

$nomeMesCalendario = $nomesMeses[$mesCalendario] ?? '';
$tituloMesCalendario = $nomeMesCalendario . ' de ' . $anoCalendario;

$inicioMes = new DateTime(sprintf('%04d-%02d-01', $anoCalendario, $mesCalendario));
$diasNoMes = (int)$inicioMes->format('t');
$primeiroDiaSemana = (int)$inicioMes->format('N');

$mesAnteriorData = (clone $inicioMes)->modify('-1 month');
$mesProximoData = (clone $inicioMes)->modify('+1 month');

$anoAnterior = (int)$mesAnteriorData->format('Y');
$mesAnterior = (int)$mesAnteriorData->format('m');
$anoProximo = (int)$mesProximoData->format('Y');
$mesProximo = (int)$mesProximoData->format('m');
?>

<section class="agenda-page-header">
    <div>
        <span class="agenda-eyebrow">Central operacional</span>
        <h1>Agenda</h1>
        <p class="page-subtitle">
            Compromissos, visitas, entrevistas e demandas internas em uma visão limpa.
        </p>
    </div>

    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url('/agendamentos?data=' . date('Y-m-d')) ?>">Hoje</a>
        <a class="btn btn-primary" href="<?= url('/agendamentos/novo') ?>">Novo compromisso</a>
    </div>
</section>

<section class="agenda-kpis">
    <article>
        <span>Pendentes</span>
        <strong><?= (int)($resumoStatus['Pendente'] ?? 0) ?></strong>
    </article>
    <article>
        <span>Agendados</span>
        <strong><?= (int)($resumoStatus['Agendado'] ?? 0) ?></strong>
    </article>
    <article>
        <span>Em andamento</span>
        <strong><?= (int)($resumoStatus['Em andamento'] ?? 0) ?></strong>
    </article>
    <article>
        <span>Concluídos</span>
        <strong><?= (int)($resumoStatus['Concluído'] ?? 0) ?></strong>
    </article>
</section>

<div class="agenda-layout">
    <aside class="agenda-sidebar-card">
        <div class="agenda-calendar-head">
            <a href="<?= url('/agendamentos?mes=' . $mesAnterior . '&ano=' . $anoAnterior) ?>">‹</a>

            <strong><?= e($tituloMesCalendario) ?></strong>

            <a href="<?= url('/agendamentos?mes=' . $mesProximo . '&ano=' . $anoProximo) ?>">›</a>
        </div>

        <div class="agenda-calendar-grid agenda-calendar-weekdays">
            <span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span><span>Dom</span>
        </div>

        <div class="agenda-calendar-grid">
            <?php for ($i = 1; $i < $primeiroDiaSemana; $i++): ?>
            <span class="agenda-day agenda-day--empty"></span>
            <?php endfor; ?>

            <?php for ($dia = 1; $dia <= $diasNoMes; $dia++): ?>
            <?php
                $dataDia = sprintf('%04d-%02d-%02d', $anoCalendario, $mesCalendario, $dia);
                $isActive = $dataDia === $dataSelecionada;
                $totalDia = (int)($diasComEventos[$dataDia] ?? 0);
                ?>
            <a class="agenda-day <?= $isActive ? 'active' : '' ?> <?= $totalDia > 0 ? 'has-events' : '' ?>"
                href="<?= url('/agendamentos?data=' . $dataDia) ?>">
                <?= $dia ?>
                <?php if ($totalDia > 0): ?>
                <small><?= $totalDia ?></small>
                <?php endif; ?>
            </a>
            <?php endfor; ?>
        </div>

        <form class="agenda-date-jump" method="GET" action="<?= url('/agendamentos') ?>">
            <label>
                Ir para data
                <input type="date" name="data" value="<?= e($dataSelecionada) ?>">
            </label>
            <button class="btn btn-secondary" type="submit">Abrir</button>
        </form>
    </aside>

    <main class="agenda-main-card">
        <div class="agenda-section-title-row">
            <div>
                <h2><?= e(agenda_data_br($dataSelecionada)) ?></h2>
                <p class="page-subtitle">Compromissos do dia selecionado</p>
            </div>
        </div>

        <?php if (empty($eventosDia)): ?>
        <p class="empty-state">Nenhum compromisso para esta data.</p>
        <?php else: ?>
        <div class="agenda-timeline">
            <?php foreach ($eventosDia as $evento): ?>
            <article
                class="agenda-item agenda-priority-<?= e(strtolower((string)($evento['prioridade'] ?? 'normal'))) ?>">
                <time><?= e(agenda_hora($evento['data_inicio'] ?? null)) ?></time>

                <div class="agenda-item-body">
                    <div class="agenda-item-top">
                        <span class="agenda-type"><?= e($evento['tipo_evento'] ?? 'Outro') ?></span>
                        <span class="agenda-status"><?= e($evento['status'] ?? 'Pendente') ?></span>
                    </div>

                    <h3><?= e($evento['titulo'] ?? '') ?></h3>
                    <p><?= e(agenda_nome_alvo($evento)) ?></p>

                    <?php if (!empty($evento['local'])): ?>
                    <small>Local: <?= e($evento['local']) ?></small>
                    <?php endif; ?>

                    <div class="agenda-actions">
                        <a href="<?= url('/agendamentos/' . rawurlencode((string)$evento['uuid'])) ?>">Ver</a>
                        <a
                            href="<?= url('/agendamentos/' . rawurlencode((string)$evento['uuid']) . '/editar') ?>">Editar</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>
</div>

<section class="agenda-columns">
    <article class="agenda-list-card">
        <h2>Próximos compromissos</h2>
        <?php if (empty($proximos)): ?>
        <p class="empty-state">Nenhum compromisso futuro.</p>
        <?php else: ?>
        <ul class="agenda-mini-list">
            <?php foreach ($proximos as $evento): ?>
            <li>
                <strong><?= e($evento['titulo'] ?? '') ?></strong>
                <span><?= e(agenda_data_br($evento['data_inicio'] ?? null)) ?> às
                    <?= e(agenda_hora($evento['data_inicio'] ?? null)) ?> · <?= e(agenda_nome_alvo($evento)) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </article>

    <article class="agenda-list-card">
        <h2>Pendências operacionais</h2>
        <?php if (empty($pendentes)): ?>
        <p class="empty-state">Nenhuma pendência.</p>
        <?php else: ?>
        <ul class="agenda-mini-list">
            <?php foreach ($pendentes as $evento): ?>
            <li>
                <strong><?= e($evento['status'] ?? 'Pendente') ?> · <?= e($evento['titulo'] ?? '') ?></strong>
                <span><?= e(agenda_data_br($evento['data_inicio'] ?? null)) ?> ·
                    <?= e($evento['tipo_evento'] ?? 'Outro') ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </article>
</section>