<?php
$usuario = $usuario ?? [];
$tiposUsuario = $tiposUsuario ?? [];
$cuidadores = $cuidadores ?? [];
$errors = $errors ?? [];
$modo = $modo ?? 'novo';
$isEdit = $modo === 'editar';
$uuid = (string)($usuario['uuid'] ?? '');
$action = $isEdit
    ? url('/configuracoes/usuarios/' . rawurlencode($uuid))
    : url('/configuracoes/usuarios');

$err = static fn(string $key): string => !empty($errors[$key]) ? '<small class="field-error">' . e($errors[$key]) . '</small>' : '';
$cuidadorAtual = (int)($usuario['cuidador_id'] ?? 0);
?>

<section class="cfg-page cfg-user-form-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Segurança</span>
            <h1><?= $isEdit ? 'Editar usuário' : 'Novo usuário' ?></h1>
            <p>Use usuários individuais. Nada de senha compartilhada — sistema sério deixa rastro.</p>
        </div>
        <a class="btn btn-secondary" href="<?= url('/configuracoes/usuarios') ?>">Voltar</a>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <form method="POST" action="<?= $action ?>" class="cfg-card cfg-user-form">
        <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

        <?php if (!empty($errors['geral'])): ?>
            <div class="alert alert-error"><?= e($errors['geral']) ?></div>
        <?php endif; ?>

        <div class="cfg-grid two">
            <label>
                Nome completo
                <input type="text" name="nome_completo" value="<?= e($usuario['nome_completo'] ?? '') ?>" required>
                <?= $err('nome_completo') ?>
            </label>

            <label>
                Usuário de login
                <input type="text" name="username" value="<?= e($usuario['username'] ?? '') ?>" required autocomplete="off">
                <?= $err('username') ?>
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="<?= e($usuario['email'] ?? '') ?>" required>
                <?= $err('email') ?>
            </label>

            <label>
                Telefone
                <input type="text" name="telefone" value="<?= e($usuario['telefone'] ?? '') ?>">
            </label>

            <label>
                Perfil / tipo de usuário
                <select name="tipo_usuario_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($tiposUsuario as $tipo): ?>
                        <?php $tipoId = (int)($tipo['id'] ?? 0); ?>
                        <option value="<?= $tipoId ?>" <?= (int)($usuario['tipo_usuario_id'] ?? 0) === $tipoId ? 'selected' : '' ?>>
                            <?= e($tipo['nome_tipo'] ?? '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= $err('tipo_usuario_id') ?>
            </label>

            <label>
                Cuidador vinculado
                <select name="cuidador_id">
                    <option value="">Sem vínculo</option>
                    <?php foreach ($cuidadores as $cuidador): ?>
                        <?php
                            $cid = (int)($cuidador['id'] ?? 0);
                            $statusCuidador = mb_strtolower((string)($cuidador['status'] ?? 'Ativo'), 'UTF-8');
                            $nomeCuidador = (string)($cuidador['nome_completo'] ?? 'Cuidador #' . $cid);
                        ?>
                        <?php if ($cid > 0): ?>
                            <option value="<?= $cid ?>" <?= $cuidadorAtual === $cid ? 'selected' : '' ?>>
                                <?= e($nomeCuidador . ($statusCuidador !== 'ativo' ? ' — inativo' : '')) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <small class="field-hint">Obrigatório para usuários do perfil Cuidador. O login será bloqueado se o cuidador vinculado estiver inativo.</small>
                <?= $err('cuidador_id') ?>
            </label>

            <label>
                Status
                <select name="status">
                    <option value="ativo" <?= strtolower((string)($usuario['status'] ?? 'ativo')) === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="inativo" <?= strtolower((string)($usuario['status'] ?? '')) === 'inativo' ? 'selected' : '' ?>>Inativo</option>
                </select>
            </label>
        </div>

        <?php if (!$isEdit): ?>
            <hr class="cfg-separator">
            <div class="cfg-section-title compact">
                <h2>Senha inicial</h2>
                <p>Mínimo de 8 caracteres, com letra maiúscula, minúscula e número.</p>
            </div>

            <div class="cfg-grid two">
                <label>
                    Senha
                    <input type="password" name="senha" required autocomplete="new-password">
                    <?= $err('nova_senha') ?>
                </label>
                <label>
                    Confirmar senha
                    <input type="password" name="senha_confirmacao" required autocomplete="new-password">
                    <?= $err('senha_confirmacao') ?>
                </label>
            </div>

            <label class="cfg-checkline">
                <input type="checkbox" name="precisa_alterar_senha" value="1" checked>
                Exigir alteração da senha no primeiro acesso
            </label>
        <?php endif; ?>

        <div class="button-row">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvar usuário' : 'Criar usuário' ?></button>
            <a class="btn btn-secondary" href="<?= url('/configuracoes/usuarios') ?>">Cancelar</a>
        </div>
    </form>

    <?php if ($isEdit): ?>
        <form method="POST" action="<?= url('/configuracoes/usuarios/' . rawurlencode($uuid) . '/resetar-senha') ?>" class="cfg-card cfg-user-form" onsubmit="return confirm('Redefinir a senha deste usuário?');">
            <input type="hidden" name="_csrf" value="<?= e($_csrf ?? '') ?>">

            <div class="cfg-section-title compact">
                <h2>Redefinir senha</h2>
                <p>Use isso quando o usuário perdeu acesso ou precisa receber uma nova senha temporária.</p>
            </div>

            <div class="cfg-grid two">
                <label>
                    Nova senha
                    <input type="password" name="nova_senha" required autocomplete="new-password">
                </label>
                <label>
                    Confirmar senha
                    <input type="password" name="senha_confirmacao" required autocomplete="new-password">
                </label>
            </div>

            <label class="cfg-checkline">
                <input type="checkbox" name="precisa_alterar_senha" value="1" checked>
                Exigir alteração no próximo login
            </label>

            <div class="button-row">
                <button type="submit" class="btn btn-secondary">Redefinir senha</button>
            </div>
        </form>
    <?php endif; ?>
</section>
