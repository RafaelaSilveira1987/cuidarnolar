<?php
$usuarios = $usuarios ?? [];
$tiposUsuario = $tiposUsuario ?? [];
$permissoes = $permissoes ?? [];
$permissoesPorTipo = $permissoesPorTipo ?? [];

$permissoesPorModulo = [];
foreach ($permissoes as $permissao) {
    $modulo = (string)($permissao['modulo'] ?? 'Sistema');
    $permissoesPorModulo[$modulo][] = $permissao;
}
?>

<section class="cfg-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Configurações</span>
            <h1>Permissões de usuários</h1>
            <p>Controle o que cada papel pode ver e executar. Admin continua com acesso total.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <form method="POST" action="<?= url('/configuracoes/permissoes') ?>" class="cfg-card cfg-permission-matrix">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <div class="cfg-permission-note">
            <strong>Regra importante</strong>
            <p>Permissões são carregadas no login. Depois de salvar, peça para os usuários entrarem novamente no sistema.</p>
        </div>

        <?php if ($permissoes === [] || $tiposUsuario === []): ?>
            <p class="cfg-empty">Execute o SQL de segurança v23 para carregar a matriz de permissões.</p>
        <?php else: ?>
            <div class="cfg-table-wrap">
                <table class="cfg-table cfg-permissions-table">
                    <thead>
                        <tr>
                            <th>Permissão</th>
                            <?php foreach ($tiposUsuario as $tipo): ?>
                                <th><?= e($tipo['nome_tipo'] ?? '') ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissoesPorModulo as $modulo => $lista): ?>
                            <tr class="cfg-permission-module-row">
                                <td colspan="<?= count($tiposUsuario) + 1 ?>"><?= e($modulo) ?></td>
                            </tr>

                            <?php foreach ($lista as $permissao): ?>
                                <?php $permissaoId = (int)($permissao['id'] ?? 0); ?>
                                <tr>
                                    <td>
                                        <strong><?= e($permissao['nome'] ?? $permissao['chave'] ?? '') ?></strong>
                                        <small><?= e($permissao['chave'] ?? '') ?></small>
                                    </td>
                                    <?php foreach ($tiposUsuario as $tipo): ?>
                                        <?php
                                            $tipoId = (int)($tipo['id'] ?? 0);
                                            $nomeTipo = strtolower((string)($tipo['nome_tipo'] ?? ''));
                                            $checked = in_array($permissaoId, $permissoesPorTipo[$tipoId] ?? [], true);
                                            $isAdmin = $nomeTipo === 'administrador';
                                        ?>
                                        <td class="cfg-permission-check-cell">
                                            <label class="cfg-permission-check">
                                                <input type="checkbox"
                                                    name="permissoes[<?= $tipoId ?>][]"
                                                    value="<?= $permissaoId ?>"
                                                    <?= ($checked || $isAdmin) ? 'checked' : '' ?>
                                                    <?= $isAdmin ? 'disabled' : '' ?>>
                                                <span><?= $isAdmin ? 'Total' : 'Permitir' ?></span>
                                            </label>
                                            <?php if ($isAdmin): ?>
                                                <input type="hidden" name="permissoes[<?= $tipoId ?>][]" value="<?= $permissaoId ?>">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="button-row">
                <button type="submit" class="btn btn-primary">Salvar permissões</button>
            </div>
        <?php endif; ?>
    </form>

    <div class="cfg-card">
        <div class="cfg-section-title compact">
            <h2>Usuários cadastrados</h2>
            <p>Lista de referência para conferir tipo/papel atual.</p>
        </div>

        <div class="cfg-table-wrap">
            <table class="cfg-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Login</th>
                        <th>Status</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($usuarios === []): ?>
                        <tr><td colspan="4" class="cfg-empty">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><strong><?= e($usuario['nome_completo'] ?? '') ?></strong></td>
                            <td><?= e($usuario['username'] ?? $usuario['email'] ?? '') ?></td>
                            <td><span class="cfg-badge <?= strtolower((string)($usuario['status'] ?? '')) === 'ativo' ? 'ok' : 'muted' ?>"><?= e($usuario['status'] ?? '—') ?></span></td>
                            <td><?= e((string)($usuario['tipo_usuario_id'] ?? '—')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
