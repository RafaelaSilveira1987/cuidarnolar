-- Segurança v26 — gestão de usuários e política de senha
-- Execute no HeidiSQL antes/depois de copiar os arquivos do patch.

INSERT INTO tb_acl_permissoes (chave, modulo, nome, descricao, ordem, ativo)
VALUES
('usuarios.gerenciar', 'Segurança', 'Gerenciar usuários', 'Criar, editar, ativar/inativar e redefinir senha de usuários.', 20, 1)
ON DUPLICATE KEY UPDATE
    modulo = VALUES(modulo),
    nome = VALUES(nome),
    descricao = VALUES(descricao),
    ordem = VALUES(ordem),
    ativo = 1;

-- Administrador recebe a nova permissão automaticamente.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave = 'usuarios.gerenciar'
WHERE LOWER(t.nome_tipo) = 'administrador';

-- Garante rastreabilidade mínima de senha para usuários antigos.
UPDATE tb_usuarios
SET last_password_change = COALESCE(last_password_change, ultimo_login, NOW())
WHERE last_password_change IS NULL;

-- Garante UUID para usuários antigos, caso algum registro tenha vindo de importação antiga.
UPDATE tb_usuarios
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';
