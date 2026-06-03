<?php
/**
 * app/Views/pacientes/plano-pdf.php
 * View exclusiva para exportação em PDF do Plano de Cuidados.
 */

if (!function_exists('pdf_plano_e')) {
    function pdf_plano_e(mixed $value, string $fallback = '-'): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            $value = $fallback;
        }

        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('pdf_plano_date')) {
    function pdf_plano_date(?string $date, string $fallback = '-'): string
    {
        if (!$date) {
            return $fallback;
        }

        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : $fallback;
    }
}

if (!function_exists('pdf_plano_text')) {
    function pdf_plano_text(mixed $value, string $fallback = '-'): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            $value = $fallback;
        }

        return nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

if (!function_exists('pdf_plano_idade')) {
    function pdf_plano_idade(?string $dataNascimento): string
    {
        if (!$dataNascimento) {
            return '-';
        }

        try {
            $nasc = new DateTime($dataNascimento);
            $hoje = new DateTime('today');
            $anos = $nasc->diff($hoje)->y;
            return $anos . ' ano' . ($anos === 1 ? '' : 's');
        } catch (Throwable) {
            return '-';
        }
    }
}

$paciente = isset($paciente) && is_array($paciente) ? $paciente : [];
$plano = isset($plano) && is_array($plano) ? $plano : [];
$empresa = isset($empresa) && is_array($empresa) ? $empresa : [];

$nomePaciente = $paciente['nome_completo'] ?? 'Paciente';
$diagnostico = $paciente['diagnostico'] ?? $paciente['diagnostico_principal'] ?? '';
$cid = $paciente['cid_principal'] ?? '';
$responsavel = $paciente['responsavel_nome'] ?? $paciente['responsavel_nome_texto'] ?? '';
$responsavelTelefone = $paciente['responsavel_telefone'] ?? '';

