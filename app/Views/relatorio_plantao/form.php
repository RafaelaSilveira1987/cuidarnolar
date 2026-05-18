<?php
/**
 * app/Views/relatorio_plantao/form.php
 *
 * Incluído por create.php via require.
 * Todas as variáveis chegam via extract() — sem $this.
 * ZERO warnings garantido: todo acesso usa ?? antes de operar.
 */

// ── Fallbacks seguros ─────────────────────────────────────────
$pac        = (isset($paciente)   && is_array($paciente))   ? $paciente   : [];
$meds       = (isset($medicacoes) && is_array($medicacoes)) ? $medicacoes : [];
$pacientes  = (isset($pacientes)  && is_array($pacientes))  ? $pacientes  : [];
$cuidadores = (isset($cuidadores) && is_array($cuidadores)) ? $cuidadores : [];
$enf        = (isset($enfermeiro) && is_array($enfermeiro)) ? $enfermeiro : [];
$turnoAtual = isset($turno_atual) ? (string)$turno_atual    : 'plantao_24h';
$csrfToken  = isset($csrf) ? $csrf : (isset($_csrf) ? $_csrf : '');
$pacSel     = isset($pacienteSelecionado) ? $pacienteSelecionado : null;

// Campos adaptativos — ?? false nunca gera "Undefined array key"
$temDiabetes = !empty($pac['tem_diabetes']);
$acamado     = !empty($pac['acamado']);

// Turnos (substituem manhã/tarde/noite)
$turnosConfig = [
    'plantao_24h' => '24 horas',
    'plantao_12h' => '12 horas',
    'plantao_8h'  => '8 horas',
    'plantao_6h'  => '6 horas',
];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_form.css">

