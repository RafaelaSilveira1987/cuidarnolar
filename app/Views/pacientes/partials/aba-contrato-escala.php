<?php
$paciente = isset($paciente) && is_array($paciente) ? $paciente : ($record ?? []);
$contratoAtivo = isset($contratoAtivo) && is_array($contratoAtivo) ? $contratoAtivo : [];
$contratosPaciente = isset($contratosPaciente) && is_array($contratosPaciente) ? $contratosPaciente : [];
$escalaResumo = isset($escalaResumo) && is_array($escalaResumo) ? $escalaResumo : [];
$escalaBase = isset($escalaResumo['base']) && is_array($escalaResumo['base']) ? $escalaResumo['base'] : [];
$profissionaisEscala = isset($escalaResumo['profissionais']) && is_array($escalaResumo['profissionais']) ? $escalaResumo['profissionais'] : [];
$proximosPlantoes = isset($escalaResumo['proximos']) && is_array($escalaResumo['proximos']) ? $escalaResumo['proximos'] : [];
$cuidadoresOptions = isset($cuidadoresEscalaOptions) && is_array($cuidadoresEscalaOptions) ? $cuidadoresEscalaOptions : [];
$tipoCoberturaSugerido = $tipoCoberturaSugerido ?? '12h';
$resourceKey = $paciente['uuid'] ?? $paciente['id'] ?? '';

$fmtDate = static function (?string $date): string {
    if (!$date) return '—';
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : '—';
};

$fmtMoney = static function (mixed $valor): string {
    if ($valor === null || $valor === '') return '—';
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
};

$checkedDia = static function (array $escalaBase, string $dia): string {
    if ($escalaBase === []) {
        return 'checked';
    }

    return !empty($escalaBase[$dia]) ? 'checked' : '';
};

$cuidadorSelecionado = array_map(static fn(array $p): int => (int)($p['cuidador_id'] ?? 0), $profissionaisEscala);

// Paleta fixa: evita cores soltas e mantém a escala visualmente organizada.
$paletaCuidadores = [
    '', // opção sem cor
    '#68f7f2', // verde água
    '#08f31c', // teal
    '#2563eb', // azul
    '#7c3aed', // roxo
    '#db2777', // rosa
    '#dc2626', // vermelho
    '#ea580c', // laranja
    '#ca8a04', // amarelo/ocre
    '#16a34a', // verde
    '#475569', // grafite
];

$corDaPaleta = static function (mixed $cor) use ($paletaCuidadores): ?string {
    $cor = strtolower(trim((string)$cor));

    // Permite a opção "sem cor"
    if ($cor === '') {
        return '';
    }

    if (!preg_match('/^#[0-9a-f]{6}$/', $cor)) {
        return null;
    }

    return in_array($cor, $paletaCuidadores, true) ? $cor : null;
};

$coresPorCuidador = [];
foreach ($profissionaisEscala as $profissionalEscala) {
    $cid = (int)($profissionalEscala['cuidador_id'] ?? 0);
    $cor = $corDaPaleta($profissionalEscala['cor_escala'] ?? null);
    if ($cid > 0 && $cor !== null) {
        $coresPorCuidador[$cid] = $cor;
    }
}

$corPadraoCuidador = static function (int $cid) use ($paletaCuidadores): string {
    // Padrão: sem cor. A cor só aparece quando a usuária escolher.
    return '';
};
?>

