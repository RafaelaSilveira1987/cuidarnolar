<?php
/**
 * app/Views/relatorio_plantao/pdf.php
 * View exclusiva para exportação em PDF do relatório de plantão.
 * Não depende do layout web, cards, accordion, JS ou CSS do sistema.
 */

if (!function_exists('pdf_e')) {
    function pdf_e(mixed $value, string $fallback = '-'): string
    {
        if ($value === null) {
            return $fallback;
        }

        $value = trim((string)$value);

        return $value !== ''
            ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            : $fallback;
    }
}

if (!function_exists('pdf_date')) {
    function pdf_date(?string $date, string $fallback = '-'): string
    {
        if (!$date) {
            return $fallback;
        }

        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : $fallback;
    }
}

if (!function_exists('pdf_time')) {
    function pdf_time(?string $date, string $fallback = '-'): string
    {
        if (!$date) {
            return $fallback;
        }

        $ts = strtotime($date);
        return $ts ? date('H:i', $ts) : $fallback;
    }
}

if (!function_exists('pdf_age')) {
    function pdf_age(?string $birthDate): string
    {
        if (!$birthDate || strtotime($birthDate) === false) {
            return '-';
        }

        try {
            return (string)(new DateTime($birthDate))->diff(new DateTime())->y . ' anos';
        } catch (Throwable $e) {
            return '-';
        }
    }
}

if (!function_exists('pdf_decode')) {
    function pdf_decode(mixed $value, string $fallback = '-'): string
    {
        if ($value === null) {
            return $fallback;
        }

        if (is_array($value)) {
            $decoded = $value;
        } else {
            $raw = trim((string)$value);
            if ($raw === '') {
                return $fallback;
            }

            $tentativa = json_decode($raw, true);
            $decoded = json_last_error() === JSON_ERROR_NONE && is_array($tentativa)
                ? $tentativa
                : null;

            if ($decoded === null) {
                return nl2br(htmlspecialchars($raw, ENT_QUOTES, 'UTF-8'));
            }
        }

        $linhas = [];
        foreach ($decoded as $item) {
            if (is_array($item)) {
                $partes = [];

                foreach (['horario', 'ml', 'medicamento', 'descricao', 'via', 'status', 'observacao'] as $campo) {
                    if (isset($item[$campo]) && trim((string)$item[$campo]) !== '') {
                        $partes[] = trim((string)$item[$campo]);
                    }
                }

                if (!empty($partes)) {
                    $linhas[] = '- ' . implode(' - ', $partes);
                }
            } else {
                $texto = trim((string)$item);
                if ($texto !== '') {
                    $linhas[] = '- ' . $texto;
                }
            }
        }

        if (empty($linhas)) {
            return $fallback;
        }

        return nl2br(htmlspecialchars(implode("\n", $linhas), ENT_QUOTES, 'UTF-8'));
    }
}

$relatorio = isset($relatorio) && is_array($relatorio) ? $relatorio : [];
$paciente = isset($paciente) && is_array($paciente) ? $paciente : [];

$nomePaciente = $paciente['nome_completo']
    ?? $paciente['nome']
    ?? $relatorio['paciente_nome']
    ?? 'Paciente';

$dataNascimento = $relatorio['data_nascimento']
    ?? $paciente['data_nascimento']
    ?? null;

$internacao = $relatorio['internacao']
    ?? $paciente['diagnostico']
    ?? $paciente['diagnostico_principal']
    ?? null;

$tipoLocal = (($relatorio['tipo_local'] ?? '') === 'domiciliar') ? 'Domiciliar' : 'Hospitalar';
$quarto = trim((string)($relatorio['quarto'] ?? ''));
$local = $quarto !== '' ? $quarto . ' / ' . $tipoLocal : $tipoLocal;

$profissional = $relatorio['profissional_nome']
    ?? $relatorio['responsavel_nome']
    ?? 'Não informado';

