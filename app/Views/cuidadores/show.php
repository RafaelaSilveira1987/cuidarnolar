<?php

/**
 * app/Views/cuidadores/show.php
 * Ficha profissional do cuidador — visual limpo e próprio do módulo.
 */

$record = isset($record) && is_array($record) ? $record : [];
$routeBase = (string)($routeBase ?? '/cuidadores');
$resourceKey = (string)($resourceKey ?? ($record['uuid'] ?? $record['id'] ?? ''));

function cuidador_valor(mixed $value, string $fallback = '—'): string
{
    $value = trim((string)$value);
    return $value !== '' ? $value : $fallback;
}

function cuidador_data(?string $date, string $fallback = '—'): string
{
    if (!$date) {
        return $fallback;
    }

    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : $fallback;
}

function cuidador_idade(?string $dataNascimento): string
{
    if (!$dataNascimento) {
        return '—';
    }

    try {
        $nascimento = new DateTime($dataNascimento);
        $hoje = new DateTime();
        return $nascimento->diff($hoje)->y . ' anos';
    } catch (Throwable $e) {
        return '—';
    }
}

$nome = cuidador_valor($record['nome_completo'] ?? null, 'Cuidador');
$inicial = strtoupper(mb_substr($nome, 0, 1, 'UTF-8'));
$status = cuidador_valor($record['status'] ?? null, 'Não informado');
$statusClass = match (mb_strtolower($status, 'UTF-8')) {
    'ativo' => 'success',
    'standby' => 'warning',
    'inativo' => 'danger',
    default => 'secondary',
};

$especialidade = cuidador_valor($record['especialidade'] ?? null);
$contrato = cuidador_valor($record['contrato_horas'] ?? null);
$telefone = cuidador_valor($record['telefone'] ?? null);
$email = cuidador_valor($record['email'] ?? null);
$pix = cuidador_valor($record['pix'] ?? null);
$cpf = cuidador_valor($record['cpf'] ?? null);
$rg = cuidador_valor($record['rg'] ?? null);
$nascimento = cuidador_data($record['data_nascimento'] ?? null);
$idade = cuidador_idade($record['data_nascimento'] ?? null);
$enderecoCompleto = cuidador_valor($record['endereco_completo'] ?? null);
$corEscala = cuidador_valor($record['cor_escala'] ?? null, '#0f766e');
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $corEscala)) {
    $corEscala = '#0f766e';
}
$canInativar = $status !== 'Inativo' && $resourceKey !== '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cuidadores_ficha_patch.css">

<section class="cuidador-hero">
    <div class="cuidador-identity">
        <div class="cuidador-avatar" aria-hidden="true" style="background: <?= e($corEscala) ?>; color: #fff;"><?= e($inicial) ?></div>

        <div>
            <span class="cuidador-eyebrow">Ficha do cuidador</span>
            <h1><?= e($nome) ?></h1>
            <p>
                <?= e($especialidade) ?>
                <?php if ($contrato !== '—'): ?>
                <span>•</span> Contrato: <?= e($contrato) ?>
                <?php endif; ?>
                <span>•</span>
                <strong class="cuidador-status cuidador-status-<?= e($statusClass) ?>">
                    <?= e($status) ?>
                </strong>
            </p>
        </div>
    </div>

    <div class="cuidador-actions">
        <a class="btn btn-secondary" href="<?= url($routeBase) ?>">Voltar</a>

        <?php if ($resourceKey !== ''): ?>
        <a class="btn btn-primary" href="<?= url($routeBase . '/' . rawurlencode($resourceKey) . '/editar') ?>">
            Editar cadastro
        </a>
        <?php endif; ?>
    </div>
</section>

<section class="cuidador-quick-grid">
    <div class="cuidador-info-pill">
        <span>Especialidade</span>
        <strong><?= e($especialidade) ?></strong>
    </div>

    <div class="cuidador-info-pill">
        <span>Contrato</span>
        <strong><?= e($contrato) ?></strong>
    </div>

    <div class="cuidador-info-pill">
        <span>Telefone</span>
        <strong><?= e($telefone) ?></strong>
    </div>

    <div class="cuidador-info-pill">
        <span>Pix</span>
        <strong><?= e($pix) ?></strong>
    </div>

    <div class="cuidador-info-pill">
        <span>Cor na escala</span>
        <strong style="display:flex;align-items:center;gap:8px;">
            <i style="width:16px;height:16px;border-radius:999px;background:<?= e($corEscala) ?>;display:inline-block;border:1px solid rgba(0,0,0,.12);"></i>
            <?= e($corEscala) ?>
        </strong>
    </div>