$secoes = [
    'resumo_clinico' => 'Resumo clínico',
    'objetivos' => 'Objetivos do cuidado',
    'monitoramento' => 'Monitoramento',
    'oxigenoterapia' => 'Oxigenoterapia',
    'nebulizacao' => 'Nebulização',
    'controle_ambiental' => 'Controle ambiental',
    'alimentacao_hidratacao' => 'Via alimentar e hidratação',
    'atividade_repouso' => 'Atividade física e repouso',
    'medicamentos' => 'Medicamentos',
    'comunicacao_familia' => 'Comunicação com a família',
    'sinais_alerta' => 'Sinais de alerta',
    'observacoes' => 'Observações',
];
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <title><?= pdf_plano_e($plano['titulo'] ?? 'Plano de Cuidados') ?></title>
    <style>
    @page {
        margin: 26mm 18mm 22mm 18mm;
    }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        color: #172033;
        font-size: 11px;
        line-height: 1.45;
    }

    .header {
        border-bottom: 2px solid #01948e;
        padding-bottom: 10px;
        margin-bottom: 16px;
    }

    .brand {
        font-size: 18px;
        font-weight: 800;
        color: #02756c;
        margin: 0 0 2px 0;
    }

    .company {
        color: #64748b;
        font-size: 10px;
    }

    .title {
        margin: 18px 0 4px 0;
        font-size: 20px;
        color: #172033;
        line-height: 1.22;
    }

    .subtitle {
        margin: 0;
        color: #475569;
        font-size: 11px;
    }

    .meta-grid {
        width: 100%;
        border-collapse: collapse;
        margin: 14px 0 16px 0;
    }

    .meta-grid td {
        width: 50%;
        border: 1px solid #d9e2ec;
        padding: 7px 8px;
        vertical-align: top;
    }

    .label {
        display: block;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-size: 8px;
        font-weight: 800;
        margin-bottom: 2px;
    }

    .value {
        font-weight: 700;
        color: #172033;
    }

    .section {
        page-break-inside: avoid;
        margin: 0 0 13px 0;
    }

    .section h2 {
        margin: 0 0 5px 0;
        padding: 4px 6px;
        background: #e8fbf9;
        color: #02756c;
        border-left: 4px solid #01948e;
        font-size: 12px;
    }

    .section .text {
        border: 1px solid #e2e8f0;
        border-top: 0;
        padding: 8px 9px;
        white-space: normal;
    }

    .signatures {
        margin-top: 26px;
        width: 100%;
        border-collapse: collapse;
    }

    .signatures td {
        width: 50%;
        padding: 22px 12px 0 12px;
        text-align: center;
        color: #475569;
    }

    .line {
        border-top: 1px solid #64748b;
        padding-top: 6px;
    }

    .footer {
        position: fixed;
        bottom: -12mm;
        left: 0;
        right: 0;
        color: #94a3b8;
        font-size: 9px;
        text-align: center;
    }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand"><?= pdf_plano_e($empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Cuidar no Lar') ?>
        </div>
        <div class="company">
            <?= pdf_plano_e($empresa['razao_social'] ?? '') ?>
            <?php if (!empty($empresa['cnpj'])): ?> | CNPJ: <?= pdf_plano_e($empresa['cnpj']) ?><?php endif; ?>
            <?php if (!empty($empresa['telefone'])): ?> | Tel.: <?= pdf_plano_e($empresa['telefone']) ?><?php endif; ?>
            <?php if (!empty($empresa['email'])): ?> | <?= pdf_plano_e($empresa['email']) ?><?php endif; ?>
        </div>

        <h1 class="title"><?= pdf_plano_e($plano['titulo'] ?? 'Plano de Cuidados Home Care') ?></h1>
        <p class="subtitle"><?= pdf_plano_e($plano['subtitulo'] ?? '') ?></p>
    </div>

    <table class="meta-grid">
        <tr>
            <td><span class="label">Paciente</span><span class="value"><?= pdf_plano_e($nomePaciente) ?></span></td>
            <td><span class="label">Data de nascimento / idade</span><span
                    class="value"><?= pdf_plano_date($paciente['data_nascimento'] ?? null) ?> -
                    <?= pdf_plano_idade($paciente['data_nascimento'] ?? null) ?></span></td>
        </tr>
        <tr>
            <td><span class="label">Diagnóstico / CID</span><span
                    class="value"><?= pdf_plano_e($diagnostico) ?><?= $cid ? ' - CID ' . pdf_plano_e($cid) : '' ?></span>
            </td>
            <td><span class="label">Status / versão</span><span
                    class="value"><?= pdf_plano_e($plano['status'] ?? 'Rascunho') ?> - Versão
                    <?= (int)($plano['versao'] ?? 1) ?></span></td>
        </tr>
        <tr>
            <td><span class="label">Responsável</span><span
                    class="value"><?= pdf_plano_e($responsavel) ?><?= $responsavelTelefone ? ' - ' . pdf_plano_e($responsavelTelefone) : '' ?></span>
            </td>
            <td><span class="label">Responsável técnico</span><span
                    class="value"><?= pdf_plano_e($plano['responsavel_tecnico'] ?? '') ?></span></td>
        </tr>
        <tr>
            <td><span class="label">Início do plano</span><span
                    class="value"><?= pdf_plano_date($plano['data_inicio'] ?? null) ?></span></td>
            <td><span class="label">Próxima revisão</span><span
                    class="value"><?= pdf_plano_date($plano['data_revisao'] ?? null) ?></span></td>
        </tr>
    </table>

    <?php $contador = 1; ?>
    <?php foreach ($secoes as $campo => $titulo): ?>
    <?php $texto = trim((string)($plano[$campo] ?? '')); ?>
    <?php if ($texto !== ''): ?>
    <section class="section">
        <h2><?= $contador++ ?>. <?= pdf_plano_e($titulo) ?></h2>
        <div class="text"><?= pdf_plano_text($texto) ?></div>
    </section>
    <?php endif; ?>
    <?php endforeach; ?>

    <table class="signatures">
        <tr>
            <td>
                <div class="line">Responsável técnico</div>
            </td>
            <td>
                <div class="line">Responsável legal/familiar</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento gerado pelo sistema Cuidar no Lar em <?= date('d/m/Y H:i') ?>.
    </div>
</body>

</html>