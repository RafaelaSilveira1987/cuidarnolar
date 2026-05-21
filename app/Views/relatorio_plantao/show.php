<?php

/**
 * app/Views/relatorio_plantao/show.php
 */

function rp_decode_json_or_text(mixed $value, string $fallback = '—'): string
{
    if ($value === null) {
        return $fallback;
    }

    // Normaliza: se já vier como array, usa direto; senão tenta decodificar JSON
    $decoded = null;
    if (is_array($value)) {
        $decoded = $value;
    } else {
        $value = trim((string)$value);
        if ($value === '') {
            return $fallback;
        }
        $tentativa = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($tentativa)) {
            $decoded = $tentativa;
        }
    }

    // Se decodificou como array, formata cada item
    if ($decoded !== null) {
        $linhas = [];
        foreach ($decoded as $i => $item) {
            if (is_array($item)) {
                // Array associativo (ex: medicações com medicamento/horario/via/status)
                $medicamento = trim($item['medicamento'] ?? $item['descricao'] ?? '');
                $horario     = trim($item['horario'] ?? '');
                $via         = trim($item['via'] ?? '');
                $status      = trim($item['status'] ?? '');

                $partes = array_filter([
                    $medicamento,
                    $horario ? "às {$horario}" : '',
                    $via     ? "via {$via}"    : '',
                    $status  ? "({$status})"   : '',
                ]);

                if (!empty($partes)) {
                    $linhas[] = ($i + 1) . ' - ' . implode(' ', $partes);
                }
            } else {
                $texto = trim((string)$item);
                if ($texto !== '') {
                    $linhas[] = $texto;
                }
            }
        }

        if (empty($linhas)) {
            return $fallback;
        }

        return nl2br(htmlspecialchars(implode("\n", $linhas), ENT_QUOTES, 'UTF-8'));
    }

    // Texto simples
    return nl2br(htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
}

function rp_show_text(mixed $value, string $fallback = '—'): string
{
    if ($value === null) {
        return $fallback;
    }

    $value = trim((string)$value);

    return $value !== ''
        ? htmlspecialchars($value)
        : $fallback;
}

function rp_show_datetime(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);

    return $ts
        ? date('d/m/Y H:i', $ts)
        : $fallback;
}

function rp_show_date(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);

    return $ts
        ? date('d/m/Y', $ts)
        : $fallback;
}

$relatorio = isset($relatorio) && is_array($relatorio)
    ? $relatorio
    : [];

$paciente = isset($paciente) && is_array($paciente)
    ? $paciente
    : [];

$nomePaciente = $paciente['nome_completo']
    ?? $paciente['nome']
    ?? $relatorio['paciente_nome']
    ?? 'Paciente';

$status = strtolower((string)($relatorio['status'] ?? 'registrado'));

$statusLabel = match ($status) {
    'assinado'   => 'Assinado',
    'finalizado' => 'Finalizado',
    'rascunho'   => 'Rascunho',
    default      => 'Registrado',
};

$statusClass = match ($status) {
    'assinado'   => 'success',
    'finalizado' => 'primary',
    'rascunho'   => 'warning',
    default      => 'secondary',
};

$inicio = $relatorio['data_inicio'] ?? null;
$fim    = $relatorio['data_fim'] ?? null;

$faixaHorario = rp_show_datetime($inicio);

if ($fim) {
    $faixaHorario .= ' até ' . rp_show_datetime($fim);
}

$voltarLink = 'javascript:history.back()';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_erp.css">