<section class="panel contrato-escala-panel">
    <div class="panel-header contrato-escala-head">
        <div>
            <h2>Escala operacional</h2>
            <p class="page-subtitle">A escala usa o contrato ativo apenas como referência. O contrato financeiro fica na aba Contratos.</p>
        </div>
    </div>

    <div class="ce-grid">
        <article class="ce-card">
            <span class="ce-label">Contrato ativo</span>
            <?php if ($contratoAtivo): ?>
            <strong><?= e($contratoAtivo['tipo_servico'] ?? 'Contrato') ?></strong>
            <p><?= e($fmtMoney($contratoAtivo['valor_mensal'] ?? null)) ?> · vencimento dia
                <?= e((string)($contratoAtivo['dia_vencimento'] ?? '—')) ?></p>
            <small>Vigência: <?= e($fmtDate($contratoAtivo['vigencia_inicio'] ?? null)) ?> até
                <?= e($fmtDate($contratoAtivo['vigencia_fim'] ?? null)) ?></small>
            <?php else: ?>
            <strong>Sem contrato ativo</strong>
            <p>Cadastre ou vincule um contrato para sugerir automaticamente 6h, 8h, 12h ou 24h.</p>
            <?php endif; ?>
        </article>

        <article class="ce-card">
            <span class="ce-label">Escala base</span>
            <?php if ($escalaBase): ?>
            <strong><?= e($escalaBase['tipo_cobertura'] ?? '—') ?> ·
                <?= e(substr((string)($escalaBase['hora_inicio'] ?? ''), 0, 5)) ?> às
                <?= e(substr((string)($escalaBase['hora_fim'] ?? ''), 0, 5)) ?></strong>
            <p><?= e(ucfirst((string)($escalaBase['tipo_atendimento'] ?? 'domiciliar'))) ?><?= !empty($escalaBase['local']) ? ' · ' . e($escalaBase['local']) : '' ?>
            </p>
            <small><?= !empty($escalaBase['revezamento_automatico']) ? 'Revezamento automático ativo' : 'Revezamento manual' ?></small>
            <?php else: ?>
            <strong>Não configurada</strong>
            <p>Preencha o formulário abaixo para criar a régua operacional desse paciente.</p>
            <?php endif; ?>
        </article>

        <article class="ce-card">
            <span class="ce-label">Equipe vinculada</span>
            <strong><?= count($profissionaisEscala) ?> profissional(is)</strong>
            <?php if ($profissionaisEscala): ?>
            <div class="ce-team-badges">
                <?php foreach ($profissionaisEscala as $profissionalEscala): ?>
                <?php
                        $cidEquipe = (int)($profissionalEscala['cuidador_id'] ?? 0);
                        $corEquipe = $coresPorCuidador[$cidEquipe] ?? $corPadraoCuidador($cidEquipe);
                        $corEquipe = $corDaPaleta($corEquipe) ?? '';
                        $semCorEquipe = $corEquipe === '';
                        ?>
                <span class="ce-team-badge <?= $semCorEquipe ? 'is-no-color' : '' ?>"
                    <?= !$semCorEquipe ? 'style="--ce-caregiver-color: ' . e($corEquipe) . '"' : '' ?>>
                    <i></i><?= e($profissionalEscala['nome_completo'] ?? 'Cuidador') ?>
                </span>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p>Nenhum cuidador fixado. A escala pode ficar aberta para preenchimento.</p>
            <?php endif; ?>
        </article>
    </div>
</section>

