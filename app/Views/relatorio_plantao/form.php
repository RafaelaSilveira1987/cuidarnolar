<?php
/**
 * Formulario de relatorio de plantao.
 *
 * Incluido por create.php e edit.php.
 */

$pac        = (isset($paciente) && is_array($paciente)) ? $paciente : [];
$meds       = (isset($medicacoes) && is_array($medicacoes)) ? $medicacoes : [];
$pacientes  = (isset($pacientes) && is_array($pacientes)) ? $pacientes : [];
$cuidadores = (isset($cuidadores) && is_array($cuidadores)) ? $cuidadores : [];
$enf        = (isset($enfermeiro) && is_array($enfermeiro)) ? $enfermeiro : [];
$rel        = (isset($relatorio) && is_array($relatorio)) ? $relatorio : [];
$pacSel     = isset($pacienteSelecionado) ? $pacienteSelecionado : null;
$csrfToken  = isset($csrf) ? $csrf : (isset($_csrf) ? $_csrf : '');
$turnoAtual = isset($turno_atual) ? (string) $turno_atual : 'plantao_24h';

$isEdit = !empty($rel['uuid']);
$pacienteUuid = (string)($pacSel['uuid'] ?? $pac['uuid'] ?? '');
$formAction = $isEdit
    ? BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode((string)$rel['uuid']) . '/atualizar'
    : ($pacienteUuid !== ''
        ? BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid) . '/store'
        : BASE_URL . '/relatorio-plantao');

$temDiabetes = !empty($pac['tem_diabetes']) || ($rel['hgt'] ?? '') !== '';
$acamado = !empty($pac['acamado']);

$turnosConfig = [
    'plantao_24h' => '24 horas',
    'plantao_12h' => '12 horas',
    'plantao_8h'  => '8 horas',
    'plantao_6h'  => '6 horas',
];

$fmtDateTimeLocal = static function (?string $value): string {
    if (!$value) {
        return '';
    }

    $ts = strtotime($value);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
};

$old = static fn(string $key, mixed $default = ''): string => htmlspecialchars((string)($_POST[$key] ?? $default));
$relValue = static fn(string $key, mixed $default = ''): string => htmlspecialchars((string)($rel[$key] ?? $default));

$decodeList = static function (mixed $value): array {
    if (is_array($value)) {
        return $value;
    }

    if (!is_string($value) || trim($value) === '') {
        return [];
    }

    $decoded = json_decode($value, true);
    if (is_array($decoded)) {
        return $decoded;
    }

    return [$value];
};

$selectedList = static function (string $key) use ($decodeList, $rel): array {
    return $decodeList($rel[$key] ?? []);
};

$isChecked = static function (string $name, string $value, mixed $current): string {
    if (is_array($current)) {
        return in_array($value, $current, true) ? ' checked' : '';
    }

    return (string)$current === $value ? ' checked' : '';
};
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_form.css">

