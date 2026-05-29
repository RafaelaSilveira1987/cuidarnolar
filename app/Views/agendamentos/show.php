<?php
$evento = isset($evento) && is_array($evento) ? $evento : [];

function agenda_show_datetime(?string $value, string $fallback = '—'): string
{
    if (!$value) {
        return $fallback;
    }

    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : $fallback;
}

function agenda_show_text(mixed $value, string $fallback = '—'): string
{
    $value = trim((string)$value);
    return $value !== '' ? e($value) : $fallback;
}
?>

<section class="agenda-page-header">
    <div>
        <span class="agenda-eyebrow"><?= agenda_show_text($evento['tipo_evento'] ?? 'Compromisso') ?></span>
        <h1><?= agenda_show_text($evento['titulo'] ?? 'Agendamento') ?></h1>
        <p class="page-subtitle">
            <?= agenda_show_text($evento['status'] ?? 'Pendente') ?> · <?= agenda_show_datetime($evento['data_inicio'] ?? null) ?>
        </p>
    </div>

    <div class="button-row">
        <a class="btn btn-secondary" href="<?= url('/agendamentos') ?>">Voltar</a>
        <?php if (!empty($evento['uuid'])): ?>
            <a class="btn btn-primary" href="<?= url('/agendamentos/' . rawurlencode((string)$evento['uuid']) . '/editar') ?>">Editar</a>
        <?php endif; ?>
    </div>
</section>

<section class="panel agenda-show-panel">
    <div class="agenda-detail-grid">
        <div><span>Início</span><strong><?= agenda_show_datetime($evento['data_inicio'] ?? null) ?></strong></div>
        <div><span>Fim</span><strong><?= agenda_show_datetime($evento['data_fim'] ?? null) ?></strong></div>
        <div><span>Paciente</span><strong><?= agenda_show_text($evento['paciente_nome'] ?? null) ?></strong></div>
        <div><span>Cuidador/profissional</span><strong><?= agenda_show_text($evento['cuidador_nome'] ?? null) ?></strong></div>
        <div><span>Prioridade</span><strong><?= agenda_show_text($evento['prioridade'] ?? null) ?></strong></div>
        <div><span>Local</span><strong><?= agenda_show_text($evento['local'] ?? null) ?></strong></div>
    </div>

    <div class="agenda-description-box">
        <h2>Descrição</h2>
        <p><?= nl2br(agenda_show_text($evento['descricao'] ?? null, 'Nenhuma descrição registrada.')) ?></p>
    </div>
</section>