<section class="panel contrato-escala-form-panel">
    <div class="panel-header">
        <div>
            <h2><?= $escalaBase ? 'Ajustar escala base' : 'Configurar escala base' ?></h2>
            <p class="page-subtitle">Aqui nasce a automação da escala. Sem isso, o sistema fica chutando igual previsão
                do tempo antiga.</p>
        </div>
    </div>

    <form method="POST" action="<?= url('/pacientes/' . rawurlencode((string)$resourceKey) . '/escala-base') ?>"
        class="ce-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="ce-subsection ce-contract-notice">
            <h3>Contrato do paciente</h3>
            <p class="page-subtitle">A parte financeira foi separada para a aba Contratos. Aqui fica só a régua operacional da escala.</p>
            <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode((string)$resourceKey) . '?aba=contratos') ?>">Abrir contratos do paciente</a>
        </div>

        <div class="ce-subsection">
            <h3>Escala base</h3>
            <p class="page-subtitle">A escala usa o tipo de cobertura abaixo para montar a grade operacional.</p>
        </div>

        <div class="form-grid">
            <label>
                Nome da escala
                <input type="text" name="nome" value="<?= e($escalaBase['nome'] ?? 'Escala base') ?>">
            </label>

            <label>
                Tipo de cobertura
                <select name="tipo_cobertura">
                    <?php foreach (['24h', '12h', '8h', '6h'] as $tipo): ?>
                    <?php $selectedTipo = $escalaBase['tipo_cobertura'] ?? $tipoCoberturaSugerido; ?>
                    <option value="<?= e($tipo) ?>" <?= $selectedTipo === $tipo ? 'selected' : '' ?>><?= e($tipo) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Início
                <input type="time" name="hora_inicio"
                    value="<?= e(substr((string)($escalaBase['hora_inicio'] ?? '07:00'), 0, 5)) ?>">
            </label>

            <label>
                Fim
                <input type="time" name="hora_fim"
                    value="<?= e(substr((string)($escalaBase['hora_fim'] ?? '19:00'), 0, 5)) ?>">
            </label>

            <label>
                Tipo de atendimento
                <?php $tipoAtendimento = $escalaBase['tipo_atendimento'] ?? 'domiciliar'; ?>
                <select name="tipo_atendimento">
                    <option value="domiciliar" <?= $tipoAtendimento === 'domiciliar' ? 'selected' : '' ?>>Domiciliar
                    </option>
                    <option value="hospitalar" <?= $tipoAtendimento === 'hospitalar' ? 'selected' : '' ?>>Hospitalar
                    </option>
                </select>
            </label>

            <label>
                Local
                <input type="text" name="local"
                    value="<?= e($escalaBase['local'] ?? ($paciente['endereco_completo'] ?? '')) ?>"
                    placeholder="Endereço, hospital, quarto...">
            </label>
        </div>

        <div class="ce-week-box">
            <strong>Dias de cobertura</strong>
            <div class="ce-week-days">
                <label><input type="checkbox" name="domingo" value="1" <?= $checkedDia($escalaBase, 'domingo') ?>>
                    Dom</label>
                <label><input type="checkbox" name="segunda" value="1" <?= $checkedDia($escalaBase, 'segunda') ?>>
                    Seg</label>
                <label><input type="checkbox" name="terca" value="1" <?= $checkedDia($escalaBase, 'terca') ?>>
                    Ter</label>
                <label><input type="checkbox" name="quarta" value="1" <?= $checkedDia($escalaBase, 'quarta') ?>>
                    Qua</label>
                <label><input type="checkbox" name="quinta" value="1" <?= $checkedDia($escalaBase, 'quinta') ?>>
                    Qui</label>
                <label><input type="checkbox" name="sexta" value="1" <?= $checkedDia($escalaBase, 'sexta') ?>>
                    Sex</label>
                <label><input type="checkbox" name="sabado" value="1" <?= $checkedDia($escalaBase, 'sabado') ?>>
                    Sáb</label>
            </div>
        </div>
        <div class="ce-check">
            <label>
                <input class="ce-check-input" type="checkbox" name="revezamento_automatico" value="1"
                    <?= ($escalaBase === [] || !empty($escalaBase['revezamento_automatico'])) ? 'checked' : '' ?>>
                Usar revezamento automático entre os cuidadores selecionados
            </label>
        </div>

        <div class="ce-caregivers-box">
            <strong>Cuidadores da escala</strong>
            <p class="page-subtitle">A cor agora pertence ao vínculo deste cuidador com este paciente/escala. O
                mesmo
                cuidador pode ter outra cor em outro paciente.</p>

            <div class="ce-caregivers-list">
                <?php foreach ($cuidadoresOptions as $cuidador): ?>
                <?php
                    $cid = (int)($cuidador['id'] ?? 0);
                    $corCuidadorEscala = $coresPorCuidador[$cid] ?? $corPadraoCuidador($cid);
                    $corCuidadorEscala = $corDaPaleta($corCuidadorEscala) ?? '';
                    $semCorCuidador = $corCuidadorEscala === '';
                    $nomeCuidador = (string)($cuidador['nome_completo'] ?? 'Cuidador');
                    ?>
                <div class="ce-caregiver-row <?= $semCorCuidador ? 'is-no-color' : '' ?>"
                    <?= !$semCorCuidador ? 'style="--ce-caregiver-color: ' . e($corCuidadorEscala) . '"' : '' ?>>
                    <label class="ce-caregiver-checkline" title="Selecionar <?= e($nomeCuidador) ?>">
                        <input type="checkbox" name="cuidador_ids[]" value="<?= $cid ?>"
                            <?= in_array($cid, $cuidadorSelecionado, true) ? 'checked' : '' ?>>
                        <span class="ce-caregiver-info">
                            <strong><?= e($nomeCuidador) ?></strong>
                            <small><?= e($cuidador['especialidade'] ?? 'Cuidador') ?><?= !empty($cuidador['contrato_horas']) ? ' · ' . e($cuidador['contrato_horas']) : '' ?></small>
                        </span>
                    </label>

                    <div class="ce-color-picker" data-color-picker>
                        <button type="button" class="ce-color-swatch <?= $semCorCuidador ? 'is-empty' : '' ?>"
                            data-color-toggle aria-label="Escolher cor de <?= e($nomeCuidador) ?> nesta escala"
                            title="<?= $semCorCuidador ? 'Sem cor' : 'Escolher cor' ?>"
                            <?= !$semCorCuidador ? 'style="background: ' . e($corCuidadorEscala) . '"' : '' ?>></button>

                        <input type="hidden" name="cuidador_cores[<?= $cid ?>]" value="<?= e($corCuidadorEscala) ?>"
                            data-color-input>

                        <div class="ce-color-popover" data-color-popover>
                            <div class="ce-color-popover-title">Cor do cuidador nesta escala</div>
                            <div class="ce-color-grid" role="listbox" aria-label="Paleta de cores">
                                <?php foreach ($paletaCuidadores as $corOpcao): ?>
                                <?php
                                    $semCorOpcao = $corOpcao === '';
                                    $tituloCor = $semCorOpcao ? 'Sem cor' : strtoupper($corOpcao);
                                ?>
                                <button type="button"
                                    class="ce-color-choice <?= $corOpcao === $corCuidadorEscala ? 'is-active' : '' ?> <?= $semCorOpcao ? 'is-empty' : '' ?>"
                                    data-color-choice="<?= e($corOpcao) ?>" title="<?= e($tituloCor) ?>"
                                    aria-label="<?= e($tituloCor) ?>"
                                    <?= !$semCorOpcao ? 'style="background: ' . e($corOpcao) . '"' : '' ?>></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <label>
            Observações da escala
            <textarea name="observacoes" rows="3"
                placeholder="Ex.: troca aos domingos, cuidador preferencial, regras do domicílio..."><?= e($escalaBase['observacoes'] ?? '') ?></textarea>
        </label>

        <div class="button-row">
            <button type="submit" class="btn btn-primary">Salvar escala base</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header">
        <h2>Próximos plantões</h2>
        <p class="page-subtitle">Prévia do que já existe gerado na escala operacional.</p>
    </div>

    <?php if (!$proximosPlantoes): ?>
    <p class="empty-state">Nenhum plantão futuro encontrado para este paciente.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Horário</th>
                    <th>Cuidador</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proximosPlantoes as $plantao): ?>
                <tr>
                    <td><?= e($fmtDate($plantao['data_plantao'] ?? null)) ?></td>
                    <td><?= e(date('H:i', strtotime((string)$plantao['inicio']))) ?> às
                        <?= e(date('H:i', strtotime((string)$plantao['fim']))) ?></td>
                    <td><?= e($plantao['cuidador_nome'] ?? 'Aberto') ?></td>
                    <td><?= e($plantao['status'] ?? 'previsto') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>