<form id="relatorio-form" method="POST" action="<?= htmlspecialchars($formAction) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="turno" value="<?= htmlspecialchars($turnoAtual) ?>" id="input-turno">
    <input type="hidden" name="paciente_id" value="<?= (int)($pac['id'] ?? $rel['paciente_id'] ?? 0) ?>">
    <input type="hidden" name="status" value="rascunho">

    <div class="rp-wrapper">
        <div class="rp-header">
            <div class="rp-header__patient">
                <div class="rp-avatar"><?= htmlspecialchars($pac['iniciais'] ?? '?') ?></div>
                <div class="rp-patient-info">
                    <div class="rp-patient-info__name">
                        <?= ($pac['nome'] ?? '') !== '' ? htmlspecialchars($pac['nome']) : 'Selecione um paciente abaixo' ?>
                    </div>
                    <div class="rp-patient-info__meta">
                        <?php if (!empty($pac['nome'])): ?>
                        <span>Prontuario #<?= htmlspecialchars((string)($pac['prontuario'] ?? '')) ?></span>
                        <span><?= (int)($pac['idade'] ?? 0) ?> anos</span>
                        <span><?= htmlspecialchars($pac['diagnostico'] ?? '') ?></span>
                        <?php endif ?>
                    </div>
                </div>
            </div>

            <div class="turno-selector">
                <?php foreach ($turnosConfig as $key => $label): ?>
                <button type="button" class="turno-pill<?= $key === $turnoAtual ? ' active' : '' ?>"
                    data-turno="<?= $key ?>">
                    <?= htmlspecialchars($label) ?>
                </button>
                <?php endforeach ?>
            </div>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-calendar" aria-hidden="true"></i> Dados do plantao
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="sel-paciente">Paciente</label>
                    <select id="sel-paciente" name="paciente_id" required>
                        <option value="">Selecione o paciente...</option>
                        <?php foreach ($pacientes as $p):
                            $pid = (int)($p['id'] ?? 0);
                            $pnome = $p['nome_completo'] ?? $p['nome'] ?? '';
                            $sel = ((int)($pac['id'] ?? $rel['paciente_id'] ?? 0) === $pid) ? ' selected' : '';
                        ?>
                        <option value="<?= $pid ?>" <?= $sel ?>><?= htmlspecialchars($pnome) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="sel-cuidador">Cuidador responsavel</label>
                    <select id="sel-cuidador" name="cuidador_id">
                        <option value="">Selecione o cuidador...</option>
                        <?php foreach ($cuidadores as $c):
                            $cid = (int)($c['id'] ?? 0);
                            $sel = ((int)($rel['cuidador_id'] ?? 0) === $cid) ? ' selected' : '';
                        ?>
                        <option value="<?= $cid ?>" <?= $sel ?>>
                            <?= htmlspecialchars($c['nome_completo'] ?? $c['nome'] ?? '') ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="data-inicio">Inicio do plantao</label>
                    <input type="datetime-local" id="data-inicio" name="data_inicio" class="sinal-field__input"
                        value="<?= htmlspecialchars($fmtDateTimeLocal($rel['data_inicio'] ?? null)) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="data-fim">Fim do plantao</label>
                    <input type="datetime-local" id="data-fim" name="data_fim" class="sinal-field__input"
                        value="<?= htmlspecialchars($fmtDateTimeLocal($rel['data_fim'] ?? null)) ?>">
                </div>
            </div>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-activity" aria-hidden="true"></i> Sinais vitais
            </div>

            <div class="sinais-grid-form">
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-pa">Pressao arterial</label>
                    <input class="sinal-field__input" type="text" id="inp-pa" name="pa" placeholder="ex: 120/80"
                        value="<?= $relValue('pa') ?>" oninput="avaliarSinal(this, 'pa')" autocomplete="off">
                    <span class="sinal-field__unidade">mmHg</span>
                    <span class="sinal-badge hidden" id="badge-pa"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-fc">Freq. cardiaca</label>
                    <input class="sinal-field__input" type="number" id="inp-fc" name="fc" placeholder="bpm" min="0"
                        max="300" value="<?= $relValue('fc') ?>" oninput="avaliarSinal(this, 'fc')">
                    <span class="sinal-field__unidade">bpm</span>
                    <span class="sinal-badge hidden" id="badge-fc"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-temp">Temperatura</label>
                    <input class="sinal-field__input" type="number" step="0.1" id="inp-temp" name="temperatura"
                        placeholder="C" min="30" max="45" value="<?= $relValue('temperatura') ?>"
                        oninput="avaliarSinal(this, 'temp')">
                    <span class="sinal-field__unidade">C</span>
                    <span class="sinal-badge hidden" id="badge-temp"></span>
                </div>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-spo2">SpO2</label>
                    <input class="sinal-field__input" type="number" id="inp-spo2" name="spo2" placeholder="%" min="50"
                        max="100" value="<?= $relValue('spo2') ?>" oninput="avaliarSinal(this, 'spo2')">
                    <span class="sinal-field__unidade">%</span>
                    <span class="sinal-badge hidden" id="badge-spo2"></span>
                </div>

                <?php if ($temDiabetes): ?>
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-hgt">Glicemia (HGT)</label>
                    <input class="sinal-field__input" type="number" id="inp-hgt" name="hgt" placeholder="mg/dL" min="0"
                        max="600" value="<?= $relValue('hgt') ?>" oninput="avaliarSinal(this, 'hgt')">
                    <span class="sinal-field__unidade">mg/dL</span>
                    <span class="sinal-badge hidden" id="badge-hgt"></span>
                </div>
                <?php endif ?>
            </div>

            <div class="form-group" style="margin-top:1.25rem">
                <label class="form-label">Nivel de consciencia</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Lucido e orientado','Confuso','Sonolento','Nao responsivo'] as $c): ?>
                    <label class="check-opt">
                        <input type="radio" name="consciencia" value="<?= htmlspecialchars($c) ?>"
                            <?= $isChecked('consciencia', $c, $rel['consciencia'] ?? '') ?>>
                        <?= htmlspecialchars($c) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nivel de dor (0 = sem dor, 10 = maxima)</label>
                <div class="dor-row">
                    <input type="range" id="dor-range" name="nivel_dor" min="0" max="10" step="1"
                        value="<?= (int)($rel['nivel_dor'] ?? 0) ?>" oninput="updateDor(this)">
                    <span id="dor-val" class="dor-val"><?= (int)($rel['nivel_dor'] ?? 0) ?></span>
                    <span class="sinal-badge badge-ok" id="dor-badge">Sem dor</span>
                </div>
            </div>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-heart" aria-hidden="true"></i> Rotina e cuidados
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="alimentacao">Aceitacao alimentar</label>
                    <select id="alimentacao" name="alimentacao">
                        <option value="">Selecionar...</option>
                        <?php foreach ([
                            'Aceitou bem todas as refeicoes',
                            'Aceitou parcialmente',
                            'Recusou alimentacao',
                            'Dieta via sonda - infundida',
                            'Jejum prescrito',
                        ] as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>"
                            <?= $isChecked('alimentacao', $opt, $rel['alimentacao'] ?? '') ?>>
                            <?= htmlspecialchars($opt) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="hidratacao">Hidratacao (ml)</label>
                    <input type="number" id="hidratacao" name="hidratacao_ml" placeholder="ex: 500" min="0" max="5000"
                        class="sinal-field__input" value="<?= $relValue('hidratacao_ml') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Higiene</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Banho de chuveiro','Banho no leito','Higiene parcial','Troca de fraldas','Nao realizado'] as $h): ?>
                    <label class="check-opt">
                        <input type="radio" name="higiene" value="<?= htmlspecialchars($h) ?>"
                            <?= $isChecked('higiene', $h, $rel['higiene'] ?? '') ?>>
                        <?= htmlspecialchars($h) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Eliminacoes</label>
                <div class="check-group" data-mode="multi">
                    <?php $eliminacoes = $selectedList('eliminacoes'); ?>
                    <?php foreach (['Diurese normal','Evacuacao normal','Incontinencia urinaria','Incontinencia fecal','Sem eliminacoes no turno'] as $e): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="eliminacoes[]" value="<?= htmlspecialchars($e) ?>"
                            <?= $isChecked('eliminacoes', $e, $eliminacoes) ?>>
                        <?= htmlspecialchars($e) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if ($acamado): ?>
            <div class="form-group">
                <label class="form-label">Mudanca de decubito</label>
                <div class="check-group" data-mode="multi">
                    <?php $decubito = $selectedList('decubito'); ?>
                    <?php foreach (['D.D. para D.L.D.','D.L.D. para D.L.E.','D.L.E. para D.D.','Semi-fowler','Fowler'] as $d): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="decubito[]" value="<?= htmlspecialchars($d) ?>"
                            <?= $isChecked('decubito', $d, $decubito) ?>>
                        <?= htmlspecialchars($d) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
            <?php endif ?>

            <div class="form-group">
                <label class="form-label">Sono / repouso</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Dormiu bem','Sono fragmentado','Insonia / agitacao','Turno diurno'] as $s): ?>
                    <label class="check-opt">
                        <input type="radio" name="sono" value="<?= htmlspecialchars($s) ?>"
                            <?= $isChecked('sono', $s, $rel['sono'] ?? '') ?>>
                        <?= htmlspecialchars($s) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-pill" aria-hidden="true"></i> Medicacoes do plantao
            </div>
            <?php if (!empty($meds)): ?>
            <div class="med-list">
                <?php foreach ($meds as $i => $med): ?>
                <input type="hidden" name="medicacoes[<?= $i ?>][id]" value="<?= (int)($med['id'] ?? 0) ?>">
                <input type="hidden" name="medicacoes[<?= $i ?>][status]" id="med-status-<?= $i ?>" value="pendente">
                <div class="med-row med-check-item">
                    <input type="hidden" name="medicacoes[<?= $i ?>][medicamento]"
                        value="<?= htmlspecialchars($med['nome'] ?? $med['nome_medicamento'] ?? '') ?>">
                    <?php $horariosMed = $med['horarios'] ?? $med['horario'] ?? ''; ?>
                    <input type="hidden" name="medicacoes[<?= $i ?>][horario]"
                        value="<?= htmlspecialchars($horariosMed) ?>">
                    <span class="med-time"><?= htmlspecialchars($horariosMed) ?></span>
                    <span class="med-name">
                        <?= htmlspecialchars($med['nome'] ?? $med['nome_medicamento'] ?? '') ?>
                        <?php if (!empty($med['dosagem'])): ?>
                            <span class="med-via">- <?= htmlspecialchars($med['dosagem']) ?></span>
                        <?php endif ?>
                        <span class="med-via">- <?= htmlspecialchars($med['via'] ?? '') ?></span>
                    </span>
                    <div class="med-toggle-group">
                        <button type="button" class="med-pill active"
                            onclick="setMedStatus(<?= $i ?>, 'pendente', this)">Pendente</button>
                        <button type="button" class="med-pill"
                            onclick="setMedStatus(<?= $i ?>, 'administrado', this)">Administrado</button>
                        <button type="button" class="med-pill"
                            onclick="setMedStatus(<?= $i ?>, 'recusado', this)">Recusado</button>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
            <?php else: ?>
            <p class="empty-note">
                <i class="ti ti-info-circle" aria-hidden="true"></i>
                Nenhuma medicacao cadastrada. Registre na evolucao se necessario.
            </p>
            <?php endif ?>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-notes" aria-hidden="true"></i> Evolucao de enfermagem
            </div>
            <textarea id="evolucao" name="evolucao" class="evolucao-textarea" rows="5"
                placeholder="Descreva o estado geral do paciente, condutas adotadas e observacoes relevantes..."><?= $relValue('evolucao') ?></textarea>
            <button type="button" class="btn btn-secondary" id="btn-gerar-evolucao" style="margin-top: .75rem">
                Gerar evolucao
            </button>
        </div>

        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i> Intercorrencias
            </div>

            <div id="inter-list">
                <?php foreach ($decodeList($rel['intercorrencias'] ?? []) as $idx => $inter):
                    $desc = is_array($inter) ? ($inter['descricao'] ?? '') : $inter;
                    if (trim((string)$desc) === '') {
                        continue;
                    }
                ?>
                <div class="inter-item">
                    <i class="ti ti-alert-triangle inter-icon"></i>
                    <textarea name="intercorrencias[<?= (int)$idx ?>][descricao]" class="rp-input inter-textarea"
                        rows="2"><?= htmlspecialchars((string)$desc) ?></textarea>
                    <button type="button" class="inter-remove" onclick="removeInter(this)">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <?php endforeach ?>
            </div>

            <label class="sem-inter-label">
                <input type="checkbox" id="sem-inter-chk" name="sem_intercorrencias" value="1"
                    onchange="toggleSemInter(this)">
                Turno sem intercorrencias
            </label>

            <button type="button" class="btn-add-inter" id="btn-add-inter" onclick="addInter()">
                <i class="ti ti-plus" aria-hidden="true"></i> Adicionar intercorrencia
            </button>
        </div>

        <div class="rp-section">
            <div class="rp-footer">
                <div class="rp-footer__person">
                    <?php
                    $nomeEnf = $enf['nome'] ?? '';
                    $partes = array_filter(explode(' ', trim($nomeEnf)));
                    $iniciais = implode('', array_map(
                        static fn($p) => strtoupper(substr($p, 0, 1)),
                        array_slice(array_values($partes), 0, 2)
                    )) ?: '?';
                    ?>
                    <div class="rp-footer__avatar"><?= htmlspecialchars($iniciais) ?></div>
                    <div>
                        <div class="rp-footer__name"><?= htmlspecialchars($nomeEnf) ?></div>
                        <div class="rp-footer__coren"><?= htmlspecialchars($enf['coren'] ?? '') ?></div>
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
    </div>
</form>

<script>
window.FORM_DATA = {
    interCount: <?= count($decodeList($rel['intercorrencias'] ?? [])) ?>,
    diagnostico: <?= json_encode($pac['diagnostico'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    cid: <?= json_encode($pac['cid'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    acamado: <?= $acamado ? 'true' : 'false' ?>
};
</script>
<script src="<?= BASE_URL ?>/assets/js/relatorio_plantao_form.js"></script>
