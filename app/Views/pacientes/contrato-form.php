<?php
$paciente = $paciente ?? [];
$record = $record ?? [];
$errors = $errors ?? [];
$responsaveisOptions = $responsaveisOptions ?? [];
$action = $action ?? '';
$isEdit = !empty($isEdit);

$value = static fn(string $key, string $default = ''): string => (string)($record[$key] ?? $default);
$checkedServico = static function (string $servico) use ($record): string {
    $lista = $record['servicos_contratados'] ?? ($record['servicos_lista'] ?? []);
    if (is_string($lista)) {
        $decoded = json_decode($lista, true);
        $lista = is_array($decoded) ? $decoded : [];
    }
    return in_array($servico, is_array($lista) ? $lista : [], true) ? 'checked' : '';
};
$selected = static fn(string $key, string $expected): string => ((string)($record[$key] ?? '') === $expected) ? 'selected' : '';
$selectedResponsavel = static fn(string $key, mixed $id): string => ((string)($record[$key] ?? '') === (string)$id) ? 'selected' : '';
$fmtDateView = static function (?string $date): string {
    if (!$date) {
        return '—';
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : '—';
};
$servicos = ['Cuidador', 'Técnico de Enfermagem', 'Enfermeiro', 'Fisioterapeuta', 'Acompanhante Hospitalar', 'Outros'];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsaveis_paciente_patch.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pacientes_contrato_escala_patch.css">

<section class="panel contrato-form-panel">
    <div class="panel-header contrato-paciente-head">
        <div>
            <h1><?= e($title ?? ($isEdit ? 'Editar contrato' : 'Novo contrato')) ?></h1>
            <p class="page-subtitle">
                Cadastro do contrato vinculado ao paciente. Dados do paciente são preenchidos automaticamente e o restante vira a régua do financeiro.
            </p>
        </div>
        <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '?aba=contratos') ?>">Voltar</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Revise os campos:</strong>
            <ul>
                <?php foreach ($errors as $erro): ?>
                    <li><?= e($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="contrato-form-summary-grid">
        <article class="ce-card">
            <span class="ce-label">Dados do paciente</span>
            <strong><?= e($paciente['nome_completo'] ?? 'Paciente') ?></strong>
            <p>CPF: <?= e($paciente['cpf'] ?? '—') ?> · RG: <?= e($paciente['rg'] ?? '—') ?></p>
            <p>DN: <?= e($fmtDateView($paciente['data_nascimento'] ?? null)) ?></p>
            <p><?= e($paciente['endereco_completo'] ?? 'Endereço não informado') ?></p>
            <p><?= e($paciente['telefone_principal'] ?? 'Telefone não informado') ?> · <?= e($paciente['email'] ?? 'E-mail não informado') ?></p>
        </article>

        <article class="ce-card">
            <span class="ce-label">Responsável atual do cadastro</span>
            <strong><?= e($paciente['responsavel_nome'] ?? 'Não informado') ?></strong>
            <p>CPF: <?= e($paciente['responsavel_cpf'] ?? '—') ?></p>
            <p><?= e($paciente['responsavel_parentesco'] ?? 'Parentesco não informado') ?> · <?= e($paciente['responsavel_telefone'] ?? 'Telefone não informado') ?></p>
            <p><?= e($paciente['responsavel_email'] ?? 'E-mail não informado') ?></p>
        </article>
    </div>

    <form method="POST" action="<?= url($action) ?>" class="contrato-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Responsáveis do contrato</h2>
                <p>Legal e financeiro podem ser a mesma pessoa, mas o sistema permite separar.</p>
            </div>

            <div class="contrato-form-grid two">
                <label>
                    Responsável legal
                    <select name="responsavel_legal_id">
                        <option value="">Selecione</option>
                        <?php foreach ($responsaveisOptions as $resp): ?>
                            <option value="<?= e((string)$resp['id']) ?>" <?= $selectedResponsavel('responsavel_legal_id', $resp['id']) ?>>
                                <?= e($resp['nome_completo'] ?? '') ?><?= !empty($resp['grau_parentesco']) ? ' — ' . e($resp['grau_parentesco']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Responsável financeiro
                    <select name="responsavel_financeiro_id">
                        <option value="">Selecione</option>
                        <?php foreach ($responsaveisOptions as $resp): ?>
                            <option value="<?= e((string)$resp['id']) ?>" <?= $selectedResponsavel('responsavel_financeiro_id', $resp['id']) ?>>
                                <?= e($resp['nome_completo'] ?? '') ?><?= !empty($resp['grau_parentesco']) ? ' — ' . e($resp['grau_parentesco']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
        </section>

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Dados da empresa</h2>
                <p>Esses dados ficam gravados como retrato do contrato.</p>
            </div>

            <div class="contrato-form-grid two">
                <label>
                    Razão social
                    <input type="text" name="empresa_razao_social" value="<?= e($value('empresa_razao_social', 'Cuidar no Lar')) ?>">
                </label>
                <label>
                    CNPJ
                    <input type="text" name="empresa_cnpj" value="<?= e($value('empresa_cnpj')) ?>">
                </label>
                <label class="wide">
                    Endereço da empresa
                    <input type="text" name="empresa_endereco" value="<?= e($value('empresa_endereco')) ?>">
                </label>
                <label class="wide">
                    Responsável pelo contrato
                    <input type="text" name="empresa_responsavel_contrato" value="<?= e($value('empresa_responsavel_contrato')) ?>">
                </label>
            </div>
        </section>

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Assistência contratada</h2>
                <p>Serviço contratado e padrão da escala. Aqui nasce a regra de operação.</p>
            </div>

            <div class="contrato-check-grid">
                <?php foreach ($servicos as $servico): ?>
                    <label>
                        <input type="checkbox" name="servicos_contratados[]" value="<?= e($servico) ?>" <?= $checkedServico($servico) ?>>
                        <?= e($servico) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="contrato-form-grid three">
                <label>
                    Descrição resumida do serviço
                    <input type="text" name="tipo_servico" value="<?= e($value('tipo_servico')) ?>" placeholder="Ex.: Home care 12x36 diurno">
                </label>
                <label>
                    Escala contratada
                    <select name="escala_contratada" data-contrato-escala>
                        <option value="">Selecione</option>
                        <option <?= $selected('escala_contratada', '12x36 Diurno') ?>>12x36 Diurno</option>
                        <option <?= $selected('escala_contratada', '12x36 Noturno') ?>>12x36 Noturno</option>
                        <option <?= $selected('escala_contratada', '24 horas') ?>>24 horas</option>
                        <option <?= $selected('escala_contratada', 'Segunda a Sexta') ?>>Segunda a Sexta</option>
                        <option <?= $selected('escala_contratada', 'Personalizada') ?>>Personalizada</option>
                    </select>
                </label>
                <label>
                    Tipo de plantão
                    <select name="tipo_plantao">
                        <option value="">Automático pela escala</option>
                        <option value="6h" <?= $selected('tipo_plantao', '6h') ?>>6h</option>
                        <option value="8h" <?= $selected('tipo_plantao', '8h') ?>>8h</option>
                        <option value="12h" <?= $selected('tipo_plantao', '12h') ?>>12h</option>
                        <option value="24h" <?= $selected('tipo_plantao', '24h') ?>>24h</option>
                    </select>
                </label>
                <label>
                    Horário início padrão
                    <input type="time" name="hora_inicio_padrao" value="<?= e(substr($value('hora_inicio_padrao'), 0, 5)) ?>">
                </label>
                <label>
                    Horário fim padrão
                    <input type="time" name="hora_fim_padrao" value="<?= e(substr($value('hora_fim_padrao'), 0, 5)) ?>">
                </label>
            </div>
        </section>

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Vigência</h2>
                <p>Contrato determinado precisa de término. Indeterminado fica em aberto.</p>
            </div>

            <div class="contrato-form-grid three">
                <label>
                    Data de início
                    <input type="date" name="vigencia_inicio" value="<?= e($value('vigencia_inicio', date('Y-m-d'))) ?>">
                </label>
                <label>
                    Previsão de término
                    <input type="date" name="vigencia_fim" value="<?= e($value('vigencia_fim')) ?>">
                </label>
                <label>
                    Tipo de prazo
                    <select name="tipo_prazo">
                        <option value="Indeterminado" <?= $selected('tipo_prazo', 'Indeterminado') ?>>Indeterminado</option>
                        <option value="Determinado" <?= $selected('tipo_prazo', 'Determinado') ?>>Determinado</option>
                    </select>
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="Ativo" <?= $selected('status', 'Ativo') ?>>Ativo</option>
                        <option value="Suspenso" <?= $selected('status', 'Suspenso') ?>>Suspenso</option>
                        <option value="Encerrado" <?= $selected('status', 'Encerrado') ?>>Encerrado</option>
                        <option value="Cancelado" <?= $selected('status', 'Cancelado') ?>>Cancelado</option>
                    </select>
                </label>
            </div>
        </section>

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Informações financeiras</h2>
                <p>Daqui saem as contas a receber. Se preencher bem aqui, o financeiro trabalha limpo.</p>
            </div>

            <div class="contrato-form-grid three contrato-financeiro-grid">
                <label>
                    Tipo de cobrança
                    <select name="tipo_cobranca" data-contrato-tipo-cobranca>
                        <option value="Mensal" <?= $selected('tipo_cobranca', 'Mensal') ?>>Mensal</option>
                        <option value="Semanal" <?= $selected('tipo_cobranca', 'Semanal') ?>>Semanal</option>
                        <option value="Por plantão" <?= $selected('tipo_cobranca', 'Por plantão') ?>>Por plantão</option>
                    </select>
                </label>
                <label>
                    Valor mensal
                    <input type="text" name="valor_mensal" value="<?= e($value('valor_mensal')) ?>" placeholder="0,00" data-contrato-valor-mensal>
                    <small>Usado quando a cobrança for mensal. Também pode ser estimado pelo plantão.</small>
                </label>
                <label>
                    Valor semanal
                    <input type="text" name="valor_semanal" value="<?= e($value('valor_semanal')) ?>" placeholder="0,00" data-contrato-valor-semanal>
                    <small>Usado quando a cobrança for semanal. Também pode ser estimado pelo plantão.</small>
                </label>
                <label>
                    Valor por plantão
                    <input type="text" name="valor_plantao" value="<?= e($value('valor_plantao')) ?>" placeholder="0,00" data-contrato-valor-plantao>
                    <small>Para cobrança por plantão, este é o único valor obrigatório.</small>
                </label>
                <label>
                    Forma de pagamento
                    <select name="forma_pagamento">
                        <option value="">Selecione</option>
                        <option value="PIX" <?= $selected('forma_pagamento', 'PIX') ?>>PIX</option>
                        <option value="Boleto" <?= $selected('forma_pagamento', 'Boleto') ?>>Boleto</option>
                        <option value="Cartão" <?= $selected('forma_pagamento', 'Cartão') ?>>Cartão</option>
                        <option value="Transferência" <?= $selected('forma_pagamento', 'Transferência') ?>>Transferência</option>
                    </select>
                </label>
                <label>
                    Vencimento: dia do mês
                    <input type="number" min="1" max="31" name="dia_vencimento" value="<?= e($value('dia_vencimento', '10')) ?>">
                </label>
                <label>
                    Multa (%)
                    <input type="text" name="multa_percentual" value="<?= e($value('multa_percentual')) ?>" placeholder="Ex.: 2,00">
                </label>
                <label>
                    Juros (%)
                    <input type="text" name="juros_percentual" value="<?= e($value('juros_percentual')) ?>" placeholder="Ex.: 1,00">
                </label>
                <p class="contrato-calc-note wide" data-contrato-calc-note>
                    O valor mensal/semanal pode ser calculado automaticamente conforme o tipo de cobrança e a escala contratada. Os valores continuam editáveis.
                </p>
            </div>
        </section>

        <section class="contrato-fieldset">
            <div class="contrato-fieldset-title">
                <h2>Observações</h2>
            </div>
            <label>
                Observações contratuais
                <textarea name="observacoes" rows="4" placeholder="Cláusulas internas, particularidades, observações de cobrança etc."><?= e($value('observacoes')) ?></textarea>
            </label>
        </section>

        <div class="form-actions contrato-form-actions">
            <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode((string)($paciente['uuid'] ?? $paciente['id'])) . '?aba=contratos') ?>">Cancelar</a>
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar alterações' : 'Cadastrar contrato' ?></button>
        </div>
    </form>
</section>


<script>
(function () {
    const form = document.querySelector('.contrato-form');
    if (!form) return;

    const tipo = form.querySelector('[data-contrato-tipo-cobranca]');
    const escala = form.querySelector('[data-contrato-escala]');
    const mensal = form.querySelector('[data-contrato-valor-mensal]');
    const semanal = form.querySelector('[data-contrato-valor-semanal]');
    const plantao = form.querySelector('[data-contrato-valor-plantao]');
    const note = form.querySelector('[data-contrato-calc-note]');

    const parseMoney = (value) => {
        value = String(value || '').replace(/R\$/g, '').replace(/\s/g, '').trim();
        if (!value) return 0;
        if (value.includes(',') && value.includes('.')) {
            value = value.replace(/\./g, '').replace(',', '.');
        } else if (value.includes(',')) {
            value = value.replace(',', '.');
        }
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatMoney = (value) => {
        if (!Number.isFinite(value) || value <= 0) return '';
        return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const fatoresPlantao = () => {
        const texto = String(escala?.value || '').toLowerCase();
        if (texto.includes('12x36') || texto.includes('12x')) {
            return { semana: 3.5, mes: 15, label: '12x36: média de 3,5 plantões/semana e 15 plantões/mês.' };
        }
        if (texto.includes('24')) {
            return { semana: 7, mes: 30, label: '24h: média de 7 plantões/semana e 30 plantões/mês.' };
        }
        if (texto.includes('segunda') || texto.includes('sexta')) {
            return { semana: 5, mes: 22, label: 'Segunda a sexta: média de 5 plantões/semana e 22 plantões/mês.' };
        }
        return { semana: 1, mes: 1, label: 'Escala personalizada: informe ou ajuste os valores manualmente.' };
    };

    const setNote = (message) => {
        if (note) note.textContent = message;
    };

    const calcular = (origem) => {
        const tipoAtual = tipo?.value || 'Mensal';

        if (tipoAtual === 'Mensal') {
            const valorMensal = parseMoney(mensal?.value);
            if (valorMensal > 0 && origem !== 'semanal') {
                semanal.value = formatMoney(valorMensal / 4.3333);
            }
            setNote('Cobrança mensal: o semanal é apenas uma referência média. O financeiro gera pelo valor mensal.');
            return;
        }

        if (tipoAtual === 'Semanal') {
            const valorSemanal = parseMoney(semanal?.value);
            if (valorSemanal > 0 && origem !== 'mensal') {
                mensal.value = formatMoney(valorSemanal * 4.3333);
            }
            setNote('Cobrança semanal: o mensal é uma referência média. O financeiro calcula pelo período ativo do contrato.');
            return;
        }

        if (tipoAtual === 'Por plantão') {
            const valorPlantao = parseMoney(plantao?.value);
            const fatores = fatoresPlantao();
            if (valorPlantao > 0) {
                semanal.value = formatMoney(valorPlantao * fatores.semana);
                mensal.value = formatMoney(valorPlantao * fatores.mes);
            }
            setNote('Cobrança por plantão: somente o valor por plantão é obrigatório. ' + fatores.label);
        }
    };

    tipo?.addEventListener('change', () => calcular('tipo'));
    escala?.addEventListener('change', () => calcular('escala'));
    mensal?.addEventListener('input', () => calcular('mensal'));
    semanal?.addEventListener('input', () => calcular('semanal'));
    plantao?.addEventListener('input', () => calcular('plantao'));

    calcular('init');
})();
</script>
