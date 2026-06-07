-- Segurança v31 - Backups e manutenção
-- Não altera estrutura crítica. Apenas garante permissões se a tabela de permissões existir.

INSERT INTO tb_permissoes (chave, nome, grupo, descricao)
SELECT 'configuracoes.ver', 'Ver configurações', 'Configurações', 'Permite acessar telas de configurações.'
WHERE NOT EXISTS (SELECT 1 FROM tb_permissoes WHERE chave = 'configuracoes.ver');

INSERT INTO tb_permissoes (chave, nome, grupo, descricao)
SELECT 'configuracoes.editar', 'Editar configurações', 'Configurações', 'Permite alterar configurações e gerar backups.'
WHERE NOT EXISTS (SELECT 1 FROM tb_permissoes WHERE chave = 'configuracoes.editar');

-- Libera para Administrador, quando existir tipo id = 1.
INSERT IGNORE INTO tb_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT 1, p.id
FROM tb_permissoes p
WHERE p.chave IN ('configuracoes.ver', 'configuracoes.editar');
