<?php
$responsaveisPaciente = isset($responsaveis) && is_array($responsaveis) ? $responsaveis : [];
$pacienteUuid = (string)($paciente['uuid'] ?? $record['uuid'] ?? '');

function resp_paciente_texto(mixed $valor, string $fallback = 'Não informado'): string
{
    $valor = trim((string)$valor);

    return $valor !== '' ? e($valor) : $fallback;
}

function resp_paciente_data(?string $data): string
{
    if (!$data) {
        return 'Não informado';
    }

    $ts = strtotime($data);

    return $ts ? date('d/m/Y', $ts) : 'Não informado';
}

function resp_paciente_idade(?string $dataNascimento): string
{
    if (!$dataNascimento) {
        return 'Não informado';
    }

    try {
        $nascimento = new DateTime($dataNascimento);
        $hoje = new DateTime();

        return $nascimento->diff($hoje)->y . ' anos';
    } catch (Throwable) {
        return 'Não informado';
    }
}
?>

<section class="panel responsaveis-paciente-panel">
    <div class="panel-header responsaveis-paciente-header">
        <div>
            <h2>Responsáveis e contatos</h2>
            <p class="page-subtitle">
                O paciente mantém apenas o vínculo. Os dados pessoais ficam no cadastro oficial de responsáveis.
            </p>
        </div>

        <div class="button-row">
            <?php if ($pacienteUuid !== ''): ?>
            <a class="btn btn-secondary" href="<?= url('/pacientes/' . rawurlencode($pacienteUuid) . '/editar') ?>">
                Alterar vínculo
            </a>
            <?php endif; ?>

            <a class="btn btn-primary" href="<?= url('/responsaveis/novo') ?>">
                Novo responsável
            </a>
        </div>
    </div>

    <?php if ($responsaveisPaciente === []): ?>
    <p class="empty-state">
        Nenhum responsável vinculado. Clique em <strong>Alterar vínculo</strong> e selecione um responsável já
        cadastrado.
    </p>
    <?php else: ?>
    <div class="responsaveis-paciente-list">
        <?php foreach ($responsaveisPaciente as $resp): ?>
        <article class="responsavel-paciente-item">
            <div class="responsavel-paciente-topo">
                <div class="responsavel-paciente-main">
                    <strong><?= resp_paciente_texto($resp['nome_completo'] ?? null, 'Responsável') ?></strong>
                    <span><?= resp_paciente_texto($resp['grau_parentesco'] ?? null, 'Parentesco não informado') ?></span>
                </div>

                <span class="badge">
                    <?= resp_paciente_texto($resp['status'] ?? 'Ativo', 'Ativo') ?>
                </span>
            </div>

            <dl class="responsavel-paciente-grid">
                <div>
                    <dt>CPF</dt>
                    <dd><?= resp_paciente_texto($resp['cpf'] ?? null) ?></dd>
                </div>

                <div>
                    <dt>Nascimento</dt>
                    <dd><?= e(resp_paciente_data($resp['data_nascimento'] ?? null)) ?></dd>
                </div>

                <div>
                    <dt>Idade</dt>
                    <dd><?= e(resp_paciente_idade($resp['data_nascimento'] ?? null)) ?></dd>
                </div>

                <div>
                    <dt>Telefone</dt>
                    <dd><?= resp_paciente_texto($resp['telefone'] ?? null) ?></dd>
                </div>

                <div>
                    <dt>E-mail</dt>
                    <dd><?= resp_paciente_texto($resp['email'] ?? null) ?></dd>
                </div>

                <!-- <div>
                            <dt>Origem</dt>
                            <dd><?= resp_paciente_texto($resp['origem'] ?? 'tb_responsavel') ?></dd>
                        </div> -->

                <div class="span-2">
                    <dt>Endereço</dt>
                    <dd><?= resp_paciente_texto($resp['endereco_completo'] ?? null) ?></dd>
                </div>
            </dl>

            <div class="responsavel-paciente-actions">
                <?php if (!empty($resp['uuid'])): ?>
                <a class="btn btn-secondary"
                    href="<?= url('/responsaveis/' . rawurlencode((string)$resp['uuid']) . '/editar') ?>">
                    Editar responsável
                </a>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>