<div class="rp-page">

    <!-- Header -->
    <header class="rp-header-card">
        <div class="rp-patient">
            <div class="rp-avatar"> </div>

            <div class="rp-patient-info">
                <h1>Relatório de Plantão</h1>

                <p class="rp-patient-meta">
                    <?= htmlspecialchars($nomePaciente) ?>
                    <?php if (!empty($relatorio['data_inicio'])): ?>
                    • <?= rp_show_date($relatorio['data_inicio']) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="rp-header-actions">
            <a href="<?= $voltarLink ?>" class="rp-btn rp-btn-secondary">
                Voltar
            </a>

            <?php if (!empty($relatorio['uuid'])): ?>
            <a href="<?= BASE_URL ?>/relatorio-plantao/plantao/<?= rawurlencode($relatorio['uuid']) ?>/editar"
                class="rp-btn rp-btn-primary">
                Editar
            </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Stats -->
    <div class="rp-stats">
        <div class="rp-stat">
            <span class="rp-stat-label">Período</span>
            <strong><?= htmlspecialchars($faixaHorario) ?></strong>
        </div>

        <div class="rp-stat">
            <span class="rp-stat-label">Status</span>
            <strong>
                <span class="rp-badge rp-badge-<?= $statusClass ?>">
                    <?= htmlspecialchars($statusLabel) ?>
                </span>
            </strong>
        </div>

        <div class="rp-stat">
            <span class="rp-stat-label">Responsável</span>
            <strong>
                <?= rp_show_text(
                    $relatorio['profissional_nome']
                        ?? $relatorio['responsavel_nome']
                        ?? 'Não informado'
                ) ?>
            </strong>
        </div>
    </div>

    <!-- Resumo Clínico -->
    <section class="rp-summary-grid">

        <!-- Identificação Clínica -->
        <div class="rp-card">
            <div class="rp-card-body">

                <h3 class="rp-section-title">
                    Identificação Clínica
                </h3>

                <div class="rp-info-grid">

                    <div>
                        <span>Idade</span>
                        <strong><?= rp_show_text($paciente['idade'] ?? null) ?> anos</strong>
                    </div>

                    <div>
                        <span>Sexo</span>
                        <strong><?= rp_show_text($paciente['sexo'] ?? null) ?></strong>
                    </div>

                    <div>
                        <span>CID</span>
                        <strong><?= rp_show_text($paciente['cid_principal'] ?? null) ?></strong>
                    </div>

                    <div>
                        <span>Tipo Sanguíneo</span>
                        <strong><?= rp_show_text($paciente['tipo_sanguineo'] ?? null) ?></strong>
                    </div>

                </div>

                <div class="rp-text-box" style="margin-top:16px;">
                    <strong>Diagnóstico:</strong><br>
                    <?= rp_show_text($paciente['diagnostico'] ?? null) ?>
                </div>

                <?php if (!empty($paciente['alergias'])): ?>
                <div class="rp-alert-box">
                    ⚠ <strong>Alergias:</strong>
                    <?= rp_show_text($paciente['alergias']) ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Dispositivos -->
        <div class="rp-card">
            <div class="rp-card-body">

                <h3 class="rp-section-title">
                    Dispositivos em Uso
                </h3>

                <div class="rp-tags">

                    <?php if (($paciente['usa_oxigenio'] ?? '') === 'Sim'): ?>
                    <span class="rp-tag">O₂</span>
                    <?php endif; ?>

                    <?php if (($paciente['usa_sonda'] ?? '') === 'Sim'): ?>
                    <span class="rp-tag">Sonda</span>
                    <?php endif; ?>

                    <?php if (($paciente['traqueostomia'] ?? '') === 'Sim'): ?>
                    <span class="rp-tag">Traqueostomia</span>
                    <?php endif; ?>

                    <?php if (($paciente['gastrostomia'] ?? '') === 'Sim'): ?>
                    <span class="rp-tag">GTT</span>
                    <?php endif; ?>

                    <?php if (!empty($paciente['sne'])): ?>
                    <span class="rp-tag">SNE</span>
                    <?php endif; ?>

                    <?php if (!empty($paciente['picc'])): ?>
                    <span class="rp-tag">PICC</span>
                    <?php endif; ?>

                    <?php if (($paciente['cateter_vesical'] ?? '') === 'Sim'): ?>
                    <span class="rp-tag">Cateter Vesical</span>
                    <?php endif; ?>

                </div>

            </div>
        </div>

        <!-- Mobilidade -->
        <div class="rp-card">
            <div class="rp-card-body">

                <h3 class="rp-section-title">
                    Mobilidade e Cognição
                </h3>

                <div class="rp-info-grid">

                    <div>
                        <span>Mobilidade</span>
                        <strong><?= rp_show_text($paciente['mobilidade'] ?? null) ?></strong>
                    </div>

                    <div>
                        <span>Cognição</span>
                        <strong><?= rp_show_text($paciente['estado_cognitivo_base'] ?? null) ?></strong>
                    </div>

                    <div>
                        <span>Acamado</span>
                        <strong>
                            <?= !empty($paciente['acamado']) ? 'Sim' : 'Não' ?>
                        </strong>
                    </div>

                </div>

            </div>
        </div>

        <!-- Alertas -->
        <?php if (
            !empty($paciente['areas_risco']) ||
            !empty($paciente['condutas_permanentes'])
        ): ?>

        <div class="rp-card">
            <div class="rp-card-body">

                <h3 class="rp-section-title">
                    Alertas Assistenciais
                </h3>

                <?php if (!empty($paciente['areas_risco'])): ?>
                <div class="rp-alert-box">
                    ⚠ <?= rp_decode_json_or_text($paciente['areas_risco']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($paciente['condutas_permanentes'])): ?>
                <div class="rp-alert-box">
                    ⚠ <?= rp_decode_json_or_text($paciente['condutas_permanentes']) ?>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php endif; ?>

    </section>

    <!-- Conteúdo -->
    <article class="rp-card expanded">
        <div class="rp-card-body" style="display:block;">

            <!-- Sinais Vitais -->
            <section class="rp-section">
                <h3>Sinais Vitais</h3>

                <div class="rp-vitals-grid">
                    <div class="rp-vital">
                        <span class="rp-vital-label">PA</span>
                        <strong><?= rp_show_text($relatorio['pa'] ?? null) ?></strong>
                    </div>

                    <div class="rp-vital">
                        <span class="rp-vital-label">FC</span>
                        <strong><?= rp_show_text($relatorio['fc'] ?? null) ?></strong>
                    </div>

                    <div class="rp-vital">
                        <span class="rp-vital-label">Temperatura</span>
                        <strong><?= rp_show_text($relatorio['temperatura'] ?? null) ?></strong>
                    </div>

                    <div class="rp-vital">
                        <span class="rp-vital-label">SpO₂</span>
                        <strong><?= rp_show_text($relatorio['spo2'] ?? null) ?></strong>
                    </div>

                    <div class="rp-vital">
                        <span class="rp-vital-label">HGT</span>
                        <strong><?= rp_show_text($relatorio['hgt'] ?? null) ?></strong>
                    </div>

                    <div class="rp-vital">
                        <span class="rp-vital-label">Dor</span>
                        <strong><?= rp_show_text($relatorio['nivel_dor'] ?? null) ?></strong>
                    </div>
                </div>
            </section>

            <!-- Estado do Paciente -->
            <?php if (!empty($relatorio['estado_paciente']) || !empty($relatorio['consciencia'])): ?>
            <section class="rp-section">
                <h3>Estado do Paciente</h3>

                <?php if (!empty($relatorio['estado_paciente'])): ?>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['estado_paciente']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($relatorio['consciencia'])): ?>
                <div class="rp-text-box" style="margin-top:12px;">
                    <strong>Consciência:</strong>
                    <?= rp_show_text($relatorio['consciencia']) ?>
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Evolução -->
            <?php if (!empty($relatorio['evolucao'])): ?>
            <section class="rp-section">
                <h3>Evolução de Enfermagem</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['evolucao']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Alimentação -->
            <?php if (!empty($relatorio['alimentacao']) || !empty($relatorio['hidratacao_ml'])): ?>
            <section class="rp-section">
                <h3>Alimentação e Hidratação</h3>

                <?php if (!empty($relatorio['alimentacao'])): ?>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['alimentacao']) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($relatorio['hidratacao_ml'])): ?>
                <div class="rp-text-box" style="margin-top:12px;">
                    <strong>Hidratação:</strong>
                    <?= rp_show_text($relatorio['hidratacao_ml']) ?> ml
                </div>
                <?php endif; ?>
            </section>
            <?php endif; ?>

            <!-- Eliminações -->
            <?php if (!empty($relatorio['eliminacoes'])): ?>
            <section class="rp-section">
                <h3>Eliminações</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['eliminacoes']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Medicações -->
            <?php if (!empty($relatorio['medicacoes'])): ?>
            <section class="rp-section">
                <h3>Medicações / Condutas</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['medicacoes']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Intercorrências -->
            <?php if (!empty($relatorio['intercorrencias'])): ?>
            <section class="rp-section">
                <h3>Intercorrências</h3>
                <div class="rp-text-box rp-text-box-warning">
                    <?= rp_decode_json_or_text($relatorio['intercorrencias']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Higiene -->
            <?php if (!empty($relatorio['higiene'])): ?>
            <section class="rp-section">
                <h3>Higiene</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['higiene']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Sono -->
            <?php if (!empty($relatorio['sono'])): ?>
            <section class="rp-section">
                <h3>Sono e Repouso</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['sono']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Decúbito -->
            <?php if (!empty($relatorio['decubito'])): ?>
            <section class="rp-section">
                <h3>Decúbito</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['decubito']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Observações -->
            <?php if (!empty($relatorio['observacoes_gerais'])): ?>
            <section class="rp-section">
                <h3>Observações Gerais</h3>
                <div class="rp-text-box">
                    <?= rp_decode_json_or_text($relatorio['observacoes_gerais']) ?>
                </div>
            </section>
            <?php endif; ?>

        </div>
    </article>
</div>