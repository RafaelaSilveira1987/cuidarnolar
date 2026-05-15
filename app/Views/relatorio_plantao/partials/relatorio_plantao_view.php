<?php

/**
 * views/relatorio_plantao/index.php
 *
 * Tela de Relatório de Plantão — períodos livres (sem turno fixo)
 * Inclua esta view via seu controller/router habitual.
 *
 * Variáveis esperadas:
 *   $paciente   array  — dados do paciente
 *   $relatorios array  — lista de períodos do dia (ordem cronológica)
 *                        cada item segue a estrutura de $mockRelatorios abaixo
 */

// ------------------------------------------------------------------
// Mock: remova ao integrar com o controller real
// ------------------------------------------------------------------
require_once __DIR__ . '/../../data/mock_plantao.php';
$paciente   = $mockPaciente;
$relatorios = array_values($mockRelatorios); // já ordenado por hora_inicio
// ------------------------------------------------------------------

$dataAtual = date('D, d M Y');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Plantão — <?= htmlspecialchars($paciente['nome']) ?></title>
    <link rel="stylesheet" href="/assets/css/relatorio_plantao.css">
</head>

<body>
    <div class="rp-wrapper">

        <!-- ================================================================
         CABEÇALHO DO PACIENTE
         ================================================================ -->
        <div class="rp-header">
            <div class="rp-header__patient">
                <div class="rp-avatar"><?= htmlspecialchars($paciente['iniciais']) ?></div>
                <div>
                    <div class="rp-patient-info__name"><?= htmlspecialchars($paciente['nome']) ?></div>
                    <div class="rp-patient-info__meta">
                        <span>Prontuário #<?= htmlspecialchars($paciente['prontuario']) ?></span>
                        <span><?= htmlspecialchars($paciente['idade']) ?> anos</span>
                        <span><?= htmlspecialchars($paciente['diagnostico']) ?></span>
                    </div>
                </div>
            </div>

            <div class="rp-date-nav">
                <button class="rp-date-nav__btn" id="rp-data-prev" title="Dia anterior">&#8249;</button>
                <span class="rp-date-nav__label" id="rp-data-label"><?= $dataAtual ?></span>
                <button class="rp-date-nav__btn" id="rp-data-next" title="Próximo dia">&#8250;</button>
            </div>
        </div>

        <!-- Tabs de navegação -->
        <div class="rp-tabs">
            <button class="rp-tab active">📋 Relatório de plantão</button>
            <button class="rp-tab">📅 Histórico</button>
            <button class="rp-tab">💊 Prescrição</button>
        </div>

        <!-- ================================================================
         CARDS DE PERÍODO (livres, sem turno fixo)
         ================================================================ -->
        <div class="rp-periodos-header">
            <span class="rp-periodos-label">Períodos do dia</span>
            <button class="btn-add-periodo" id="btn-add-periodo" title="Adicionar período">
                + Adicionar período
            </button>
        </div>

        <div class="rp-periodos" id="rp-periodos">
            <?php foreach ($relatorios as $idx => $rel): ?>
            <div class="periodo-card" data-id="<?= $idx ?>">
                <div class="periodo-card__top">
                    <span class="periodo-card__horario">
                        <?= htmlspecialchars($rel['hora_inicio']) ?> - <?= htmlspecialchars($rel['hora_fim']) ?>
                    </span>
                    <span class="periodo-card__duracao">
                        <?= htmlspecialchars($rel['duracao_label']) ?>
                    </span>
                </div>
                <div class="periodo-card__enfermeiro">
                    <span class="periodo-card__enf-avatar">EN</span>
                    <span class="periodo-card__enf-nome">
                        <?= htmlspecialchars($rel['enfermeiro']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Card de placeholder para adicionar novo período -->
            <div class="periodo-card periodo-card--add" id="card-add-periodo">
                <div class="periodo-card__add-icon">+</div>
                <div class="periodo-card__add-text">Novo período</div>
            </div>
        </div>

        <!-- ================================================================
         CONTEÚDO DO PERÍODO SELECIONADO
         ================================================================ -->
        <div id="rp-conteudo">

            <!-- Sinais Vitais -->
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
                <div class="evolucao-text" id="rp-evolucao-text"></div>
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

            <!-- Responsável pelo plantão -->
            <div class="rp-section">
                <div class="rp-section__title">
                    <span>👤</span> Responsável pelo plantão
                </div>
                <div id="rp-footer-area">
                    <!-- renderizado pelo JS -->
                </div>
            </div>

        </div><!-- /#rp-conteudo -->

        <!-- Estado vazio (antes de selecionar qualquer card) -->
        <div class="rp-empty" id="rp-empty">
            <div class="rp-empty__icon">📋</div>
            <div class="rp-empty__text">Selecione um período acima para visualizar o relatório</div>
        </div>

    </div><!-- /.rp-wrapper -->

    <!-- ================================================================
     MODAL — Novo período
     ================================================================ -->
    <div class="rp-modal-overlay" id="modal-periodo" aria-modal="true" role="dialog" aria-label="Adicionar período">
        <div class="rp-modal">
            <div class="rp-modal__header">
                <h2 class="rp-modal__title">Novo período de plantão</h2>
                <button class="rp-modal__close" id="modal-close" title="Fechar">&times;</button>
            </div>

            <div class="rp-modal__body">
                <div class="form-row">
                    <label for="hora-inicio">Hora início</label>
                    <input type="time" id="hora-inicio" name="hora_inicio">
                </div>
                <div class="form-row">
                    <label for="hora-fim">Hora fim</label>
                    <input type="time" id="hora-fim" name="hora_fim">
                </div>
                <div class="form-row">
                    <label for="enfermeiro">Nome do cuidador</label>
                    <input type="text" id="enfermeiro" name="enfermeiro">
                </div>
                <div class="form-row">
                    <label for="coren">COREN</label>
                    <input type="text" id="coren" name="coren">
                </div>
                <div id="modal-duracao-preview" class="modal-duracao-preview" style="display:none"></div>
            </div>

            <div class="rp-modal__footer">
                <button class="btn-cancel" id="modal-cancelar">Cancelar</button>
                <button class="btn-salvar" id="modal-salvar">Adicionar período</button>
            </div>
        </div>
    </div>

    <!-- Dados injetados pelo PHP para o JS -->
    <script>
    window.PLANTAO_DATA = <?= json_encode(array_values($relatorios), JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="/assets/js/relatorio_plantao.js"></script>
</body>

</html>