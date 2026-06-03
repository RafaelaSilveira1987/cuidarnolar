<?php $usuarios = $usuarios ?? []; ?>
<section class="cfg-page">
    <div class="cfg-header">
        <div>
            <span class="cfg-kicker">Configurações</span>
            <h1>Permissões de usuários</h1>
            <p>Área reservada para controlar acesso por perfil. Vamos deixar preparada, sem mexer no login agora.</p>
        </div>
    </div>

    <?php include __DIR__ . '/_subnav.php'; ?>

    <div class="cfg-card">
        <div class="cfg-permission-note">
            <strong>Próximo passo deste bloco</strong>
            <p>Aqui entra a matriz de permissões por módulo: pacientes, escala, financeiro, relatórios e configurações. Por enquanto, esta tela só mostra os usuários cadastrados para validar a base.</p>
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
                            <td><span class="cfg-badge <?= (($usuario['status'] ?? '') === 'Ativo') ? 'ok' : 'muted' ?>"><?= e($usuario['status'] ?? '—') ?></span></td>
                            <td><?= e((string)($usuario['tipo_usuario_id'] ?? '—')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
