<?php

/**
 * app/Views/pacientes/show.php
 * Tela de exibição do paciente — versão com topo clínico limpo.
 */



$paciente = isset($paciente) && is_array($paciente)
    ? $paciente
    : [];

$record = isset($record) && is_array($record)
    ? $record
    : $paciente;


$fields = $fields ?? [
    'id' => '#',
    'prontuario' => 'Prontuário',
    'nome_completo' => 'Nome completo',
    'data_nascimento' => 'Nascimento',
    'idade' => 'Idade',
    'sexo' => 'Sexo',
    'cpf' => 'CPF',
    'rg' => 'RG',
    'cartao_nac_sus' => 'Cartão Nacional SUS',
    'endereco_completo' => 'Endereço',
    'telefone_principal' => 'Telefone principal',
    'telefone_secundario' => 'Telefone secundário',
    'email' => 'E-mail',
    'convenio' => 'Convênio',
    'numero_carteirinha' => 'Nº carteirinha',
    'responsavel_nome' => 'Responsável',
    'responsavel_parentesco' => 'Parentesco',
    'responsavel_telefone' => 'Telefone do responsável',
    'responsavel_email' => 'E-mail do responsável',
    'diagnostico' => 'Diagnóstico',
    'cid_principal' => 'CID principal',
    'diagnostico_principal' => 'Diagnóstico principal',
    'comorbidades' => 'Comorbidades',
    'alergias' => 'Alergias',
    'tipo_sanguineo' => 'Tipo sanguíneo',
    'peso' => 'Peso',
    'altura' => 'Altura',
    'alimentacao_via' => 'Via alimentar',
    'mobilidade' => 'Mobilidade',
    'estado_cognitivo_base' => 'Estado cognitivo',
    'usa_oxigenio' => 'Usa oxigênio',
    'usa_sonda' => 'Usa sonda',
    'traqueostomia' => 'Traqueostomia',
    'gastrostomia' => 'Gastrostomia',
    'colostomia' => 'Colostomia',
    'cateter_vesical' => 'Cateter vesical',
    'gtt' => 'GTT',
    'sne' => 'SNE',
    'picc' => 'PICC',
    'curativos' => 'Curativos',
    'observacoes_clinicas' => 'Observações clínicas',
    'status' => 'Status',
];

// Mantém compatibilidade: se a tela abriu por /pacientes/12, continua usando 12.
// Se abriu por /pacientes/uuid, continua usando uuid.
$currentPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
$currentKey = trim((string)basename($currentPath));

if ($currentKey === '' || in_array($currentKey, ['pacientes', 'editar', 'novo'], true)) {
    $currentKey = (string)($paciente['uuid'] ?? ($paciente['id'] ?? ''));
}

$tabUrl = static fn(string $a): string => url('/pacientes/' . rawurlencode($currentKey) . '?aba=' . urlencode($a));

$abas = [
    'cadastro' => 'Dados cadastrais',
    'responsaveis' => 'Responsáveis e contatos',
    'anamnese' => 'Anamnese',
    'medicacoes' => 'Medicações',
    'historico' => 'Histórico clínico',
    'plano' => 'Plano de cuidados',
    'plantao' => 'Relatórios de plantão',
    'contrato_escala' => 'Contrato e escala',
];

$abaAtiva = (string)($_GET['aba'] ?? 'cadastro');

if (!array_key_exists($abaAtiva, $abas)) {
    $abaAtiva = 'cadastro';
}

function paciente_calcular_idade(?string $dataNascimento): string
{
    if (empty($dataNascimento)) {
        return '-';
    }

    try {
        $nascimento = new DateTime($dataNascimento);
        $hoje = new DateTime();

        return (string) $nascimento->diff($hoje)->y;
    } catch (Exception $e) {
        return '-';
    }
}

$idadeCalculada = paciente_calcular_idade($paciente['data_nascimento'] ?? null);

$paciente['idade'] = $idadeCalculada;
$record['idade'] = $idadeCalculada;

$valor = static function (mixed $v, string $fallback = '—'): string {
    $v = trim((string)$v);
    return $v !== '' ? $v : $fallback;
};

$sim = static function (mixed $v): bool {
    return mb_strtolower(trim((string)$v), 'UTF-8') === 'sim';
};

$fmtDate = static function (?string $date, string $fallback = '—'): string {
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $fallback;
};

$idade = static function (?string $date): string {
    if (!$date) {
        return 'Idade não informada';
    }

    try {
        $nasc = new DateTime($date);
        $hoje = new DateTime('today');
        $anos = $nasc->diff($hoje)->y;
        return $anos . ' ano' . ($anos === 1 ? '' : 's');
    } catch (Throwable) {
        return 'Idade não informada';
    }
};

