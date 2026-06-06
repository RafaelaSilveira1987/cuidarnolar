-- Segurança v24 - UUID público nas URLs sensíveis
-- Rode uma vez no HeidiSQL antes/depois de copiar os arquivos.

-- Tabela de plantões ainda podia trabalhar só com ID. Adicionamos UUID público.
SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_tabela_plantoes'
      AND COLUMN_NAME = 'uuid'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE tb_tabela_plantoes ADD COLUMN uuid CHAR(36) NULL AFTER id',
    'SELECT 'Coluna uuid já existe em tb_tabela_plantoes''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tb_tabela_plantoes
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';

ALTER TABLE tb_tabela_plantoes
    MODIFY uuid CHAR(36) NOT NULL DEFAULT (uuid());

SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_tabela_plantoes'
      AND INDEX_NAME = 'uq_tabela_plantoes_uuid'
);

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE tb_tabela_plantoes ADD UNIQUE KEY uq_tabela_plantoes_uuid (uuid)',
    'SELECT 'Índice uq_tabela_plantoes_uuid já existe''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garantia para tabelas que já tinham UUID, mas podem ter vindo de base antiga.
UPDATE tb_financeiro
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';

UPDATE tb_contratos_paciente
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';

UPDATE tb_medicacoes_paciente
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';

UPDATE tb_planos_cuidado
SET uuid = UUID()
WHERE uuid IS NULL OR TRIM(uuid) = '';
