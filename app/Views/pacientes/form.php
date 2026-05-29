<?php
$simNao = ['Nao' => 'Nao', 'Sim' => 'Sim'];
$sexoOptions = ['' => 'Selecione...', 'Feminino' => 'Feminino', 'Masculino' => 'Masculino', 'Outro' => 'Outro'];
$mobilidadeOptions = [
    '' => 'Selecione...',
    'Acamado' => 'Acamado',
    'Cadeirante' => 'Cadeirante',
    'Deambula com auxilio' => 'Deambula com auxilio',
    'Independente' => 'Independente',
];
$cognitivoOptions = [
    '' => 'Selecione...',
    'Orientado' => 'Orientado',
    'Confuso' => 'Confuso',
    'Demencia' => 'Demencia',
    'Alzheimer' => 'Alzheimer',
];
$vias = $medicacaoOptions['vias'] ?? [];
$medicacoes = $medicacoes ?? [];
$condutasSelecionadas = [];
if (!empty($paciente['condutas_permanentes'])) {
    $decoded = json_decode((string)$paciente['condutas_permanentes'], true);
    $condutasSelecionadas = is_array($decoded) ? $decoded : [];
}
$condutas = [
    'Mudanca de decubito a cada 2h',
    'Controle glicemico',
    'Restricao hidrica',
    'Aspiracao de vias aereas',
];

$selected = static fn($a, $b): string => (string)$a === (string)$b ? 'selected' : '';
$checked = static fn(array $arr, string $value): string => in_array($value, $arr, true) ? 'checked' : '';
?>

<section class="page-header">
    <div>
        <h1><?= e($title) ?></h1>
        <p class="page-subtitle">
            Cadastro base usado por anamnese, relatorios de plantao, agenda e financeiro.
        </p>
    </div>
    <a class="btn btn-secondary" href="<?= url('/pacientes') ?>">Voltar</a>
</section>