$nomePaciente = $valor($paciente['nome_completo'] ?? '', 'Paciente');
$prontuario = $valor($paciente['prontuario'] ?? '', 'Sem prontuário');
$status = $valor($paciente['status'] ?? '', 'Não informado');
$dataNascimento = $paciente['data_nascimento'] ?? null;
$idadePaciente = $idade($dataNascimento);

$diagnostico = $valor($paciente['diagnostico'] ?? $paciente['diagnostico_principal'] ?? '', 'Não informado');
$cid = $valor($paciente['cid_principal'] ?? '', 'Não informado');
$convenio = $valor($paciente['convenio'] ?? $paciente['plano_saude'] ?? '', 'Não informado');
$responsavel = $valor($paciente['responsavel_nome'] ?? $paciente['responsavel_nome_texto'] ?? '', 'Não informado');
$responsavelTelefone = $valor($paciente['responsavel_telefone'] ?? '', 'Não informado');
$cuidador = $valor($paciente['cuidador_nome'] ?? '', 'Não informado');
$mobilidade = $valor($paciente['mobilidade'] ?? '', 'Não informado');
$cognicao = $valor($paciente['estado_cognitivo_base'] ?? '', 'Não informado');
$alimentacaoVia = $valor($paciente['alimentacao_via'] ?? '', 'Não informado');

$tagsClinicas = [];

