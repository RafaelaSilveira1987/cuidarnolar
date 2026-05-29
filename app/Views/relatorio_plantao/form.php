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

// UUID do paciente
$pacienteUuid = (string)($pacSel['uuid'] ?? $pac['uuid'] ?? '');

// Link de retorno
$voltarLink = $pacienteUuid !== ''
    ? BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid)
    : 'javascript:history.back()';

// ── Datas — calcular ANTES de qualquer output ─────────────────
$dataInicioFormatada = '';
$dataFimFormatada    = '';

if (!empty($relatorio['data_inicio'])) {
    $ts = strtotime((string)$relatorio['data_inicio']);
    if ($ts) $dataInicioFormatada = date('Y-m-d\TH:i', $ts);
}
if (!empty($relatorio['data_fim'])) {
    $ts = strtotime((string)$relatorio['data_fim']);
    if ($ts) $dataFimFormatada = date('Y-m-d\TH:i', $ts);
}

$isEdit = !empty($rel['uuid']);

$formAction = $isEdit
    ? BASE_URL . '/relatorio-plantao/plantao/' . rawurlencode((string)$rel['uuid']) . '/atualizar'
    : ($pacienteUuid !== ''
        ? BASE_URL . '/relatorio-plantao/paciente/' . rawurlencode($pacienteUuid) . '/store'
        : BASE_URL . '/relatorio-plantao');

$temDiabetes = !empty($pac['tem_diabetes']) || ($rel['hgt'] ?? '') !== '';
$acamado = !empty($pac['acamado']);

$turnoSalvo = $relatorio['turno'] ?? 'plantao_24h';

$turnos = [
    'plantao_6h'  => '6 horas',
    'plantao_8h'  => '8 horas',
    'plantao_12h' => '12 horas',
    'plantao_24h' => '24 horas',
];

$fmtDateTimeLocal = static function (?string $value): string {
    if (!$value) return '';
    $ts = strtotime($value);
    return $ts ? date('Y-m-d\TH:i', $ts) : '';
};

$old = static fn(string $key, mixed $default = ''): string => htmlspecialchars((string)($_POST[$key] ?? $default));
$relValue = static fn(string $key, mixed $default = ''): string => htmlspecialchars((string)($rel[$key] ?? $default));