<section class="panel">
    <form class="form-grid paciente-form" method="POST" action="<?= url($action) ?>">
        <input type="hidden" name="_csrf" value="<?= e($_csrf) ?>">

        <div class="span-2 form-section-title">
            <h3>Identificacao</h3>
        </div>

        <label class="span-2">
            Nome completo
            <input type="text" name="nome_completo" value="<?= e($paciente['nome_completo'] ?? '') ?>" maxlength="100" required>
            <?php if (!empty($errors['nome_completo'])): ?>
                <small class="field-error"><?= e($errors['nome_completo']) ?></small>
            <?php endif; ?>
        </label>

        <label>
            Data de nascimento
            <input type="date" name="data_nascimento" value="<?= e($paciente['data_nascimento'] ?? '') ?>">
        </label>

        <label>
            Sexo
            <select name="sexo">
                <?php foreach ($sexoOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $selected($paciente['sexo'] ?? '', $value) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            CPF
            <input type="text" name="cpf" value="<?= e($paciente['cpf'] ?? '') ?>" maxlength="14">
        </label>

        <label>
            RG
            <input type="text" name="rg" value="<?= e($paciente['rg'] ?? '') ?>">
        </label>

        <label>
            CNS / Cartao SUS
            <input type="text" name="cartao_nac_sus" value="<?= e($paciente['cartao_nac_sus'] ?? '') ?>">
        </label>

        <label>
            Foto (URL opcional)
            <input type="text" name="foto" value="<?= e($paciente['foto'] ?? '') ?>" placeholder="https://...">
        </label>

        <div class="span-2 form-section-title">
            <h3>Contato</h3>
        </div>

        <label class="span-2">
            Endereco completo
            <input type="text" name="endereco_completo" value="<?= e($paciente['endereco_completo'] ?? '') ?>">
        </label>

        <label>
            Telefone principal
            <input type="text" name="telefone_principal" value="<?= e($paciente['telefone_principal'] ?? '') ?>">
        </label>

        <label>
            Telefone secundario
            <input type="text" name="telefone_secundario" value="<?= e($paciente['telefone_secundario'] ?? '') ?>">
        </label>

        <label class="span-2">
            E-mail
            <input type="email" name="email" value="<?= e($paciente['email'] ?? '') ?>">
        </label>

        <div class="span-2 form-section-title">
            <h3>Responsável financeiro/familiar</h3>
            <p class="page-subtitle">
                Os dados do responsável ficam no cadastro de Responsáveis. Aqui o paciente apenas recebe o vínculo correto.
            </p>
        </div>

        <label class="span-2">
            Responsável vinculado
            <select name="responsavel_id">
                <option value="">Sem responsável vinculado</option>
                <?php foreach (($responsaveis ?? []) as $responsavel): ?>
                    <option value="<?= (int)$responsavel['id'] ?>" <?= $selected($paciente['responsavel_id'] ?? '', $responsavel['id']) ?>>
                        <?= e($responsavel['nome_completo']) ?><?= !empty($responsavel['cpf']) ? ' — CPF: ' . e($responsavel['cpf']) : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>
                Para alterar nome, telefone, CPF, e-mail ou endereço, edite em <strong>Responsáveis</strong>.
                <?php if (!empty($paciente['responsavel_uuid'])): ?>
                    <a href="<?= url('/responsaveis/' . rawurlencode((string)$paciente['responsavel_uuid']) . '/editar') ?>">Editar responsável vinculado</a>.
                <?php else: ?>
                    <a href="<?= url('/responsaveis/novo') ?>">Cadastrar novo responsável</a>.
                <?php endif; ?>
            </small>
        </label>

        <label>
            Cuidador principal
            <select name="cuidador_id">
                <option value="">Sem vinculo</option>
                <?php foreach ($cuidadores as $cuidador): ?>
                    <option value="<?= (int)$cuidador['id'] ?>" <?= $selected($paciente['cuidador_id'] ?? '', $cuidador['id']) ?>>
                        <?= e($cuidador['nome_completo']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Plano de saude
            <input type="text" name="plano_saude" value="<?= e($paciente['plano_saude'] ?? '') ?>">
        </label>

        <div class="span-2 form-section-title">
            <h3>Dados clinicos fixos</h3>
        </div>

        <label class="span-2">
            Diagnostico principal
            <input type="text" name="diagnostico" value="<?= e($paciente['diagnostico'] ?? '') ?>" placeholder="Ex.: AVC isquemico, Alzheimer, Parkinson">
        </label>

        <label>
            CID principal
            <input type="text" name="cid_principal" value="<?= e($paciente['cid_principal'] ?? '') ?>" placeholder="Ex.: I63.9">
        </label>

        <label>
            Diagnostico principal detalhado
            <input type="text" name="diagnostico_principal" value="<?= e($paciente['diagnostico_principal'] ?? '') ?>">
        </label>

        <label class="span-2">
            Comorbidades
            <textarea name="comorbidades" rows="3"><?= e($paciente['comorbidades'] ?? '') ?></textarea>
        </label>

        <label class="span-2">
            Alergias
            <textarea name="alergias" rows="3"><?= e($paciente['alergias'] ?? '') ?></textarea>
        </label>

        <label class="span-2">
            Historico cirurgico
            <textarea name="historico_cirurgico" rows="3"><?= e($paciente['historico_cirurgico'] ?? '') ?></textarea>
        </label>

        <label>
            Tipo sanguineo
            <input type="text" name="tipo_sanguineo" value="<?= e($paciente['tipo_sanguineo'] ?? '') ?>" placeholder="Ex.: O+">
        </label>

        <label>
            Peso (kg)
            <input type="number" step="0.01" name="peso" value="<?= e($paciente['peso'] ?? '') ?>">
        </label>

        <label>
            Altura (m)
            <input type="number" step="0.01" name="altura" value="<?= e($paciente['altura'] ?? '') ?>">
        </label>

        <label class="span-2">
            Motivo do home care
            <textarea name="motivo_homecare" rows="3"><?= e($paciente['motivo_homecare'] ?? '') ?></textarea>
        </label>

        <div class="span-2 form-section-title">
            <h3>Medicamentos de uso continuo</h3>
        </div>

        <div class="span-2 med-editor" id="med-editor">
            <div class="med-editor-head">
                <span>Lista enumerada do paciente</span>
                <button type="button" class="btn btn-secondary" id="btn-add-med">Adicionar medicamento</button>
            </div>

            <div id="med-list-form">
                <?php
                $rows = $medicacoes !== [] ? $medicacoes : [[]];
                foreach ($rows as $idx => $med):
                ?>
                    <div class="med-form-row">
                        <input type="hidden" name="medicacoes_continuas[<?= $idx ?>][id]" value="<?= (int)($med['id'] ?? 0) ?>">
                        <div class="med-form-index"><?= $idx + 1 ?></div>
                        <label>
                            Medicamento
                            <input type="text" name="medicacoes_continuas[<?= $idx ?>][nome_medicamento]" value="<?= e($med['nome_medicamento'] ?? '') ?>">
                        </label>
                        <label>
                            Dosagem
                            <input type="text" name="medicacoes_continuas[<?= $idx ?>][dosagem]" value="<?= e($med['dosagem'] ?? '') ?>" placeholder="Ex.: 50 mg">
                        </label>
                        <label>
                            Horarios
                            <input type="text" name="medicacoes_continuas[<?= $idx ?>][horarios]" value="<?= e($med['horarios'] ?? '') ?>" placeholder="Ex.: 08:00, 20:00">
                        </label>
                        <label>
                            Via
                            <select name="medicacoes_continuas[<?= $idx ?>][via]">
                                <option value="">Selecione...</option>
                                <?php foreach ($vias as $via => $viaLabel): ?>
                                    <option value="<?= e($via) ?>" <?= $selected($med['via'] ?? '', $via) ?>><?= e($viaLabel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>
                            Frequencia
                            <input type="text" name="medicacoes_continuas[<?= $idx ?>][frequencia]" value="<?= e($med['frequencia'] ?? '') ?>">
                        </label>
                        <label>
                            Status
                            <select name="medicacoes_continuas[<?= $idx ?>][status]">
                                <option value="Ativo" <?= $selected($med['status'] ?? 'Ativo', 'Ativo') ?>>Ativo</option>
                                <option value="Inativo" <?= $selected($med['status'] ?? '', 'Inativo') ?>>Inativo</option>
                            </select>
                        </label>
                        <label class="med-form-obs">
                            Observacoes
                            <input type="text" name="medicacoes_continuas[<?= $idx ?>][observacoes]" value="<?= e($med['observacoes'] ?? '') ?>">
                        </label>
                        <label class="med-form-remove">
                            <input type="checkbox" name="medicacoes_continuas[<?= $idx ?>][_delete]" value="1">
                            Remover
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="span-2 form-section-title">
            <h3>Dieta, eliminacoes e mobilidade</h3>
        </div>

        <label>
            Tipo de dieta
            <input type="text" name="dieta_tipo" value="<?= e($paciente['dieta_tipo'] ?? '') ?>">
        </label>

        <label>
            Restricao alimentar
            <input type="text" name="dieta_restricao" value="<?= e($paciente['dieta_restricao'] ?? '') ?>">
        </label>

        <label>
            Via de alimentacao
            <input type="text" name="alimentacao_via" value="<?= e($paciente['alimentacao_via'] ?? '') ?>" placeholder="VO, GTT, SNE...">
        </label>

        <label>
            Uso de sonda vesical
            <select name="sonda_vesical">
                <?php foreach ($simNao as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $selected($paciente['sonda_vesical'] ?? 'Nao', $value) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Incontinencia
            <input type="text" name="incontinencia" value="<?= e($paciente['incontinencia'] ?? '') ?>">
        </label>

        <label>
            Mobilidade
            <select name="mobilidade">
                <?php foreach ($mobilidadeOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $selected($paciente['mobilidade'] ?? '', $value) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            Estado cognitivo base
            <select name="estado_cognitivo_base">
                <?php foreach ($cognitivoOptions as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $selected($paciente['estado_cognitivo_base'] ?? '', $value) ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <div class="span-2 form-section-title">
            <h3>Dispositivos, pele e condutas</h3>
        </div>

        <?php
        $camposSimNao = [
            'usa_sonda' => 'Usa sonda',
            'usa_oxigenio' => 'Oxigenio domiciliar',
            'traqueostomia' => 'Traqueostomia',
            'gastrostomia' => 'Gastrostomia',
            'colostomia' => 'Colostomia',
            'cateter_vesical' => 'Cateter vesical',
            'gtt' => 'GTT',
            'sne' => 'SNE',
            'cateter_venoso' => 'Cateter venoso',
            'picc' => 'PICC',
            'lesao_pressao' => 'Lesao por pressao',
        ];
        ?>

        <?php foreach ($camposSimNao as $campo => $label): ?>
            <label>
                <?= e($label) ?>
                <select name="<?= e($campo) ?>">
                    <?php foreach ($simNao as $value => $opcao): ?>
                        <option value="<?= e($value) ?>" <?= $selected($paciente[$campo] ?? 'Nao', $value) ?>><?= e($opcao) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endforeach; ?>

        <label class="span-2">
            Curativos
            <textarea name="curativos" rows="3"><?= e($paciente['curativos'] ?? '') ?></textarea>
        </label>

        <label class="span-2">
            Areas de risco
            <textarea name="areas_risco" rows="3"><?= e($paciente['areas_risco'] ?? '') ?></textarea>
        </label>

        <div class="span-2 checkbox-grid">
            <?php foreach ($condutas as $conduta): ?>
                <label>
                    <input type="checkbox" name="condutas_permanentes[]" value="<?= e($conduta) ?>" <?= $checked($condutasSelecionadas, $conduta) ?>>
                    <?= e($conduta) ?>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="span-2 form-section-title">
            <h3>Documentos</h3>
        </div>

        <label>
            Convenio
            <input type="text" name="convenio" value="<?= e($paciente['convenio'] ?? '') ?>">
        </label>

        <label>
            Numero da carteirinha
            <input type="text" name="numero_carteirinha" value="<?= e($paciente['numero_carteirinha'] ?? '') ?>">
        </label>

        <label class="span-2">
            Prescricao medica
            <textarea name="prescricao_medica" rows="3"><?= e($paciente['prescricao_medica'] ?? '') ?></textarea>
        </label>

        <label class="span-2">
            Termos assinados
            <textarea name="termos_assinados" rows="3"><?= e($paciente['termos_assinados'] ?? '') ?></textarea>
        </label>

        <label class="span-2">
            Observacoes clinicas
            <textarea name="observacoes_clinicas" rows="5"><?= e($paciente['observacoes_clinicas'] ?? '') ?></textarea>
        </label>

        <label>
            Status
            <select name="status">
                <option value="Ativo" <?= $selected($paciente['status'] ?? 'Ativo', 'Ativo') ?>>Ativo</option>
                <option value="Inativo" <?= $selected($paciente['status'] ?? '', 'Inativo') ?>>Inativo</option>
            </select>
        </label>

        <label class="span-2">
            Motivo de inativacao
            <textarea name="motivo_inativacao" rows="3"><?= e($paciente['motivo_inativacao'] ?? '') ?></textarea>
        </label>

        <div class="form-actions span-2">
            <button class="btn btn-primary" type="submit">
                <?= $isEdit ? 'Salvar alteracoes' : 'Cadastrar paciente' ?>
            </button>
            <a class="btn btn-secondary" href="<?= url('/pacientes') ?>">Cancelar</a>
        </div>
    </form>
</section>

<template id="med-row-template">
    <div class="med-form-row">
        <input type="hidden" data-name="id" value="0">
        <div class="med-form-index"></div>
        <label>Medicamento <input type="text" data-name="nome_medicamento"></label>
        <label>Dosagem <input type="text" data-name="dosagem" placeholder="Ex.: 50 mg"></label>
        <label>Horarios <input type="text" data-name="horarios" placeholder="Ex.: 08:00, 20:00"></label>
        <label>
            Via
            <select data-name="via">
                <option value="">Selecione...</option>
                <?php foreach ($vias as $via => $viaLabel): ?>
                    <option value="<?= e($via) ?>"><?= e($viaLabel) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Frequencia <input type="text" data-name="frequencia"></label>
        <label>
            Status
            <select data-name="status">
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
            </select>
        </label>
        <label class="med-form-obs">Observacoes <input type="text" data-name="observacoes"></label>
        <label class="med-form-remove"><input type="checkbox" data-name="_delete" value="1"> Remover</label>
    </div>
</template>

<script>
document.getElementById('btn-add-med')?.addEventListener('click', () => {
    const list = document.getElementById('med-list-form');
    const tpl = document.getElementById('med-row-template');
    if (!list || !tpl) return;

    const idx = list.querySelectorAll('.med-form-row').length;
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('.med-form-index').textContent = idx + 1;
    node.querySelectorAll('[data-name]').forEach((el) => {
        el.name = `medicacoes_continuas[${idx}][${el.dataset.name}]`;
    });
    list.appendChild(node);
});
</script>