if ($sim($paciente['usa_oxigenio'] ?? 'Não')) {
    $tagsClinicas[] = 'Oxigênio';
}
if ($sim($paciente['usa_sonda'] ?? 'Não')) {
    $tagsClinicas[] = 'Sonda';
}
if ($sim($paciente['traqueostomia'] ?? 'Não')) {
    $tagsClinicas[] = 'Traqueostomia';
}
if ($sim($paciente['gtt'] ?? $paciente['gastrostomia'] ?? 'Não')) {
    $tagsClinicas[] = 'GTT';
}
if ($sim($paciente['sne'] ?? 'Não')) {
    $tagsClinicas[] = 'SNE';
}
if ($sim($paciente['cateter_vesical'] ?? 'Não')) {
    $tagsClinicas[] = 'Cateter vesical';
}
if ($sim($paciente['cateter_venoso'] ?? 'Não')) {
    $tagsClinicas[] = 'Cateter venoso';
}
if ($sim($paciente['picc'] ?? 'Não')) {
    $tagsClinicas[] = 'PICC';
}
if ($sim($paciente['lesao_pressao'] ?? 'Não')) {
    $tagsClinicas[] = 'Lesão por pressão';
}
if (trim((string)($paciente['alergias'] ?? '')) !== '') {
    $tagsClinicas[] = 'Alergia registrada';
}
if (trim((string)($paciente['curativos'] ?? '')) !== '') {
    $tagsClinicas[] = 'Curativos';
}
if ($alimentacaoVia !== 'Não informado') {
    $tagsClinicas[] = 'Via alimentar: ' . $alimentacaoVia;
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsaveis_paciente_patch.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pacientes_contrato_escala_patch.css">

<section class="pac-profile-hero">
    <div class="pac-profile-main">
        <div class="pac-profile-avatar" aria-hidden="true">
            <?= e(mb_strtoupper(mb_substr($nomePaciente, 0, 1, 'UTF-8'), 'UTF-8')) ?>
        </div>

        <div class="pac-profile-title">
            <span class="pac-profile-kicker">Ficha do paciente</span>
            <h1>Paciente — <?= e($nomePaciente) ?></h1>

            <div class="pac-profile-meta">
                <span><strong>Prontuário:</strong> <?= e($prontuario) ?></span>
                <span><strong>Status:</strong> <?= e($status) ?></span>
                <span><strong>DN:</strong> <?= e($fmtDate($dataNascimento)) ?></span>
                <span><strong>Idade:</strong> <?= e($idadePaciente) ?></span>
            </div>
        </div>
    </div>

    <div class="button-row pac-profile-actions">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>
        <a class="btn btn-primary" href="<?= url($routeBase . '/' . rawurlencode($currentKey) . '/editar') ?>">Editar
            cadastro</a>
    </div>
</section>

<section class="pac-quick-panel" aria-label="Resumo rápido do paciente">
    <div class="pac-quick-grid">
        <div class="pac-quick-item pac-quick-item--wide">
            <span>Diagnóstico principal</span>
            <strong><?= e($diagnostico) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Convênio</span>
            <strong><?= e($convenio) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Responsável</span>
            <strong><?= e($responsavel) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Telefone</span>
            <strong><?= e($responsavelTelefone) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Cuidador referência</span>
            <strong><?= e($cuidador) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Mobilidade</span>
            <strong><?= e($mobilidade) ?></strong>
        </div>

        <div class="pac-quick-item">
            <span>Cognição</span>
            <strong><?= e($cognicao) ?></strong>
        </div>
    </div>

    <div class="pac-clinical-tags" aria-label="Tags clínicas">
        <?php if (!empty($tagsClinicas)): ?>
        <?php foreach ($tagsClinicas as $tag): ?>
        <span><?= e($tag) ?></span>
        <?php endforeach; ?>
        <?php else: ?>
        <span>Sem dispositivos críticos informados</span>
        <?php endif; ?>
    </div>
</section>

<nav class="patient-tabs">
    <?php foreach ($abas as $key => $label): ?>
    <a href="<?= $tabUrl($key) ?>" class="<?= $abaAtiva === $key ? 'active' : '' ?>">
        <?= e($label) ?>
    </a>
    <?php endforeach; ?>
</nav>

<section class="panel pac-clinical-summary">
    <div class="panel-header">
        <h2>Resumo clínico</h2>
        <p class="page-subtitle">Visão rápida das informações mais importantes para assistência e tomada de decisão.</p>
    </div>

    <div class="pac-summary-grid">
        <article class="pac-summary-box">
            <h3>Resumo assistencial</h3>

            <dl>
                <div>
                    <dt>Diagnóstico</dt>
                    <dd><?= e($diagnostico) ?></dd>
                </div>
                <div>
                    <dt>CID</dt>
                    <dd><?= e($cid) ?></dd>
                </div>
                <div>
                    <dt>Mobilidade</dt>
                    <dd><?= e($mobilidade) ?></dd>
                </div>
                <div>
                    <dt>Cognição</dt>
                    <dd><?= e($cognicao) ?></dd>
                </div>
                <div>
                    <dt>Via alimentar</dt>
                    <dd><?= e($alimentacaoVia) ?></dd>
                </div>
            </dl>
        </article>

        <article class="pac-summary-box">
            <h3>Responsável e contato</h3>

            <dl>
                <div>
                    <dt>Responsável</dt>
                    <dd><?= e($responsavel) ?></dd>
                </div>
                <div>
                    <dt>Parentesco</dt>
                    <dd><?= e($valor($paciente['responsavel_parentesco'] ?? '', 'Não informado')) ?></dd>
                </div>
                <div>
                    <dt>Telefone</dt>
                    <dd><?= e($responsavelTelefone) ?></dd>
                </div>
                <div>
                    <dt>E-mail</dt>
                    <dd><?= e($valor($paciente['responsavel_email'] ?? $paciente['email'] ?? '', 'Não informado')) ?>
                    </dd>
                </div>
            </dl>
        </article>

        <article class="pac-summary-box">
            <h3>Dispositivos e cuidados</h3>

            <dl>
                <div>
                    <dt>Oxigênio</dt>
                    <dd><?= $sim($paciente['usa_oxigenio'] ?? 'Não') ? 'Sim' : 'Não' ?></dd>
                </div>
                <div>
                    <dt>GTT</dt>
                    <dd><?= $sim($paciente['gtt'] ?? $paciente['gastrostomia'] ?? 'Não') ? 'Sim' : 'Não' ?></dd>
                </div>
                <div>
                    <dt>SNE</dt>
                    <dd><?= $sim($paciente['sne'] ?? 'Não') ? 'Sim' : 'Não' ?></dd>
                </div>
                <div>
                    <dt>PICC</dt>
                    <dd><?= $sim($paciente['picc'] ?? 'Não') ? 'Sim' : 'Não' ?></dd>
                </div>
                <div>
                    <dt>Traqueostomia</dt>
                    <dd><?= $sim($paciente['traqueostomia'] ?? 'Não') ? 'Sim' : 'Não' ?></dd>
                </div>
                <div>
                    <dt>Curativos</dt>
                    <dd><?= e($valor($paciente['curativos'] ?? '', 'Não informado')) ?></dd>
                </div>
            </dl>
        </article>
    </div>

    <?php if (trim((string)($paciente['observacoes_clinicas'] ?? '')) !== ''): ?>
    <div class="pac-summary-note">
        <strong>Observações clínicas:</strong><br>
        <?= nl2br(e($paciente['observacoes_clinicas'])) ?>
    </div>
    <?php endif; ?>
</section>

<div class="patient-tab-content">

    <?php
    $partialMap = [
        'cadastro' => 'aba-cadastro.php',
        'responsaveis' => 'aba-responsaveis.php',
        'anamnese' => 'aba-anamnese.php',
        'medicacoes' => 'aba-medicacoes.php',
        'historico' => 'aba-historico.php',
        'plano' => 'aba-plano.php',
        'plantao' => 'aba-plantao.php',
        'contrato_escala' => 'aba-contrato-escala.php',
    ];

    $partialFile = $partialMap[$abaAtiva] ?? 'aba-cadastro.php';
    $partialPath = __DIR__ . '/partials/' . $partialFile;

    if (is_file($partialPath)) {
        include $partialPath;
    } else {
        echo '<div class="panel"><p class="empty-state">Conteúdo da aba não encontrado.</p></div>';
    }
    ?>

</div>