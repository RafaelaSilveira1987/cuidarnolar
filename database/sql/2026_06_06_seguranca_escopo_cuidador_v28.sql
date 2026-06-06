-- Segurança v28: escopo do cuidador por usuário
SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_usuarios' AND COLUMN_NAME = 'cuidador_id'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE tb_usuarios ADD COLUMN cuidador_id INT NULL AFTER tipo_usuario_id',
    'SELECT "tb_usuarios.cuidador_id já existe" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_usuarios' AND INDEX_NAME = 'idx_usuarios_cuidador_id'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE tb_usuarios ADD INDEX idx_usuarios_cuidador_id (cuidador_id)',
    'SELECT "idx_usuarios_cuidador_id já existe" AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO tb_acl_permissoes (chave, nome, modulo, descricao, ativo, ordem)
SELECT 'usuarios.gerenciar', 'Gerenciar usuários', 'Segurança', 'Permite gerenciar usuários e vínculo com cuidador.', 1, 10
WHERE EXISTS (SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tb_acl_permissoes')
  AND NOT EXISTS (SELECT 1 FROM tb_acl_permissoes WHERE chave = 'usuarios.gerenciar');

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT 1, id FROM tb_acl_permissoes WHERE chave = 'usuarios.gerenciar';
