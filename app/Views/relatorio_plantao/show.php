<?php

/**
 * app/Views/relatorio_plantao/show.php
 */

function rp_text(mixed $value, string $fallback = '—'): string
{
    if ($value === null) {
        return $fallback;
    }

    $value = trim((string)$value);

    return $value !== ''
        ? nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'))
        : $fallback;
}

function rp_date(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);

    return $ts
        ? date('d/m/Y', $ts)
        : $fallback;
}

function rp_datetime(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);

    return $ts
        ? date('d/m/Y H:i', $ts)
        : $fallback;
}

$relatorio = is_array($relatorio ?? null)
    ? $relatorio
    : [];

$paciente = is_array($paciente ?? null)
    ? $paciente
    : [];

$nomePaciente = $paciente['nome_completo']
    ?? $relatorio['paciente_nome']
    ?? 'Paciente';

$idade = !empty($paciente['idade'])
    ? $paciente['idade'] . ' anos'
    : 'Não informado';

$profissional = $relatorio['profissional_nome']
    ?? 'Não informado';

$tipoLocal = ($relatorio['tipo_local'] ?? '') === 'domiciliar'
    ? 'Domiciliar'
    : 'Hospitalar';

$quarto = trim((string)($relatorio['quarto'] ?? ''));

$localCompleto = $quarto !== ''
    ? $quarto . ' / ' . $tipoLocal
    : $tipoLocal;

?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_erp.css">