<?php if ($contratosPaciente): ?>
<section class="panel">
    <div class="panel-header">
        <h2>Histórico de contratos</h2>
    </div>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contrato</th>
                    <th>Valor</th>
                    <th>Vigência</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contratosPaciente as $contrato): ?>
                <tr>
                    <td><?= e($contrato['tipo_servico'] ?? '-') ?></td>
                    <td><?= e($fmtMoney($contrato['valor_mensal'] ?? null)) ?></td>
                    <td><?= e($fmtDate($contrato['vigencia_inicio'] ?? null)) ?> até
                        <?= e($fmtDate($contrato['vigencia_fim'] ?? null)) ?></td>
                    <td><?= e($contrato['status'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>


<script>
(function() {
    document.addEventListener('click', function(event) {
        var toggle = event.target.closest('[data-color-toggle]');
        var choice = event.target.closest('[data-color-choice]');
        var picker = event.target.closest('[data-color-picker]');

        if (!picker) {
            document.querySelectorAll('[data-color-popover].is-open').forEach(function(popover) {
                popover.classList.remove('is-open');
            });
        }

        if (toggle) {
            event.preventDefault();
            var currentPicker = toggle.closest('[data-color-picker]');
            var currentPopover = currentPicker ? currentPicker.querySelector('[data-color-popover]') : null;

            document.querySelectorAll('[data-color-popover].is-open').forEach(function(popover) {
                if (popover !== currentPopover) {
                    popover.classList.remove('is-open');
                }
            });

            if (currentPopover) {
                currentPopover.classList.toggle('is-open');
            }
        }

        if (choice) {
            event.preventDefault();
            var color = choice.getAttribute('data-color-choice');
            var currentPicker = choice.closest('[data-color-picker]');
            var row = choice.closest('.ce-caregiver-row');
            var input = currentPicker ? currentPicker.querySelector('[data-color-input]') : null;
            var swatch = currentPicker ? currentPicker.querySelector('[data-color-toggle]') : null;
            var popover = currentPicker ? currentPicker.querySelector('[data-color-popover]') : null;

            if (input) input.value = color;

            if (swatch) {
                if (color) {
                    swatch.style.background = color;
                    swatch.classList.remove('is-empty');
                    swatch.setAttribute('title', 'Escolher cor');
                } else {
                    swatch.style.background = '';
                    swatch.classList.add('is-empty');
                    swatch.setAttribute('title', 'Sem cor');
                }
            }

            if (row) {
                if (color) {
                    row.style.setProperty('--ce-caregiver-color', color);
                    row.classList.remove('is-no-color');
                } else {
                    row.style.removeProperty('--ce-caregiver-color');
                    row.classList.add('is-no-color');
                }
            }

            if (currentPicker) {
                currentPicker.querySelectorAll('[data-color-choice]').forEach(function(btn) {
                    btn.classList.remove('is-active');
                });
            }

            choice.classList.add('is-active');
            if (popover) popover.classList.remove('is-open');
        }
    });
})();
</script>