$hidratacaoRegistros = $relatorio['hidratacao_registros'] ?? null;
$urinaHorarios = $relatorio['urina_horarios'] ?? null;
$fezesHorarios = $relatorio['fezes_horarios'] ?? null;
$dispositivos = $relatorio['dispositivos'] ?? null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Plantão</title>
    <style>
        @page {
            margin: 24mm 18mm 18mm 18mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            font-size: 11.5px;
            line-height: 1.45;
            margin: 0;
        }

        .topo {
            border-bottom: 2px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 0;
            letter-spacing: .5px;
        }

        .subtitulo {
            text-align: center;
            margin: 4px 0 0;
            color: #4b5563;
            font-size: 10px;
        }

        .bloco {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 10px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .bloco.sem-quebra {
            page-break-inside: auto;
        }

        .bloco h2 {
            font-size: 12.5px;
            margin: 0 0 7px;
            text-transform: uppercase;
            color: #02756c;
            letter-spacing: .3px;
        }

        .linha {
            margin-bottom: 3px;
        }

        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding: 2px 8px 3px 0;
        }

        .grid-3 {
            width: 100%;
            border-collapse: collapse;
        }

        .grid-3 td {
            width: 33.33%;
            vertical-align: top;
            padding: 2px 8px 3px 0;
        }

        strong {
            font-weight: bold;
        }

        .texto {
            white-space: normal;
        }

        .assinaturas {
            margin-top: 34px;
            width: 100%;
            border-collapse: collapse;
        }

        .assinaturas td {
            width: 50%;
            text-align: center;
            padding-top: 28px;
        }

        .linha-assinatura {
            border-top: 1px solid #111827;
            padding-top: 5px;
            display: inline-block;
            min-width: 220px;
        }

        .rodape {
            margin-top: 18px;
            color: #6b7280;
            font-size: 9px;
            text-align: right;
        }
    </style>
</head>
<body>

<header class="topo">
    <h1 class="titulo">RELATÓRIO DE PLANTÃO</h1>
    <p class="subtitulo">Documento assistencial para acompanhamento clínico e passagem de plantão</p>
</header>

<section class="bloco">
    <h2>Identificação</h2>

    <div class="linha"><strong>Data:</strong> <?= pdf_date($relatorio['data_inicio'] ?? null) ?></div>
    <div class="linha"><strong>Horário do Plantão:</strong> <?= pdf_time($relatorio['data_inicio'] ?? null) ?> às <?= pdf_time($relatorio['data_fim'] ?? null) ?></div>
    <div class="linha"><strong>Nome do Paciente:</strong> <?= pdf_e($nomePaciente) ?></div>
    <div class="linha"><strong>DN / Idade:</strong> <?= pdf_date($dataNascimento) ?> - <?= pdf_age($dataNascimento) ?></div>
    <div class="linha"><strong>Internação:</strong> <?= pdf_e($internacao) ?></div>
    <div class="linha"><strong>Quarto / Local:</strong> <?= pdf_e($local) ?></div>
    <div class="linha"><strong>Nome do Acompanhante:</strong> <?= pdf_e($relatorio['nome_acompanhante'] ?? null) ?></div>
    <div class="linha"><strong>Responsável pelo registro:</strong> <?= pdf_e($profissional) ?></div>
</section>

<section class="bloco">
    <h2>Sinais Vitais</h2>
    <table class="grid-3">
        <tr>
            <td><strong>Temperatura:</strong> <?= pdf_e($relatorio['temperatura'] ?? null) ?></td>
            <td><strong>Pressão Arterial:</strong> <?= pdf_e($relatorio['pa'] ?? null) ?></td>
            <td><strong>Frequência Cardíaca:</strong> <?= pdf_e($relatorio['fc'] ?? null) ?></td>
        </tr>
        <tr>
            <td><strong>Saturação:</strong> <?= pdf_e($relatorio['spo2'] ?? null) ?></td>
            <td><strong>Frequência Respiratória:</strong> <?= pdf_e($relatorio['frequencia_respiratoria'] ?? null) ?></td>
            <td><strong>HGT:</strong> <?= pdf_e($relatorio['hgt'] ?? null) ?></td>
        </tr>
    </table>
</section>

