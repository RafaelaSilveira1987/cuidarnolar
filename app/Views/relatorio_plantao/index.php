<?php
/**
 * app/Views/relatorio_plantao/index.php
 *
 * Variáveis disponíveis (vindas do controller):
 *   $pacienteId   int     — ID do paciente
 *   $pacientes    array   — lista de pacientes para seleção
 *   $dataAtual    string  — data atual formatada "Y-m-d"
 *   $dataLabelPt  string  — data formatada para exibição
 *   $temDados     bool    — se há dados de plantão
 */

// Variáveis padrão caso não sejam fornecidas
$pacientes ??= [];
$pacienteId ??= $pacientes[0]['id'] ?? 1;
$dataAtual ??= date('Y-m-d');
$dataLabelPt ??= date('d F Y', strtotime($dataAtual));
$temDados ??= !empty($pacientes[0]['plantoes'] ?? []);

// Se houver múltiplos pacientes, filtrar
if (count($pacientes) > 1) {
    foreach ($pacientes as $paciente) {
        if ($paciente['id'] == $pacienteId) {
            $pacienteSelecionado = $paciente;
            break;
        }
    }
} else {
    $pacienteSelecionado = $pacientes[0] ?? null;
}

// Buscar dados da tabela pacientes para usar como fallback
if (!$pacienteSelecionado && !empty($pacientes)) {
    $pacienteSelecionado = $pacientes[0];
}

// Se não houver paciente selecionado, redirecionar
if (!$pacienteSelecionado) {
    header('Location: /relatorio');
    exit;
}

// URL base atual
$currentUrl = $_SERVER['REQUEST_URI'];
$baseUrl = preg_replace('/\?paciente=\d+/', '', $currentUrl);
$baseUrl = strtok($baseUrl, '&') ?: $baseUrl; // Remover query params

