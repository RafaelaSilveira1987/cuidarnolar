-- Segurança v28.2 - Correções do escopo do cuidador e usuários
-- Execute no banco atual antes/depois de copiar os arquivos.

-- Garante coluna para vincular usuário ao cadastro de cuidador.
SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_usuarios'
      AND COLUMN_NAME = 'cuidador_id'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE tb_usuarios ADD COLUMN cuidador_id INT NULL AFTER tipo_usuario_id',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garante índice para filtros de escopo.
SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_usuarios'
      AND INDEX_NAME = 'idx_tb_usuarios_cuidador_id'
);

SET @sql := IF(
    @idx_exists = 0,
    'CREATE INDEX idx_tb_usuarios_cuidador_id ON tb_usuarios (cuidador_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Conferência opcional após aplicar:
-- SELECT u.id, u.username, u.status, u.tipo_usuario_id, t.nome_tipo, u.cuidador_id, c.nome_completo AS cuidador, c.status AS status_cuidador
-- FROM tb_usuarios u
-- LEFT JOIN tb_tipos_usuarios t ON t.id = u.tipo_usuario_id
-- LEFT JOIN tb_cuidador c ON c.id = u.cuidador_id
-- ORDER BY u.id;