<section class="bloco">
    <h2>Alimentação / Hidratação</h2>
    <div class="texto"><?= pdf_decode($relatorio['alimentacao'] ?? null) ?></div>
    <?php if (!empty($relatorio['hidratacao_ml'])): ?>
        <div class="linha"><strong>Total hidratação:</strong> <?= pdf_e($relatorio['hidratacao_ml']) ?> ml</div>
    <?php endif; ?>
    <?php if (!empty($hidratacaoRegistros)): ?>
        <div class="texto"><strong>Registros:</strong><br><?= pdf_decode($hidratacaoRegistros) ?></div>
    <?php endif; ?>
</section>

<section class="bloco">
    <h2>Higiene</h2>
    <div class="texto"><?= pdf_decode($relatorio['higiene'] ?? null) ?></div>
    <table class="grid-2">
        <tr>
            <td><strong>Urina:</strong> <?= pdf_decode($urinaHorarios) ?></td>
            <td><strong>Fezes:</strong> <?= pdf_decode($fezesHorarios) ?></td>
        </tr>
    </table>
</section>

<section class="bloco">
    <h2>Sono e Descanso</h2>
    <div class="texto"><?= pdf_decode($relatorio['sono'] ?? null) ?></div>
</section>

<section class="bloco sem-quebra">
    <h2>Medicações administradas pela enfermagem</h2>
    <div class="texto"><?= pdf_decode($relatorio['medicacoes'] ?? null) ?></div>
</section>

<section class="bloco sem-quebra">
    <h2>Informações Adicionais</h2>
    <table class="grid-2">
        <tr>
            <td><strong>Estado Geral:</strong> <?= pdf_e($relatorio['estado_geral'] ?? $relatorio['estado_paciente'] ?? null) ?></td>
            <td><strong>Consciência:</strong> <?= pdf_e($relatorio['consciencia'] ?? null) ?></td>
        </tr>
        <tr>
            <td><strong>Pele / Mucosas:</strong> <?= pdf_e($relatorio['pele_mucosas'] ?? null) ?></td>
            <td><strong>Eliminações:</strong> <?= pdf_decode($relatorio['eliminacoes'] ?? null) ?></td>
        </tr>
    </table>

    <div class="linha"><strong>Queixas Referidas:</strong><br><?= pdf_decode($relatorio['queixas_referidas'] ?? null) ?></div>
    <div class="linha"><strong>Exame Físico:</strong><br><?= pdf_decode($relatorio['exame_fisico'] ?? null) ?></div>
    <div class="linha"><strong>Dispositivos:</strong><br><?= pdf_decode($dispositivos) ?></div>
</section>

<section class="bloco">
    <h2>Visita Médica e Condutas</h2>
    <div class="texto"><?= pdf_decode($relatorio['visita_medica'] ?? null) ?></div>
</section>

<section class="bloco">
    <h2>Entrada / Saída de Profissionais e Familiares</h2>
    <div class="linha"><strong>Enfermeiros / Técnicos:</strong><br><?= pdf_decode($relatorio['entrada_saida_profissionais'] ?? null) ?></div>
    <div class="linha"><strong>Familiares / Visitas:</strong><br><?= pdf_decode($relatorio['entrada_saida_familiares'] ?? null) ?></div>
</section>

<section class="bloco">
    <h2>Intercorrências</h2>
    <div class="texto"><?= pdf_decode($relatorio['intercorrencias'] ?? null) ?></div>
</section>

<section class="bloco">
    <h2>Passagem de Plantão</h2>
    <div class="linha"><strong>Plantão entregue para:</strong> <?= pdf_e($relatorio['plantao_entregue_para'] ?? null) ?></div>
</section>

<section class="bloco">
    <h2>Observações Finais</h2>
    <?php if (!empty($relatorio['peso'])): ?>
        <div class="linha"><strong>Peso:</strong> <?= pdf_e($relatorio['peso']) ?> kg</div>
    <?php endif; ?>
    <div class="texto"><?= pdf_decode($relatorio['observacoes_gerais'] ?? null) ?></div>
</section>

<table class="assinaturas">
    <tr>
        <td><span class="linha-assinatura">Responsável pelo registro</span></td>
        <td><span class="linha-assinatura">Responsável pela passagem</span></td>
    </tr>
</table>

<div class="rodape">
    Documento gerado em <?= date('d/m/Y H:i') ?>.
</div>

</body>
</html>