$decodeList = static function (mixed $value): array {
    if (is_array($value)) return $value;
    if (!is_string($value) || trim($value) === '') return [];
    $decoded = json_decode($value, true);
    if (is_array($decoded)) return $decoded;
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

// Hidratação: lista de registros [horario, ml]
$hidratacaoRegistros = $decodeList($rel['hidratacao_registros'] ?? []);
if (empty($hidratacaoRegistros)) {
    $hidratacaoRegistros = [['horario' => '', 'ml' => '']];
}

// Urina: lista de horários
$urinaHorarios = $decodeList($rel['urina_horarios'] ?? []);
if (empty($urinaHorarios)) $urinaHorarios = [''];

// Fezes: lista de horários
$fezesHorarios = $decodeList($rel['fezes_horarios'] ?? []);
if (empty($fezesHorarios)) $fezesHorarios = [''];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/relatorio_plantao_form.css">

<form id="relatorio-form" method="POST" action="<?= htmlspecialchars($formAction) ?>">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="paciente_id" value="<?= (int)($pac['id'] ?? $rel['paciente_id'] ?? 0) ?>">
    <input type="hidden" name="status" value="rascunho">

    <div class="rp-wrapper">

        <!-- ═══════════════════════════════════════════════════════
             CABEÇALHO + SELEÇÃO DE PACIENTE
        ════════════════════════════════════════════════════════ -->
        <div class="rp-header">
            <div class="rp-header__patient">
                <div class="rp-avatar"><?= htmlspecialchars($pac['iniciais'] ?? '?') ?></div>
                <div class="rp-patient-info">
                    <div class="rp-patient-info__name">
                        <?= ($pac['nome'] ?? '') !== '' ? htmlspecialchars($pac['nome']) : 'Selecione um paciente abaixo' ?>
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

            <article class="rp-card">
                <div class="rp-card-header">
                    <div class="rp-card-title">
                        <i class="ti ti-calendar"></i> Dados do Plantão
                    </div>
                    <div class="rp-card-subtitle">Período, turno, cuidador e status</div>
                </div>
                <div class="rp-card-body">

                    <div class="rp-form-group">
                        <label class="rp-label">Duração do turno</label>
                        <div class="turno-selector" id="turno-selector">
                            <?php foreach ($turnos as $valor => $label): ?>
                            <button type="button" class="turno-pill <?= $turnoSalvo === $valor ? 'active' : '' ?>"
                                data-turno="<?= $valor ?>" onclick="setTurno('<?= $valor ?>', this)">
                                <?= $label ?>
                            </button>
                            <?php endforeach ?>
                        </div>
                        <input type="hidden" name="turno" id="input-turno" value="<?= htmlspecialchars($turnoSalvo) ?>">
                    </div>

                    <div class="rp-form-grid">
                        <div class="rp-form-group">
                            <label>Data/Hora Inicial <span class="req">*</span></label>
                            <input type="datetime-local" name="data_inicio" value="<?= $dataInicioFormatada ?>"
                                class="rp-input" required>
                        </div>
                        <div class="rp-form-group">
                            <label>Data/Hora Final</label>
                            <input type="datetime-local" name="data_fim" value="<?= $dataFimFormatada ?>"
                                class="rp-input">
                        </div>

                        <?php if (!empty($cuidadores) && is_array($cuidadores)): ?>
                        <div class="rp-form-group">
                            <label>Cuidador</label>
                            <select name="cuidador_id" class="rp-input">
                                <option value="">— Selecione —</option>
                                <?php foreach ($cuidadores as $cid => $c):
                                    $sel = ((int)($relatorio['cuidador_id'] ?? 0) === (int)$cid) ? 'selected' : '';
                                ?>
                                <option value="<?= (int)$cid ?>" <?= $sel ?>>
                                    <?= htmlspecialchars($c['nome'] ?? '') ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php endif ?>

                        <div class="rp-form-group">
                            <label>Status</label>
                            <select name="status" class="rp-input">
                                <?php foreach (['rascunho' => 'Rascunho', 'finalizado' => 'Finalizado', 'assinado' => 'Assinado'] as $v => $l): ?>
                                <option value="<?= $v ?>"
                                    <?= ($relatorio['status'] ?? 'rascunho') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>

                </div>
            </article>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             IDENTIFICAÇÃO DO PLANTÃO
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-id-badge" aria-hidden="true"></i> Identificação do Plantão
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="sel-paciente">Paciente <span class="req">*</span></label>
                    <select id="sel-paciente" name="paciente_id" required>
                        <option value="">Selecione o paciente...</option>
                        <?php foreach ($pacientes as $p):
                            $pid  = (int)($p['id'] ?? 0);
                            $pnome = $p['nome_completo'] ?? $p['nome'] ?? '';
                            $sel  = ((int)($pac['id'] ?? $rel['paciente_id'] ?? 0) === $pid) ? ' selected' : '';
                        ?>
                        <option value="<?= $pid ?>" <?= $sel ?>><?= htmlspecialchars($pnome) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="inp-dn">Data de Nascimento</label>
                    <input type="date" id="inp-dn" name="data_nascimento" class="sinal-field__input"
                        value="<?= $relValue('data_nascimento', $pac['data_nascimento'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="inp-internacao">Diagnóstico / Internação</label>
                    <input type="text" id="inp-internacao" name="internacao" class="sinal-field__input"
                        placeholder="ex: cardiopatia / síndrome hemofagocítica"
                        value="<?= $relValue('internacao', $pac['diagnostico'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="inp-acompanhante">Nome do Acompanhante</label>
                    <input type="text" id="inp-acompanhante" name="nome_acompanhante" class="sinal-field__input"
                        placeholder="ex: Beatriz" value="<?= $relValue('nome_acompanhante') ?>">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="inp-tipo-local">Tipo de Atendimento</label>
                    <select id="inp-tipo-local" name="tipo_local" class="sinal-field__input">
                        <?php $tipoLocal = $rel['tipo_local'] ?? 'hospital'; ?>
                        <option value="hospital" <?= $tipoLocal === 'hospital'   ? 'selected' : '' ?>>Hospital</option>
                        <option value="domiciliar" <?= $tipoLocal === 'domiciliar' ? 'selected' : '' ?>>Domiciliar
                        </option>
                    </select>
                </div>

                <div class="form-group" id="grupo-quarto">
                    <label class="form-label" for="inp-quarto">Quarto / Leito</label>
                    <input type="text" id="inp-quarto" name="quarto" class="sinal-field__input"
                        placeholder="ex: P1.4 (UTI neo)" value="<?= $relValue('quarto') ?>">
                </div>
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="sel-cuidador">Cuidador Responsável</label>
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
        </div>

        <!-- ═══════════════════════════════════════════════════════
             SINAIS VITAIS
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-activity" aria-hidden="true"></i> Sinais Vitais
            </div>

            <div class="sinais-grid-form">
                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-temp">
                        <i class="ti ti-thermometer"></i> Temperatura
                    </label>
                    <input class="sinal-field__input" type="number" step="0.1" id="inp-temp" name="temperatura"
                        placeholder="°C" min="30" max="45" value="<?= $relValue('temperatura') ?>"
                        oninput="avaliarSinal(this, 'temp')">
                    <span class="sinal-field__unidade">°C</span>
                    <span class="sinal-badge hidden" id="badge-temp"></span>
                </div>

                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-pa">
                        <i class="ti ti-stethoscope"></i> Pressão Arterial
                    </label>
                    <input class="sinal-field__input" type="text" id="inp-pa" name="pa" placeholder="ex: 120/80"
                        value="<?= $relValue('pa') ?>" oninput="avaliarSinal(this, 'pa')" autocomplete="off">
                    <span class="sinal-field__unidade">mmHg</span>
                    <span class="sinal-badge hidden" id="badge-pa"></span>
                </div>

                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-fc">
                        <i class="ti ti-heart-rate-monitor"></i> Freq. Cardíaca
                    </label>
                    <input class="sinal-field__input" type="number" id="inp-fc" name="fc" placeholder="bpm" min="0"
                        max="300" value="<?= $relValue('fc') ?>" oninput="avaliarSinal(this, 'fc')">
                    <span class="sinal-field__unidade">bpm</span>
                    <span class="sinal-badge hidden" id="badge-fc"></span>
                </div>

                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-spo2">
                        <i class="ti ti-lungs"></i> Saturação (SpO₂)
                    </label>
                    <input class="sinal-field__input" type="number" id="inp-spo2" name="spo2" placeholder="%" min="50"
                        max="100" value="<?= $relValue('spo2') ?>" oninput="avaliarSinal(this, 'spo2')">
                    <span class="sinal-field__unidade">%</span>
                    <span class="sinal-badge hidden" id="badge-spo2"></span>
                </div>

                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-fr">
                        <i class="ti ti-wind"></i> Freq. Respiratória
                    </label>
                    <input class="sinal-field__input" type="number" id="inp-fr" name="frequencia_respiratoria"
                        placeholder="irpm" min="0" max="80" value="<?= $relValue('frequencia_respiratoria') ?>"
                        oninput="avaliarSinal(this, 'fr')">
                    <span class="sinal-field__unidade">irpm</span>
                    <span class="sinal-badge hidden" id="badge-fr"></span>
                </div>

                <div class="sinal-field">
                    <label class="sinal-field__label" for="inp-hgt">
                        <i class="ti ti-droplet-half"></i> Glicemia (HGT)
                    </label>
                    <input class="sinal-field__input" type="number" id="inp-hgt" name="hgt" placeholder="mg/dL" min="0"
                        max="600" value="<?= $relValue('hgt') ?>" oninput="avaliarSinal(this, 'hgt')">
                    <span class="sinal-field__unidade">mg/dL</span>
                    <span class="sinal-badge hidden" id="badge-hgt"></span>
                </div>
            </div>

            <div class="form-group" style="margin-top:1.25rem">
                <label class="form-label">Nível de Consciência</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Lúcido e orientado', 'Confuso', 'Sonolento', 'Não responsivo'] as $c): ?>
                    <label class="check-opt">
                        <input type="radio" name="consciencia" value="<?= htmlspecialchars($c) ?>"
                            <?= $isChecked('consciencia', $c, $rel['consciencia'] ?? '') ?>>
                        <?= htmlspecialchars($c) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nível de Dor (0 = sem dor / 10 = máxima)</label>
                <div class="dor-row">
                    <input type="range" id="dor-range" name="nivel_dor" min="0" max="10" step="1"
                        value="<?= (int)($rel['nivel_dor'] ?? 0) ?>" oninput="updateDor(this)">
                    <span id="dor-val" class="dor-val"><?= (int)($rel['nivel_dor'] ?? 0) ?></span>
                    <span class="sinal-badge badge-ok" id="dor-badge">Sem dor</span>
                </div>
            </div>

            <div class="rp-field-full">
                <label class="form-label">Observação dos Sinais Vitais</label>
                <textarea name="observacao_sv" rows="3"
                    placeholder="Observações complementares sobre os sinais vitais..."><?= htmlspecialchars($relatorio['observacao_sv'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             ALIMENTAÇÃO / HIDRATAÇÃO
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-droplet" aria-hidden="true"></i> Alimentação / Hidratação
            </div>

            <div class="form-row-2" style="margin-bottom:1rem">
                <div class="form-group">
                    <label class="form-label">Via de Alimentação</label>
                    <select name="alimentacao_via" class="sinal-field__input">
                        <?php $via = $rel['alimentacao_via'] ?? ''; ?>
                        <option value="">Selecione</option>
                        <option value="VO" <?= $via === 'VO'  ? 'selected' : '' ?>>VO (via oral)</option>
                        <option value="SNE" <?= $via === 'SNE' ? 'selected' : '' ?>>SNE</option>
                        <option value="GTT" <?= $via === 'GTT' ? 'selected' : '' ?>>GTT</option>
                        <option value="NPT" <?= $via === 'NPT' ? 'selected' : '' ?>>NPT</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Aceitação Alimentar</label>
                    <select name="alimentacao" class="sinal-field__input">
                        <?php $alim = $rel['alimentacao'] ?? ''; ?>
                        <option value="">Selecionar...</option>
                        <?php foreach ([
                            'Aceitou bem todas as refeições',
                            'Aceitou parcialmente',
                            'Recusou alimentação',
                            'Dieta via sonda - infundida',
                            'Jejum prescrito',
                        ] as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>" <?= $isChecked('alimentacao', $opt, $alim) ?>>
                            <?= htmlspecialchars($opt) ?>
                        </option>
                        <?php endforeach ?>
                    </select>
                </div>
            </div>

            <!-- Registros horários de hidratação -->
            <label class="form-label">
                <i class="ti ti-clock" style="font-size:.9em"></i> Registros de Hidratação por Horário
            </label>
            <div id="hidratacao-list" class="dynamic-list">
                <?php foreach ($hidratacaoRegistros as $idx => $reg):
                    $hHorario = is_array($reg) ? ($reg['horario'] ?? '') : '';
                    $hMl      = is_array($reg) ? ($reg['ml'] ?? '') : '';
                ?>
                <div class="dynamic-list-row" data-idx="<?= $idx ?>">
                    <input type="time" name="hidratacao_registros[<?= $idx ?>][horario]" class="sinal-field__input"
                        placeholder="horário" value="<?= htmlspecialchars($hHorario) ?>">
                    <input type="number" name="hidratacao_registros[<?= $idx ?>][ml]" class="sinal-field__input"
                        placeholder="ml" min="0" max="9999" value="<?= htmlspecialchars($hMl) ?>">
                    <span class="sinal-field__unidade">ml</span>
                    <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
                <?php endforeach ?>
            </div>
            <button type="button" class="btn-add-row" onclick="addHidratacaoRow()">
                <i class="ti ti-plus"></i> Adicionar registro
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             HIGIENE
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-bath" aria-hidden="true"></i> Higiene
            </div>

            <div class="form-group">
                <label class="form-label">Tipo de Higiene</label>
                <div class="check-group" data-mode="single">
                    <?php foreach (['Banho de chuveiro', 'Banho no leito', 'Higiene parcial', 'Não realizado'] as $h): ?>
                    <label class="check-opt">
                        <input type="radio" name="higiene" value="<?= htmlspecialchars($h) ?>"
                            <?= $isChecked('higiene', $h, $rel['higiene'] ?? '') ?>>
                        <?= htmlspecialchars($h) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- Troca de fralda -->
            <div class="form-group" style="margin-top:1rem">
                <label class="form-label">
                    <i class="ti ti-replace" style="font-size:.9em"></i> Troca de Fralda — Urina (horários)
                </label>
                <div id="urina-list" class="dynamic-list dynamic-list--inline">
                    <?php foreach ($urinaHorarios as $idx => $h): ?>
                    <div class="dynamic-list-row dynamic-list-row--inline">
                        <input type="time" name="urina_horarios[<?= $idx ?>]" class="sinal-field__input"
                            value="<?= htmlspecialchars((string)$h) ?>">
                        <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                    <?php endforeach ?>
                </div>
                <button type="button" class="btn-add-row" onclick="addUrinaRow()">
                    <i class="ti ti-plus"></i> Adicionar horário
                </button>
            </div>

            <div class="form-group" style="margin-top:1rem">
                <label class="form-label">
                    <i class="ti ti-replace" style="font-size:.9em"></i> Troca de Fralda — Fezes (horários)
                </label>
                <div id="fezes-list" class="dynamic-list dynamic-list--inline">
                    <?php foreach ($fezesHorarios as $idx => $h): ?>
                    <div class="dynamic-list-row dynamic-list-row--inline">
                        <input type="time" name="fezes_horarios[<?= $idx ?>]" class="sinal-field__input"
                            value="<?= htmlspecialchars((string)$h) ?>">
                        <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                    <?php endforeach ?>
                </div>
                <button type="button" class="btn-add-row" onclick="addFezesRow()">
                    <i class="ti ti-plus"></i> Adicionar horário
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             SONO E DESCANSO
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-moon" aria-hidden="true"></i> Sono e Descanso
            </div>

            <div class="form-group">
                <div class="check-group" data-mode="single">
                    <?php foreach (['Tranquilo', 'Agitado', 'Intercalado'] as $s): ?>
                    <label class="check-opt">
                        <input type="radio" name="sono" value="<?= htmlspecialchars($s) ?>"
                            <?= $isChecked('sono', $s, $rel['sono'] ?? '') ?>>
                        <?= htmlspecialchars($s) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             MEDICAÇÕES
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-pill" aria-hidden="true"></i> Medicações do Plantão
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
                        <span class="med-via">— <?= htmlspecialchars($med['dosagem']) ?></span>
                        <?php endif ?>
                        <span class="med-via">— <?= htmlspecialchars($med['via'] ?? '') ?></span>
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
                Nenhuma medicação cadastrada. Registre na evolução se necessário.
            </p>
            <?php endif ?>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             INFORMAÇÕES ADICIONAIS
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-clipboard-list" aria-hidden="true"></i> Informações Adicionais
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="inp-estado-geral">Estado Geral</label>
                    <input type="text" id="inp-estado-geral" name="estado_geral" class="sinal-field__input"
                        placeholder="ex: bom, regular, grave" value="<?= $relValue('estado_geral') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="inp-pele-mucosas">Pele / Mucosas</label>
                    <input type="text" id="inp-pele-mucosas" name="pele_mucosas" class="sinal-field__input"
                        placeholder="ex: íntegras, ressecadas, ictéricas" value="<?= $relValue('pele_mucosas') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-queixas">Queixas Referidas</label>
                <textarea id="inp-queixas" name="queixas_referidas" rows="2"
                    placeholder="Queixas e sintomas relatados pelo paciente ou responsável..."><?= $relValue('queixas_referidas') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-exame-fisico">Exame Físico</label>
                <textarea id="inp-exame-fisico" name="exame_fisico" rows="2"
                    placeholder="Achados do exame físico..."><?= $relValue('exame_fisico') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Eliminações</label>
                <div class="check-group" data-mode="multi">
                    <?php $eliminacoes = $selectedList('eliminacoes'); ?>
                    <?php foreach (['Diurese normal', 'Evacuação normal', 'Incontinência urinária', 'Incontinência fecal', 'Sem eliminações no turno'] as $e): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="eliminacoes[]" value="<?= htmlspecialchars($e) ?>"
                            <?= $isChecked('eliminacoes', $e, $eliminacoes) ?>>
                        <?= htmlspecialchars($e) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- Dispositivos em Uso -->
            <div class="form-group" style="margin-top:.75rem">
                <label class="form-label">Dispositivos em Uso</label>
                <div class="rp-checkbox-grid">
                    <?php
                    $dispositivosSelecionados = [];
                    if (!empty($relatorio['dispositivos'])) {
                        $dispositivosSelecionados = json_decode($relatorio['dispositivos'], true) ?: [];
                    }
                    $listaDispositivos = ['Oxigênio', 'SNE', 'GTT', 'PICC', 'Sonda Vesical', 'Traqueostomia', 'Colostomia'];
                    ?>
                    <?php foreach ($listaDispositivos as $disp): ?>
                    <label class="rp-check">
                        <input type="checkbox" name="dispositivos[]" value="<?= $disp ?>"
                            <?= in_array($disp, $dispositivosSelecionados) ? 'checked' : '' ?>>
                        <?= $disp ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>

            <?php if ($acamado): ?>
            <div class="form-group">
                <label class="form-label">Mudança de Decúbito</label>
                <div class="check-group" data-mode="multi">
                    <?php $decubito = $selectedList('decubito'); ?>
                    <?php foreach (['D.D. para D.L.D.', 'D.L.D. para D.L.E.', 'D.L.E. para D.D.', 'Semi-fowler', 'Fowler'] as $d): ?>
                    <label class="check-opt">
                        <input type="checkbox" name="decubito[]" value="<?= htmlspecialchars($d) ?>"
                            <?= $isChecked('decubito', $d, $decubito) ?>>
                        <?= htmlspecialchars($d) ?>
                    </label>
                    <?php endforeach ?>
                </div>
            </div>
            <?php endif ?>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             VISITA MÉDICA E CONDUTAS
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-user-check" aria-hidden="true"></i> Visita Médica e Condutas
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-visita-medica">Médico(a) / Condutas</label>
                <textarea id="inp-visita-medica" name="visita_medica" rows="3"
                    placeholder="ex: Dr. Fernanda — prescrição de antibiótico, solicitação de exames..."><?= $relValue('visita_medica') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             ENTRADA / SAÍDA DE PROFISSIONAIS E FAMILIARES
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-door-enter" aria-hidden="true"></i> Entrada / Saída de Profissionais e Familiares
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-profissionais-es">Enfermeiros / Técnicos</label>
                <textarea id="inp-profissionais-es" name="entrada_saida_profissionais" rows="2"
                    placeholder="Registre a entrada e saída de profissionais durante o plantão..."><?= $relValue('entrada_saida_profissionais') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-familiares-es">Familiares / Visitas</label>
                <textarea id="inp-familiares-es" name="entrada_saida_familiares" rows="2"
                    placeholder="Registre a entrada e saída de familiares e visitantes..."><?= $relValue('entrada_saida_familiares') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             EVOLUÇÃO DE ENFERMAGEM
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-notes" aria-hidden="true"></i> Evolução de Enfermagem
            </div>
            <textarea id="evolucao" name="evolucao" class="evolucao-textarea" rows="5"
                placeholder="Descreva o estado geral do paciente, condutas adotadas e observações relevantes..."><?= $relValue('evolucao') ?></textarea>
            <button type="button" class="btn btn-secondary" id="btn-gerar-evolucao" style="margin-top: .75rem">
                <i class="ti ti-wand"></i> Gerar evolução
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             INTERCORRÊNCIAS
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-alert-triangle" aria-hidden="true"></i> Intercorrências
            </div>

            <div id="inter-list">
                <?php foreach ($decodeList($rel['intercorrencias'] ?? []) as $idx => $inter):
                    $desc = is_array($inter) ? ($inter['descricao'] ?? '') : $inter;
                    if (trim((string)$desc) === '') continue;
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
                Turno sem intercorrências
            </label>

            <button type="button" class="btn-add-inter" id="btn-add-inter" onclick="addInter()">
                <i class="ti ti-plus" aria-hidden="true"></i> Adicionar intercorrência
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             PASSAGEM DE PLANTÃO
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-transfer" aria-hidden="true"></i> Passagem de Plantão
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-plantao-entregue">Plantão entregue para</label>
                <input type="text" id="inp-plantao-entregue" name="plantao_entregue_para" class="sinal-field__input"
                    placeholder="Nome do profissional que assumiu o plantão"
                    value="<?= $relValue('plantao_entregue_para') ?>">
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             OBSERVAÇÕES FINAIS
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-section__title">
                <i class="ti ti-pencil" aria-hidden="true"></i> Observações Finais
            </div>

            <div class="form-row-2">
                <div class="form-group">
                    <label class="form-label" for="inp-peso">Peso</label>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <input type="text" id="inp-peso" name="peso" class="sinal-field__input" placeholder="ex: 7,076"
                            value="<?= $relValue('peso') ?>">
                        <span class="sinal-field__unidade">kg</span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="inp-observacoes">Observações Gerais</label>
                <textarea id="inp-observacoes" name="observacoes_gerais" rows="4"
                    placeholder="Outras observações relevantes sobre o plantão..."><?= $relValue('observacoes_gerais') ?></textarea>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════
             RODAPÉ / AÇÕES
        ════════════════════════════════════════════════════════ -->
        <div class="rp-section">
            <div class="rp-footer">
                <div class="rp-footer__person">
                    <?php
                    $nomeEnf = $enf['nome'] ?? '';
                    $partes  = array_filter(explode(' ', trim($nomeEnf)));
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
    hidratacaoCount: <?= count($hidratacaoRegistros) ?>,
    urinaCount: <?= count($urinaHorarios) ?>,
    fezesCount: <?= count($fezesHorarios) ?>,
    diagnostico: <?= json_encode($pac['diagnostico'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    cid: <?= json_encode($pac['cid'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    acamado: <?= $acamado ? 'true' : 'false' ?>
};

// Mostrar/ocultar campo Quarto de acordo com tipo_local
(function() {
    const sel = document.getElementById('inp-tipo-local');
    const grp = document.getElementById('grupo-quarto');
    if (!sel || !grp) return;

    function toggle() {
        grp.style.display = sel.value === 'domiciliar' ? 'none' : '';
    }
    sel.addEventListener('change', toggle);
    toggle();
})();

// Adicionar linha de hidratação
let _hCount = window.FORM_DATA.hidratacaoCount;

function addHidratacaoRow() {
    const list = document.getElementById('hidratacao-list');
    const idx = _hCount++;
    const div = document.createElement('div');
    div.className = 'dynamic-list-row';
    div.dataset.idx = idx;
    div.innerHTML = `
        <input type="time" name="hidratacao_registros[${idx}][horario]" class="sinal-field__input" placeholder="horário">
        <input type="number" name="hidratacao_registros[${idx}][ml]" class="sinal-field__input" placeholder="ml" min="0" max="9999">
        <span class="sinal-field__unidade">ml</span>
        <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
            <i class="ti ti-trash"></i>
        </button>`;
    list.appendChild(div);
}

let _uCount = window.FORM_DATA.urinaCount;

function addUrinaRow() {
    const list = document.getElementById('urina-list');
    const idx = _uCount++;
    const div = document.createElement('div');
    div.className = 'dynamic-list-row dynamic-list-row--inline';
    div.innerHTML = `
        <input type="time" name="urina_horarios[${idx}]" class="sinal-field__input">
        <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
            <i class="ti ti-trash"></i>
        </button>`;
    list.appendChild(div);
}

let _fCount = window.FORM_DATA.fezesCount;

function addFezesRow() {
    const list = document.getElementById('fezes-list');
    const idx = _fCount++;
    const div = document.createElement('div');
    div.className = 'dynamic-list-row dynamic-list-row--inline';
    div.innerHTML = `
        <input type="time" name="fezes_horarios[${idx}]" class="sinal-field__input">
        <button type="button" class="btn-icon-remove" onclick="removeRow(this)" title="Remover">
            <i class="ti ti-trash"></i>
        </button>`;
    list.appendChild(div);
}

function removeRow(btn) {
    btn.closest('.dynamic-list-row').remove();
}
</script>
<script src="<?= BASE_URL ?>/assets/js/relatorio_plantao_form.js"></script>