<form id="form-plantao" method="POST" action="<?= BASE_URL ?>/relatorio-plantao/store">

    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="turno" value="<?= htmlspecialchars($turnoAtual) ?>" id="input-turno">
    <input type="hidden" name="paciente_id" value="<?= (int)($pac['id'] ?? 0) ?>" id="input-paciente-id">

    <div class="rp-wrapper">

        <!-- ══ CABEÇALHO ══════════════════════════════════════════ -->
        <div class="rp-header">
            <div class="rp-header__patient">
                <div class="rp-avatar">
                    <?= htmlspecialchars($pac['iniciais'] ?? '?') ?>
                </div>
                <div class="rp-patient-info">
                    <div class="rp-patient-info__name">
                        <?= ($pac['nome'] ?? '') !== ''
                            ? htmlspecialchars($pac['nome'])
                            : 'Selecione um paciente abaixo' ?>
                    </div>
                    <div class="rp-patient-info__meta">
                        <?php if (!empty($pac['nome'])): ?>
                        <span>Prontuário #<?= htmlspecialchars((string)($pac['prontuario'] ?? '')) ?></span>
                        <span><?= (int)($pac['idade'] ?? 0) ?> anos</span>
                        <span><?= htmlspecialchars($pac['diagnostico'] ?? '') ?></span>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <!-- Seletor de duração -->
            <div class="turno-selector">
                <?php foreach ($turnosConfig as $key => $label): ?>
                <button type="button" class="turno-pill<?= $key === $turnoAtual ? ' active' : '' ?>"
                    data-turno="<?= $key ?>">
                    <?= htmlspecialchars($label) ?>
                </button>
                <?php endforeach ?>
            </div>
        </div>

        <!-- ══ DADOS DO PLANTÃO ════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-calendar" aria-hidden="true"></i> Dados do plantão
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="sel-paciente">Paciente</label>
                    <select id="sel-paciente" name="paciente_id" required>
                        <option value="">Selecione o paciente...</option>
                        <?php foreach ($pacientes as $p):
                            $pid   = (int)($p['id'] ?? 0);
                            $pnome = $p['nome_completo'] ?? $p['nome'] ?? '';
                            $sel   = ((!empty($pac['id']) && (int)$pac['id'] === $pid)
                                     || (!empty($pacSel['id']) && (int)$pacSel['id'] === $pid))
                                     ? ' selected' : '';
                        ?>
                        <option value="<?= $pid ?>" <?= $sel ?>>
                            <?= htmlspecialchars($pnome) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sel-cuidador">Cuidador responsável</label>
                    <select id="sel-cuidador" name="cuidador_id">
                        <option value="">Selecione o cuidador...</option>
                        <?php foreach ($cuidadores as $c): ?>
                        <option value="<?= (int)($c['id'] ?? 0) ?>">
                            <?= htmlspecialchars($c['nome_completo'] ?? $c['nome'] ?? '') ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="data-inicio">Início do plantão</label>
                    <input type="datetime-local" id="data-inicio" name="data_inicio" class="sinal-field__input"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="data-fim">Fim do plantão</label>
                    <input type="datetime-local" id="data-fim" name="data_fim" class="sinal-field__input">
                </div>
            </div>
        </div>

        <!-- ══ SINAIS VITAIS ═══════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-activity" aria-hidden="true"></i> Sinais vitais
            </div>

            <div class="sinais-grid-form">
                <div class="sinal-field">
                    <label class="sinal-field__label" for="sv-pa">Pressão arterial</label>
                    <input class="sinal-field__input" type="text" id="sv-pa" name="sv_pa" placeholder="ex: 120/80"
                        autocomplete="off">
                    <span class="sinal-field__unidade">mmHg</span>
                    <span class="sinal-badge hidden" id="badge-pa"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="sv-fc">Freq. cardíaca</label>
                    <input class="sinal-field__input" type="number" id="sv-fc" name="sv_fc" placeholder="bpm" min="0"
                        max="300">
                    <span class="sinal-field__unidade">bpm</span>
                    <span class="sinal-badge hidden" id="badge-fc"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="sv-temp">Temperatura</label>
                    <input class="sinal-field__input" type="number" step="0.1" id="sv-temp" name="sv_temp"
                        placeholder="°C" min="30" max="45">
                    <span class="sinal-field__unidade">°C</span>
                    <span class="sinal-badge hidden" id="badge-temp"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="sv-spo2">SpO₂</label>
                    <input class="sinal-field__input" type="number" id="sv-spo2" name="sv_spo2" placeholder="%" min="50"
                        max="100">
                    <span class="sinal-field__unidade">%</span>
                    <span class="sinal-badge hidden" id="badge-spo2"></span>
                </div>

                <?php if ($temDiabetes): ?>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="sv-hgt">
                        Glicemia (HGT) <span class="field-tag">DM</span>
                    </label>
                    <input class="sinal-field__input" type="number" id="sv-hgt" name="sv_hgt" placeholder="mg/dL"
                        min="0" max="600">
                    <span class="sinal-field__unidade">mg/dL</span>
                    <span class="sinal-badge hidden" id="badge-hgt"></span>
                </div>
                <?php endif ?>
            </div>

            <div class="form-group" style="margin-top:1.25rem">
                <label class="form-label">Nível de consciência</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Lúcido e orientado','Confuso','Sonolento','Não responsivo'] as $c): ?>
                    <label class="check-opt">
                        <input type="radio" name="consciencia" value="<?= htmlspecialchars($c) ?>">
                        <?= htmlspecialchars($c) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nível de dor (0 = sem dor · 10 = máxima)</label>
                <div class="dor-row">
                    <input type="range" id="dor-range" name="nivel_dor" min="0" max="10" step="1" value="0">
                    <span id="dor-val" class="dor-val">0</span>
                    <span class="sinal-badge badge-ok" id="dor-badge">Sem dor</span>
                </div>
            </div>
        </div>

        <!-- ══ ROTINA E CUIDADOS ═══════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-heart" aria-hidden="true"></i> Rotina e cuidados
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="alimentacao">Aceitação alimentar</label>
                    <select id="alimentacao" name="alimentacao">
                        <option value="">Selecionar...</option>
                        <?php foreach ([
                            'Aceitou bem todas as refeições',
                            'Aceitou parcialmente',
                            'Recusou alimentação',
                            'Dieta via sonda — infundida',
                            'Jejum prescrito',
                        ] as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>"><?= htmlspecialchars($opt) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="hidratacao">Hidratação (ml)</label>
                    <input type="number" id="hidratacao" name="hidratacao_ml" placeholder="ex: 500" min="0" max="5000"
                        class="sinal-field__input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Higiene</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Banho de chuveiro','Banho no leito','Higiene parcial','Troca de fraldas','Não realizado'] as $h): ?>
                    <label class="check-opt">
                        <input type="radio" name="higiene" value="<?= htmlspecialchars($h) ?>">
                        <?= htmlspecialchars($h) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Eliminações</label>
                <div class="check-group" data-mode="multi">
                    <?php foreach (['Diurese normal','Evacuação normal','Incontinência urinária','Incontinência fecal','Sem eliminações no turno'] as $e): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="eliminacoes[]" value="<?= htmlspecialchars($e) ?>">
                        <?= htmlspecialchars($e) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if ($acamado): ?>
            <div class="form-group">
                <label class="form-label">
                    Mudança de decúbito <span class="field-tag">Acamado</span>
                </label>
                <div class="check-group" data-mode="multi">
                    <?php foreach (['D.D. → D.L.D.','D.L.D. → D.L.E.','D.L.E. → D.D.','Semi-fowler','Fowler'] as $d): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="decubito[]" value="<?= htmlspecialchars($d) ?>">
                        <?= htmlspecialchars($d) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
            <?php endif ?>

            <div class="form-group">
                <label class="form-label">Sono / repouso</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Dormiu bem','Sono fragmentado','Insônia / agitação','Turno diurno'] as $s): ?>
                    <label class="check-opt">
                        <input type="radio" name="sono" value="<?= htmlspecialchars($s) ?>">
                        <?= htmlspecialchars($s) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

        <!-- ══ MEDICAÇÕES ══════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-pill" aria-hidden="true"></i> Medicações do plantão
            </div>
            <?php if (!empty($meds)): ?>
            <div class="med-list">
                <?php foreach ($meds as $i => $med): ?>
                <input type="hidden" name="medicacoes[<?= $i ?>][id]" value="<?= (int)($med['id'] ?? 0) ?>">
                <input type="hidden" name="medicacoes[<?= $i ?>][status]" id="med-status-<?= $i ?>" value="pendente">
                <div class="med-row">
                    <span class="med-time"><?= htmlspecialchars($med['horario'] ?? '') ?></span>
                    <span class="med-name">
                        <?= htmlspecialchars($med['nome'] ?? '') ?>
                        <span class="med-via">— <?= htmlspecialchars($med['via'] ?? '') ?></span>
                    </span>
                    <button type="button" class="med-status-btn" data-index="<?= $i ?>" onclick="toggleMed(this)">
                        Pendente
                    </button>
                </div>
                <?php endforeach ?>
            </div>
            <?php else: ?>
            <p class="empty-note">
                <i class="ti ti-info-circle" aria-hidden="true"></i>
                Nenhuma medicação cadastrada. Registre na evolução se necessário.
            </p>
            <?php endif ?>
        </div>

        <!-- ══ EVOLUÇÃO ════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-notes" aria-hidden="true"></i>
                Evolução de enfermagem
                <span class="evolucao-hint">SOAP</span>
            </div>
            <textarea id="evolucao" name="evolucao" class="evolucao-textarea" rows="5"
                placeholder="Descreva o estado geral do paciente, condutas adotadas e observações relevantes..."></textarea>
        </div>

        <!-- ══ INTERCORRÊNCIAS ═════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i> Intercorrências
            </div>

            <div id="inter-list"></div>

            <label class="sem-inter-label">
                <input type="checkbox" id="sem-inter-chk" name="sem_intercorrencias" value="1"
                    onchange="toggleSemInter(this)">
                Turno sem intercorrências
            </label>

            <button type="button" class="btn-add-inter" id="btn-add-inter" onclick="addInter()">
                <i class="ti ti-plus" aria-hidden="true"></i> Adicionar intercorrência
            </button>
        </div>

        <!-- ══ RODAPÉ ══════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-footer">
                <div class="rp-footer__person">
                    <?php
                    $nomeEnf = $enf['nome'] ?? '';
                    $partes  = array_filter(explode(' ', trim($nomeEnf)));
                    $iniciais = implode('', array_map(
                        fn($p) => mb_strtoupper(mb_substr($p, 0, 1)),
                        array_slice(array_values($partes), 0, 2)
                    )) ?: '?';
                    ?>
                    <div class="rp-footer__avatar"><?= htmlspecialchars($iniciais) ?></div>
                    <div>
                        <div class="rp-footer__name"><?= htmlspecialchars($nomeEnf) ?></div>
                        <div class="rp-footer__coren">
                            <?= htmlspecialchars($enf['coren'] ?? '') ?>
                        </div>
                    </div>
                </div>

                <div class="footer-actions">
                    <a href="<?= BASE_URL ?>/relatorio-plantao" class="btn-cancelar">Cancelar</a>
                    <button type="submit" name="acao" value="salvar" class="btn-salvar">
                        <i class="ti ti-device-floppy" aria-hidden="true"></i> Salvar rascunho
                    </button>
                    <button type="submit" name="acao" value="assinar" class="btn-assinar" id="btn-assinar">
                        <i class="ti ti-pen" aria-hidden="true"></i> Assinar e finalizar
                    </button>
                </div>
            </div>
        </div>

    </div><!-- /.rp-wrapper -->
</form>

<script>
window.FORM_CONFIG = {
    turnoAtual: <?= json_encode($turnoAtual,    JSON_UNESCAPED_UNICODE) ?>,
    turnos: <?= json_encode($turnosConfig,  JSON_UNESCAPED_UNICODE) ?>,
    interCount: 0,
};
</script>
<script src="<?= BASE_URL ?>/assets/js/relatorio_plantao_form.js"></script>