</section>

<section class="cuidador-layout">
    <article class="panel cuidador-panel">
        <div class="cuidador-panel-header">
            <div>
                <h2>Dados profissionais</h2>
                <p class="page-subtitle">Informações principais usadas na escala, plantões e controle interno.</p>
            </div>
        </div>

        <dl class="cuidador-detail-grid">
            <div>
                <dt>Nome completo</dt>
                <dd><?= e($nome) ?></dd>
            </div>

            <div>
                <dt>Especialidade</dt>
                <dd><?= e($especialidade) ?></dd>
            </div>

            <div>
                <dt>Contrato de horas</dt>
                <dd><?= e($contrato) ?></dd>
            </div>

            <div>
                <dt>Status</dt>
                <dd><?= e($status) ?></dd>
            </div>

            <div>
                <dt>Cor na escala</dt>
                <dd>
                    <span style="display:inline-flex;align-items:center;gap:8px;">
                        <i style="width:16px;height:16px;border-radius:999px;background:<?= e($corEscala) ?>;display:inline-block;border:1px solid rgba(0,0,0,.12);"></i>
                        <?= e($corEscala) ?>
                    </span>
                </dd>
            </div>
        </dl>
    </article>

    <article class="panel cuidador-panel">
        <div class="cuidador-panel-header">
            <div>
                <h2>Contato e identificação</h2>
                <p class="page-subtitle">Dados de comunicação e documentos do cuidador.</p>
            </div>
        </div>

        <dl class="cuidador-detail-grid">
            <div>
                <dt>Telefone</dt>
                <dd><?= e($telefone) ?></dd>
            </div>

            <div>
                <dt>E-mail</dt>
                <dd><?= e($email) ?></dd>
            </div>

            <div>
                <dt>CPF</dt>
                <dd><?= e($cpf) ?></dd>
            </div>

            <div>
                <dt>RG</dt>
                <dd><?= e($rg) ?></dd>
            </div>

            <div>
                <dt>Data de nascimento</dt>
                <dd><?= e($nascimento) ?></dd>
            </div>

            <div>
                <dt>Idade</dt>
                <dd><?= e($idade) ?></dd>
            </div>
        </dl>
    </article>

    <article class="panel cuidador-panel cuidador-panel-full">
        <div class="cuidador-panel-header">
            <div>
                <h2>Endereço e dados financeiros</h2>
                <p class="page-subtitle">Informações úteis para cadastro, contrato e pagamentos.</p>
            </div>
        </div>

        <dl class="cuidador-detail-grid cuidador-detail-grid-wide">
            <div>
                <dt>Endereço</dt>
                <dd><?= e($enderecoCompleto) ?></dd>
            </div>

            <div>
                <dt>CEP</dt>
                <dd><?= e(cuidador_valor($record['cep'] ?? null)) ?></dd>
            </div>

            <div>
                <dt>Chave Pix</dt>
                <dd><?= e($pix) ?></dd>
            </div>

            <div>
                <dt>Motivo de inativação</dt>
                <dd><?= e(cuidador_valor($record['motivo_inativacao'] ?? null)) ?></dd>
            </div>
        </dl>
    </article>

    <article class="panel cuidador-panel cuidador-panel-full">
        <div class="cuidador-panel-header">
            <div>
                <h2>Histórico de plantões e escalas</h2>
                <p class="page-subtitle">Espaço reservado para integração com escalas, horas trabalhadas e relatórios de
                    plantão.</p>
            </div>
        </div>

        <p class="empty-state">
            Em breve esta área pode listar os últimos plantões, pacientes atendidos, horas realizadas e pendências
            financeiras.
        </p>
    </article>
</section>

<?php if ($canInativar): ?>
<section class="panel danger-panel cuidador-danger-panel">
    <h2>Inativar registro</h2>
    <p class="page-subtitle">Mantém o histórico e remove o cuidador dos fluxos ativos.</p>

    <form class="inline-form" method="POST"
        action="<?= url($routeBase . '/' . rawurlencode($resourceKey) . '/inativar') ?>"
        onsubmit="return confirm('Confirma inativar este cuidador?')">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
        <input type="text" name="motivo_inativacao" placeholder="Motivo da inativação">
        <button class="btn btn-danger" type="submit">Inativar</button>
    </form>
</section>
<?php endif; ?>