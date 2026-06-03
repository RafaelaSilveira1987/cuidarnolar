<?php
$paciente = isset($paciente) && is_array($paciente) ? $paciente : [];
$record = isset($record) && is_array($record) ? $record : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$modelosPlano = isset($modelosPlano) && is_array($modelosPlano) ? $modelosPlano : [];
$routeBase = (string)($routeBase ?? '/pacientes');
$resourceKey = (string)($paciente['uuid'] ?? $paciente['id'] ?? '');
$action = (string)($action ?? '');
$isEdit = (bool)($isEdit ?? false);

$val = static function (string $key, string $fallback = '') use ($record): string {
    return (string)($record[$key] ?? $fallback);
};

$areas = [
    'resumo_clinico' => ['Resumo clínico do plano', 'Dados principais usados como referência para a equipe.'],
    'objetivos' => ['Objetivos do cuidado *', 'Metas assistenciais e foco principal do acompanhamento.'],
    'monitoramento' => ['Monitoramento', 'O que observar em cada plantão e o que registrar.'],
    'oxigenoterapia' => ['Oxigenoterapia', 'Condutas de segurança e uso conforme prescrição/orientação.'],
    'nebulizacao' => ['Nebulização', 'Rotina, higienização, resposta e registros.'],
    'controle_ambiental' => ['Controle ambiental', 'Ambiente, gatilhos, segurança e conforto.'],
    'alimentacao_hidratacao' => ['Via alimentar e hidratação', 'Orientações de alimentação, aceitação, hidratação e intercorrências.'],
    'atividade_repouso' => ['Atividade física e repouso', 'Mobilidade, repouso, posicionamento e limites.'],
    'medicamentos' => ['Medicamentos', 'Conferência, registro, recusas e limites de atuação.'],
    'comunicacao_familia' => ['Comunicação com a família', 'Quem acionar, quando acionar e como registrar.'],
    'sinais_alerta' => ['Sinais de alerta', 'Critérios para acionar responsável, equipe ou urgência.'],
    'observacoes' => ['Observações', 'Ajustes, pendências e orientações adicionais.'],
];
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/plano_cuidado.css">

<section class="page-header plano-form-header">
    <div>
        <h1><?= e($title ?? ($isEdit ? 'Editar plano de cuidados' : 'Novo plano de cuidados')) ?></h1>
        <p class="page-subtitle">
            Paciente: <strong><?= e($paciente['nome_completo'] ?? 'Paciente') ?></strong>.
            O rascunho automático é ponto de partida: revise antes de ativar.
        </p>
    </div>

    <a class="btn btn-secondary" href="<?= url($routeBase . '/' . rawurlencode($resourceKey) . '?aba=plano') ?>">Voltar</a>
</section>

<section class="panel plano-form-panel">
    <?php if ($errors): ?>
    <div class="alert alert-error">
        <?php foreach ($errors as $msg): ?>
        <div><?= e($msg) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= url($action) ?>" class="plano-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="plano-form-grid">
            <label class="span-2">
                Título *
                <input type="text" name="titulo" value="<?= e($val('titulo', 'Plano de Cuidados Home Care')) ?>" required>
            </label>

            <label class="span-2">
                Subtítulo / resumo curto
                <input type="text" name="subtitulo" value="<?= e($val('subtitulo')) ?>" placeholder="Ex.: diagnóstico | idade | mobilidade | cognição">
            </label>

            <label>
                Modelo de origem
                <select name="modelo_chave">
                    <option value="">Manual / sem modelo</option>
                    <?php foreach ($modelosPlano as $modelo): ?>
                    <?php $chave = (string)($modelo['chave'] ?? ''); ?>
                    <option value="<?= e($chave) ?>" <?= $val('modelo_chave') === $chave ? 'selected' : '' ?>><?= e($modelo['nome'] ?? $chave) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Responsável técnico
                <input type="text" name="responsavel_tecnico" value="<?= e($val('responsavel_tecnico')) ?>" placeholder="Nome do profissional responsável">
            </label>

            <label>
                Data de início *
                <input type="date" name="data_inicio" value="<?= e($val('data_inicio', date('Y-m-d'))) ?>" required>
            </label>

            <label>
                Próxima revisão
                <input type="date" name="data_revisao" value="<?= e($val('data_revisao')) ?>">
            </label>

            <label>
                Status
                <select name="status">
                    <?php foreach (['Rascunho', 'Ativo', 'Revisado', 'Arquivado'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= $val('status', 'Rascunho') === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Versão
                <input type="number" min="1" name="versao" value="<?= e($val('versao', '1')) ?>">
            </label>
        </div>

        <div class="plano-form-note">
            <strong>Atenção:</strong> medicação, oxigênio, nebulização, doses e limites clínicos devem respeitar prescrição/orientação profissional. O sistema gera rascunho, não prescrição automática.
        </div>

        <div class="plano-section-editor">
            <?php foreach ($areas as $campo => [$label, $help]): ?>
            <label class="plano-textarea-block">
                <span><?= e($label) ?></span>
                <small><?= e($help) ?></small>
                <textarea name="<?= e($campo) ?>" rows="<?= $campo === 'resumo_clinico' ? 5 : 7 ?>"><?= e($val($campo)) ?></textarea>
            </label>
            <?php endforeach; ?>
        </div>

        <div class="button-row plano-form-actions">
            <button class="btn btn-primary" type="submit">Salvar plano</button>
            <a class="btn btn-secondary" href="<?= url($routeBase . '/' . rawurlencode($resourceKey) . '?aba=plano') ?>">Cancelar</a>
        </div>
    </form>
</section>