<div class="rp-document">

    <!-- TOPO -->
    <header class="rp-header">

        <div class="rp-header-left">
            <h1>📋 RELATÓRIO DE PLANTÃO</h1>

            <p>
                Documento assistencial para acompanhamento clínico e passagem de plantão.
            </p>
        </div>

        <div class="rp-header-right rp-screen-only">

            <button onclick="window.print()" class="rp-btn">
                Imprimir / PDF
            </button>

            <a href="javascript:history.back()" class="rp-btn rp-btn-secondary">
                Voltar
            </a>

        </div>

    </header>

    <!-- IDENTIFICAÇÃO -->
    <section class="rp-block">

        <h2>Identificação do Paciente</h2>

        <div class="rp-grid">

            <div>
                <strong>Paciente:</strong><br>
                <?= rp_text($nomePaciente) ?>
            </div>

            <div>
                <strong>DN / Idade:</strong><br>
                <?= rp_date($paciente['data_nascimento'] ?? null) ?>
                - <?= $idade ?>
            </div>

            <div>
                <strong>Data:</strong><br>
                <?= rp_date($relatorio['data_inicio'] ?? null) ?>
            </div>

            <div>
                <strong>Horário do Plantão:</strong><br>

                <?= rp_datetime($relatorio['data_inicio'] ?? null) ?>

                <?php if (!empty($relatorio['data_fim'])): ?>
                às <?= rp_datetime($relatorio['data_fim']) ?>
                <?php endif; ?>
            </div>

            <div>
                <strong>Internação:</strong><br>
                <?= rp_text($paciente['diagnostico'] ?? null) ?>
            </div>

            <div>
                <strong>Quarto / Local:</strong><br>
                <?= rp_text($localCompleto) ?>
            </div>

            <div>
                <strong>Acompanhante:</strong><br>
                <?= rp_text($relatorio['nome_acompanhante'] ?? null) ?>
            </div>

            <div>
                <strong>Profissional:</strong><br>
                <?= rp_text($profissional) ?>
            </div>

        </div>

    </section>

    <!-- SINAIS VITAIS -->
    <section class="rp-block">

        <h2>⇒ Sinais Vitais</h2>

        <div class="rp-grid">

            <div>
                <strong>Temperatura:</strong><br>
                <?= rp_text($relatorio['temperatura'] ?? null) ?>
            </div>

            <div>
                <strong>Pressão Arterial:</strong><br>
                <?= rp_text($relatorio['pa'] ?? null) ?>
            </div>

            <div>
                <strong>Frequência Cardíaca:</strong><br>
                <?= rp_text($relatorio['fc'] ?? null) ?>
            </div>

            <div>
                <strong>Saturação:</strong><br>
                <?= rp_text($relatorio['spo2'] ?? null) ?>
            </div>

            <div>
                <strong>Frequência Respiratória:</strong><br>
                <?= rp_text($relatorio['frequencia_respiratoria'] ?? null) ?>
            </div>

            <div>
                <strong>HGT:</strong><br>
                <?= rp_text($relatorio['hgt'] ?? null) ?>
            </div>

        </div>

    </section>

    <!-- ALIMENTAÇÃO -->
    <section class="rp-block">

        <h2>⇒ Alimentação / Hidratação</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['alimentacao'] ?? null) ?>
        </div>

    </section>

    <!-- HIGIENE -->
    <section class="rp-block">

        <h2>⇒ Higiene</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['higiene'] ?? null) ?>
        </div>

    </section>

    <!-- SONO -->
    <section class="rp-block">

        <h2>⇒ Sono e Descanso</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['sono'] ?? null) ?>
        </div>

    </section>

    <!-- MEDICAÇÕES -->
    <section class="rp-block">

        <h2>⇒ Medicações</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['medicacoes'] ?? null) ?>
        </div>

    </section>

    <!-- EVOLUÇÃO -->
    <section class="rp-block">

        <h2>⇒ Evolução</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['evolucao'] ?? null) ?>
        </div>

    </section>

    <!-- INTERCORRÊNCIAS -->
    <section class="rp-block">

        <h2>⇒ Intercorrências</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['intercorrencias'] ?? null) ?>
        </div>

    </section>

    <!-- INFORMAÇÕES ADICIONAIS -->
    <section class="rp-block">

        <h2>⇒ Informações Adicionais</h2>

        <div class="rp-grid">

            <div>
                <strong>Estado Geral:</strong><br>
                <?= rp_text($relatorio['estado_geral'] ?? null) ?>
            </div>

            <div>
                <strong>Pele / Mucosas:</strong><br>
                <?= rp_text($relatorio['pele_mucosas'] ?? null) ?>
            </div>

            <div>
                <strong>Eliminações:</strong><br>
                <?= rp_text($relatorio['eliminacoes'] ?? null) ?>
            </div>

            <div>
                <strong>Dispositivos:</strong><br>
                <?= rp_text($relatorio['dispositivos'] ?? null) ?>
            </div>

        </div>

        <?php if (!empty($relatorio['queixas_referidas'])): ?>

        <div class="rp-text" style="margin-top:16px;">
            <strong>Queixas Referidas:</strong><br>
            <?= rp_text($relatorio['queixas_referidas']) ?>
        </div>

        <?php endif; ?>

        <?php if (!empty($relatorio['exame_fisico'])): ?>

        <div class="rp-text" style="margin-top:16px;">
            <strong>Exame Físico:</strong><br>
            <?= rp_text($relatorio['exame_fisico']) ?>
        </div>

        <?php endif; ?>

    </section>

    <!-- VISITA MÉDICA -->
    <section class="rp-block">

        <h2>⇒ Visita Médica e Condutas</h2>

        <div class="rp-text">
            <?= rp_text($relatorio['visita_medica'] ?? null) ?>
        </div>

    </section>

    <!-- PROFISSIONAIS -->
    <section class="rp-block">

        <h2>⇒ Entrada / Saída de Profissionais e Familiares</h2>

        <div class="rp-grid">

            <div>
                <strong>Enfermeiros / Técnicos:</strong><br>
                <?= rp_text($relatorio['entrada_saida_profissionais'] ?? null) ?>
            </div>

            <div>
                <strong>Familiares / Visitas:</strong><br>
                <?= rp_text($relatorio['entrada_saida_familiares'] ?? null) ?>
            </div>

        </div>

    </section>

    <!-- PASSAGEM -->
    <section class="rp-block">

        <h2>⇒ Passagem de Plantão</h2>

        <div class="rp-text">
            Plantão entregue para:
            <strong>
                <?= rp_text($relatorio['plantao_entregue_para'] ?? null) ?>
            </strong>
        </div>

    </section>

    <!-- OBSERVAÇÕES -->
    <section class="rp-block">

        <h2>⇒ Observações Finais</h2>

        <?php if (!empty($relatorio['peso'])): ?>

        <div class="rp-text" style="margin-bottom:16px;">
            <strong>Peso:</strong>
            <?= rp_text($relatorio['peso']) ?> kg
        </div>

        <?php endif; ?>

        <div class="rp-text">
            <?= rp_text($relatorio['observacoes_gerais'] ?? null) ?>
        </div>

    </section>

    <!-- ASSINATURA -->
    <footer class="rp-footer">

        <div class="rp-signature">

            _______________________________________

            <br><br>

            <?= rp_text($profissional) ?>

        </div>

        <div class="rp-generated">
            Documento gerado em <?= date('d/m/Y H:i') ?>
        </div>

    </footer>

</div>
```