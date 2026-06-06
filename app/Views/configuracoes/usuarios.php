<?php
$usuarios = $usuarios ?? [];
$cuidadores = $cuidadores ?? [];
$busca = $busca ?? '';

$cuidadorOptions = [];
foreach ($cuidadores as $cuidador) {
    $cid = (int)($cuidador['id'] ?? 0);
    if ($cid > 0) {
        $cuidadorOptions[$cid] = (string)($cuidador['nome_completo'] ?? 'Cuidador #' . $cid);
    }
}
?>

<section class="cfg-page cfg-users-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Segurança</span>
            <h1>Usuários do sistema</h1>
            <p>Gerencie acessos individuais, status, troca obrigatória de senha e vínculo com cuidador.</p>
        </div>
        <a class="btn btn-primary" href="<?= url('/configuracoes/usuarios/novo') ?>">Novo usuário</a>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <div class="cfg-card">
        <form method="GET" action="<?= url('/configuracoes/usuarios') ?>" class="cfg-user-search">
            <input type="search" name="busca" value="<?= e($busca) ?>" placeholder="Buscar por nome, usuário, e-mail, perfil ou cuidador...">
            <button type="submit" class="btn btn-secondary">Buscar</button>
        </form>

        <div class="cfg-section-title compact">
            <h2>Usuários cadastrados</h2>
            <p>Para usuários do perfil cuidador, vincule o cadastro correspondente. Após alterar o vínculo, o usuário precisa sair e entrar novamente.</p>
        </div>

        <div class="cfg-table-wrap">
            <table class="cfg-table cfg-users-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Login</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Senha</th>
                        <th>Cuidador vinculado</th>
                        <th>Último acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usuarios === []): ?>
                        <tr><td colspan="8" class="cfg-empty">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($usuarios as $usuario): ?>
                        <?php
                            $uuid = (string)($usuario['uuid'] ?? '');
                            $ativo = mb_strtolower((string)($usuario['status'] ?? ''), 'UTF-8') === 'ativo';
                            $trocaSenha = !empty($usuario['precisa_alterar_senha']);
                            $ultimoLogin = (string)($usuario['ultimo_login'] ?? '');
                            $ultimoLoginFmt = ($ultimoLogin !== '' && $ultimoLogin !== '0000-00-00 00:00:00' && $ultimoLogin !== '1970-01-01 00:00:00')
                                ? date('d/m/Y H:i', strtotime($ultimoLogin))
                                : 'Nunca';
                            $cuidadorAtual = (int)($usuario['cuidador_id'] ?? 0);
                            $formVinculoId = 'usuario-vinculo-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $uuid);
                        ?>
                        <tr>
                            <td>
                                <strong><?= e($usuario['nome_completo'] ?? '') ?></strong>
                                <small><?= e($usuario['email'] ?? '') ?></small>
                            </td>
                            <td><?= e($usuario['username'] ?? '') ?></td>
                            <td><?= e($usuario['tipo_usuario'] ?? ('Tipo #' . ($usuario['tipo_usuario_id'] ?? '—'))) ?></td>
                            <td><span class="cfg-badge <?= $ativo ? 'ok' : 'muted' ?>"><?= $ativo ? 'Ativo' : 'Inativo' ?></span></td>
                            <td>
                                <?php if ($trocaSenha): ?>
                                    <span class="cfg-badge warn">Troca obrigatória</span>
                                <?php else: ?>
                                    <span class="cfg-badge ok">Regular</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form id="<?= e($formVinculoId) ?>" method="POST" action="<?= url('/configuracoes/usuarios/' . rawurlencode($uuid) . '/cuidador') ?>">
                                    <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                                    <select name="cuidador_id" aria-label="Cuidador vinculado">
                                        <option value="">Sem vínculo</option>
                                        <?php foreach ($cuidadorOptions as $cid => $nome): ?>
                                            <option value="<?= (int)$cid ?>" <?= $cuidadorAtual === (int)$cid ? 'selected' : '' ?>>
                                                <?= e($nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                                <?php if (!empty($usuario['cuidador_nome'])): ?>
                                    <small>Atual: <?= e($usuario['cuidador_nome']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= e($ultimoLoginFmt) ?></td>
                            <td class="cfg-actions-inline cfg-actions-stack">
                                <button class="link-button" type="submit" form="<?= e($formVinculoId) ?>">Salvar vínculo</button>

                                <a href="<?= url('/configuracoes/usuarios/' . rawurlencode($uuid) . '/editar') ?>">Editar</a>

                                <form method="POST" action="<?= url('/configuracoes/usuarios/' . rawurlencode($uuid) . '/alternar-status') ?>" onsubmit="return confirm('Alterar status deste usuário?');">
                                    <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">
                                    <button type="submit" class="link-button"><?= $ativo ? 'Inativar' : 'Ativar' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