?>
<?php $this->start('content') ?>
<link rel="stylesheet" href="<?= url('/assets/css/relatorio_plantao.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="relatorio-plantao-container">

    <!-- ── Cabeçalho ─────────────────────────────────────────── -->
    <div class="rp-header rp-header--with-tabs">
        <div class="rp-header__top">
            <div class="rp-header__patient-info">
                <?php if (!empty($pacientes[0])): ?>
                <div class="rp-avatar rp-avatar--lg">
                    <?= htmlspecialchars($pacientes[0]['iniciais'] ?? strtoupper(substr($pacientes[0]['nome_completo'] ?? 'PC', 0, 2))) ?>
                </div>
                <div>
                    <div class="rp-title rp-title--h2">
                        <?= htmlspecialchars($pacientes[0]['nome_completo'] ?? 'Paciente') ?>
                    </div>
                    <div class="rp-meta">
                        <span><?= htmlspecialchars($pacientes[0]['cpf'] ?? 'CPF não informado') ?></span>
                        <span>CPF</span>
                        <span></span>
                    </div>
                </div>
                <?php else: ?>
                <div class="rp-title rp-title--h2">Paciente não selecionado</div>
                <?php endif; ?>
            </div>

            <div class="rp-date-nav">
                <?php if (!empty($pacientes)): ?>
                <a href="<?= $baseUrl ?>&paciente=<?= $pacientes[0]['id'] ?? $pacienteId ?>"
                   class="rp-btn rp-btn--nav rp-btn--outline" title="Alterar paciente">
                    <i class="ti ti-user"></i> Selecionar paciente
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabs de navegação -->
        <div class="rp-tabs">
            <a href="<?= $baseUrl ?>"
               class="rp-tab rp-tab--active">
                <i class="ti ti-calendar-check"></i> Relatório de plantão
            </a>
            <a href="<?= $baseUrl ?>&tab=historico"
               class="rp-tab">
                <i class="ti ti-history"></i> Histórico
            </a>
            <a href="<?= $baseUrl ?>&tab=prescricao"
               class="rp-tab">
                <i class="ti ti-file-medical"></i> Prescrição
            </a>
        </div>
    </div>

    <!-- ── Conteúdo Principal ─────────────────────────────────── -->
    <?php if ($temDados && !empty($pacientes[0]['plantoes'] ?? [])): ?>
        <?php foreach ($pacientes[0]['plantoes'] as $idx => $plantao): ?>
        <div class="rp-periodo-card">
            <div class="rp-periodo-header">
                <div class="rp-periodo-top">
                    <div class="rp-periodo-date">
                        <i class="ti ti-calendar"></i>
                        <?= htmlspecialchars(date('d', strtotime($plantao['data']))) ?> de
                        <?= htmlspecialchars(date('F', strtotime($plantao['data']))) ?> de
                        <?= htmlspecialchars(date('Y', strtotime($plantao['data']))) ?>
                    </div>
                    <div class="rp-periodo-period">
                        <i class="ti ti-clock"></i>
                        <?= htmlspecialchars($plantao['periodo'] ?? 'Plantão completo') ?>
                    </div>
                </div>

                <?php if (isset($plantao['status']) && $plantao['status'] === 'assinado'): ?>
                <span class="rp-badge rp-badge--success">
                    <i class="ti ti-check-circle"></i> Assinado
                </span>
                <?php else: ?>
                <button class="rp-btn rp-btn--primary rp-btn--sm"
                        onclick="assinarPlantao(<?= $plantao['id'] ?? 0 ?>)"
                        data-plantao-id="<?= $plantao['id'] ?? 0 ?>">
                    <i class="ti ti-pencil"></i> Assinar
                </button>
                <?php endif; ?>
            </div>

            <!-- Sinais Vitais -->
            <?php if (!empty($plantao['sinais_vitais'] ?? [])): ?>
            <div class="rp-section rp-section--vitals">
                <div class="rp-section__header">
                    <i class="ti ti-heartbeat"></i>
                    <span>Sinais vitais</span>
                </div>
                <div class="rp-grid-2">
                    <?php foreach ($plantao['sinais_vitais'] as $sinal): ?>
                    <div class="rp-vital-card rp-vital-card--<?= htmlspecialchars($sinal['status'] ?? 'normal') ?>">
                        <div class="rp-vital-card__label">
                            <?= htmlspecialchars($sinal['label']) ?>
                        </div>
                        <div class="rp-vital-card__value">
                            <?= htmlspecialchars($sinal['valor']) ?>
                            <span class="rp-vital-card__unit">
                                <?= htmlspecialchars($sinal['unidade']) ?>
                            </span>
                        </div>
                        <?php if (!empty($sinal['texto'])): ?>
                        <div class="rp-vital-card__text">
                            <?= htmlspecialchars($sinal['texto']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Medicações -->
            <?php if (!empty($plantao['medicacoes'] ?? [])): ?>
            <div class="rp-section rp-section--meds">
                <div class="rp-section__header">
                    <i class="ti ti-prescription"></i>
                    <span>Medicações</span>
                </div>
                <div class="rp-table-container">
                    <table class="rp-table">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Via</th>
                                <th>Horário</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plantao['medicacoes'] as $med): ?>
                            <tr>
                                <td>
                                    <div class="rp-table-cell rp-table-cell--name">
                                        <i class="ti ti-pill"></i>
                                        <?= htmlspecialchars($med['nome']) ?>
                                    </div>
                                </td>
                                <td class="rp-table-cell rp-table-cell--muted">
                                    <?= htmlspecialchars($med['via'] ?? '') ?>
                                </td>
                                <td class="rp-table-cell rp-table-cell--muted">
                                    <?= htmlspecialchars($med['horario'] ?? '') ?>
                                </td>
                                <td>
                                    <span class="rp-badge rp-badge--<?= htmlspecialchars($med['status'] ?? 'pendente') ?>">
                                        <?= $med['status'] === 'administrado' ? 'Administrado' : 'Pendente' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Evolução -->
            <?php if (!empty($plantao['evolucao'] ?? '')): ?>
            <div class="rp-section rp-section--evolucao">
                <div class="rp-section__header">
                    <i class="ti ti-document"></i>
                    <span>Evolução de enfermagem</span>
                    <span class="rp-section__hint">SOAP</span>
                </div>
                <div class="rp-text-content">
                    <?= nl2br(htmlspecialchars($plantao['evolucao'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Intercorrências -->
            <?php if (!empty($plantao['intercorrencias'] ?? [])): ?>
            <div class="rp-section rp-section--alerts">
                <div class="rp-section__header">
                    <i class="ti ti-alert-triangle"></i>
                    <span>Intercorrências</span>
                </div>
                <div class="rp-alerts-list">
                    <?php foreach ($plantao['intercorrencias'] as $inter): ?>
                    <div class="rp-alert-item">
                        <div class="rp-alert-item__icon">
                            <i class="ti ti-alert-circle"></i>
                        </div>
                        <div class="rp-alert-item__content">
                            <div class="rp-alert-item__text">
                                <?= htmlspecialchars($inter['descricao']) ?>
                            </div>
                            <div class="rp-alert-item__time">
                                🕐 <?= htmlspecialchars($inter['horario'] ?? '') ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Responsável -->
            <div class="rp-footer rp-footer--signed">
                <div class="rp-footer__content">
                    <?php
                    $partes  = explode(' ', $plantao['enfermeiro'] ?? 'N/A');
                    $iniciais = strtoupper(($partes[0][0] ?? '') . ($partes[1][0] ?? ''));
                    ?>
                    <div class="rp-footer__avatar">
                        <?= htmlspecialchars($iniciais) ?>
                    </div>
                    <div class="rp-footer__info">
                        <div class="rp-footer__name">
                            <?= htmlspecialchars($plantao['enfermeiro'] ?? 'Não informado') ?>
                        </div>
                        <div class="rp-footer__coren">
                            <?= htmlspecialchars($plantao['coren'] ?? '') ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Estado vazio -->
        <div class="rp-empty-state">
            <div class="rp-empty-state__icon">
                <i class="ti ti-calendar-x"></i>
            </div>
            <div class="rp-empty-state__title">
                Nenhum relatório de plantão encontrado
            </div>
            <div class="rp-empty-state__text">
                Selecione um paciente diferente ou verifique se há dados cadastrados
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
function assinarPlantao(plantaoId) {
    if (!plantaoId || plantaoId <= 0) {
        alert('ID do plantão inválido');
        return;
    }

    if (!confirm('Deseja assinar este plantão? Esta ação não pode ser desfeita.')) {
        return;
    }

    // Confirmar ação via modal
    alert('Assinatura do plantão ID ' + plantaoId + ' (ação confirmada)');
    // TODO: Implementar chamada AJAX real
}

document.addEventListener('DOMContentLoaded', function() {
    // Adicionar event listeners para botões de assinatura
    const botaoAssinar = document.querySelector('.rp-btn--primary');
    if (botaoAssinar) {
        botaoAssinar.addEventListener('click', function() {
            const plantaoId = this.getAttribute('data-plantao-id');
            if (plantaoId) {
                assinarPlantao(parseInt(plantaoId));
            }
        });
    }
});
</script>
<?php $this->stop() ?>