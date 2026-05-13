<?php
/**
 * app/Views/relatorio_plantao/show.php
 *
 * View principal do Relatório de Plantão.
 * Recebe do controller:
 *   $title       — string
 *   $paciente    — array|null  (nome, prontuario, diagnostico, iniciais, idade)
 *   $plantaoData — string JSON (window.PLANTAO_DATA)
 *   $dataAtual   — string Y-m-d
 *   $pacienteId  — int
 */
?>
<?php $this->layout('layouts/main', ['title' => $title]) ?>

<!-- CSS da página -->
<link rel="stylesheet" href="/assets/css/relatorio_plantao.css">

<div class="rp-wrapper">

    <?php if ($paciente): ?>

    <!-- ======================================================
         CABEÇALHO DO PACIENTE
         ====================================================== -->
    <div class="rp-header">
        <div class="rp-header__patient">
            <div class="rp-avatar"><?= htmlspecialchars($paciente['iniciais']) ?></div>
            <div class="rp-patient-info">
                <div class="rp-patient-info__name"><?= htmlspecialchars($paciente['nome']) ?></div>
                <div class="rp-patient-info__meta">
                    <span>Prontuário #<?= htmlspecialchars($paciente['prontuario']) ?></span>
                    <span><?= (int) $paciente['idade'] ?> anos</span>
                    <span><?= htmlspecialchars($paciente['diagnostico']) ?></span>
                </div>
            </div>
        </div>

        <!-- Navegação de data -->
        <div class="rp-date-nav">
            <button class="rp-date-nav__btn" id="rp-data-prev" title="Dia anterior">&#8249;</button>
            <span class="rp-date-nav__label" id="rp-data-label"></span>
            <button class="rp-date-nav__btn" id="rp-data-next" title="Próximo dia">&#8250;</button>
        </div>
    </div>

    <!-- ======================================================
         TABS DE NAVEGAÇÃO
         ====================================================== -->
    <div class="rp-tabs">
        <button class="rp-tab active">📋 Relatório de plantão</button>
        <button class="rp-tab">📁 Histórico</button>
        <button class="rp-tab">💊 Prescrição</button>
    </div>

    <!-- ======================================================
         CARDS DE TURNO
         ====================================================== -->
    <?php
    $turnosConfig = [
        'manha' => ['label' => 'Manhã',  'icone' => '☀️'],
        'tarde' => ['label' => 'Tarde',  'icone' => '🌤️'],
        'noite' => ['label' => 'Noite',  'icone' => '🌙'],
    ];

    // Decodifica para saber quais turnos existem
    $dadosTurnos = json_decode($plantaoData, true) ?: [];
    ?>

    <div class="rp-turnos">
        <?php foreach ($turnosConfig as $chave => $cfg): ?>
            <?php
            $turno      = $dadosTurnos[$chave] ?? null;
            $temDados   = $turno !== null;
            $status     = $turno['status']       ?? 'andamento';
            $enfermeiro = $turno['enfermeiro']    ?? '—';
            $horario    = $turno['horario']       ?? ($chave === 'manha' ? '07:00 – 13:00' : ($chave === 'tarde' ? '13:00 – 19:00' : '19:00 – 07:00'));
            $statusLabel= $turno['status_label']  ?? '';

            // Define se este turno deve ser pré-selecionado
            // Lógica: turno "andamento" tem prioridade; fallback para o último disponível
            $autoSelect = $temDados && $status === 'andamento' ? 'true' : 'false';
            ?>
            <div class="turno-card<?= !$temDados ? ' turno-card--vazio' : '' ?>"
                 data-turno="<?= $chave ?>"
                 <?= $temDados ? '' : 'style="opacity:.45;pointer-events:none"' ?>
                 data-auto-select="<?= $autoSelect ?>">

                <div class="turno-card__top">
                    <div class="turno-card__label">
                        <?= $cfg['icone'] ?> <?= $cfg['label'] ?>
                    </div>
                    <?php if ($temDados): ?>
                        <span class="turno-card__badge badge--<?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($statusLabel) ?>
                        </span>
                    <?php endif ?>
                </div>

                <div class="turno-card__enfermeiro">
                    Enf. <?= htmlspecialchars($enfermeiro) ?>
                </div>
                <div class="turno-card__horario"><?= htmlspecialchars($horario) ?></div>
            </div>
        <?php endforeach ?>
    </div>

    <!-- ======================================================
         CONTEÚDO DO TURNO (preenchido pelo JS)
         ====================================================== -->
    <div id="rp-conteudo">

        <!-- Sinais vitais -->
        <div class="rp-section">
            <div class="rp-section__title">
                <span>🩺</span> Sinais vitais
            </div>
            <div class="sinais-grid" id="rp-sinais-vitais">
                <!-- renderizado pelo JS -->
            </div>
        </div>

        <!-- Medicações -->
        <div class="rp-section">
            <div class="rp-section__title">
                <span>💊</span> Medicações do turno
            </div>
            <div id="rp-medicacoes">
                <!-- renderizado pelo JS -->
            </div>
        </div>

        <!-- Evolução de enfermagem -->
        <div class="rp-section">
            <div class="rp-section__title">
                <span>📝</span> Evolução de enfermagem
                <span class="evolucao-hint">SOAP</span>
            </div>
            <div class="evolucao-text" id="rp-evolucao-text">
                <!-- renderizado pelo JS -->
            </div>
        </div>

        <!-- Intercorrências -->
        <div class="rp-section">
            <div class="rp-section__title">
                <span>⚠</span> Intercorrências
            </div>
            <div id="rp-intercorrencias-lista">
                <!-- renderizado pelo JS -->
            </div>
        </div>

        <!-- Rodapé / assinatura -->
        <div class="rp-section">
            <div class="rp-section__title">
                <span>👤</span> Responsável pelo plantão
            </div>
            <div id="rp-footer-area">
                <!-- renderizado pelo JS -->
            </div>
        </div>

    </div><!-- /#rp-conteudo -->

    <?php else: ?>

    <!-- Estado vazio: nenhum relatório para esta data -->
    <div class="rp-empty">
        <div class="rp-empty__icon">📋</div>
        <div class="rp-empty__text">Nenhum relatório de plantão encontrado para esta data.</div>
    </div>

    <?php endif ?>

</div><!-- /.rp-wrapper -->

<!-- Injeta os dados do PHP no JS -->
<script>
    window.PLANTAO_DATA = <?= $plantaoData ?>;
    window.PLANTAO_DATA_ATUAL = <?= json_encode($dataAtual) ?>;
    window.PLANTAO_PACIENTE_ID = <?= (int) $pacienteId ?>;
</script>

<!-- JS da página -->
<script src="/assets/js/relatorio_plantao.js"></script>
