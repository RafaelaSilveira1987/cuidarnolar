-- =============================================================
-- Cuidar no Lar — Escala/Financeiro v11
-- Fechar escala / Finalizar plantões antes de gerar contas a pagar
-- =============================================================

-- 1) Permite status "fechada" na aprovação da escala.
SET @status_type := (
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_escala_aprovacoes'
      AND COLUMN_NAME = 'status'
    LIMIT 1
);

SET @sql := IF(
    @status_type IS NOT NULL AND @status_type NOT LIKE '%''fechada''%',
    'ALTER TABLE `tb_escala_aprovacoes` MODIFY `status` ENUM(''em_edicao'',''aprovada'',''reaberta'',''fechada'',''cancelada'') NOT NULL DEFAULT ''em_edicao''',
    'SELECT ''Status fechada já existe ou tabela/coluna não encontrada'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Registra quem e quando fechou a escala.
SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_escala_aprovacoes'
      AND COLUMN_NAME = 'fechado_por'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `tb_escala_aprovacoes` ADD COLUMN `fechado_por` INT NULL AFTER `reaberto_em`',
    'SELECT ''Coluna fechado_por já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_escala_aprovacoes'
      AND COLUMN_NAME = 'fechado_em'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `tb_escala_aprovacoes` ADD COLUMN `fechado_em` DATETIME NULL AFTER `fechado_por`',
    'SELECT ''Coluna fechado_em já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Garante que o status finalizado existe nas ocorrências.
SET @oc_status_type := (
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_escala_ocorrencias'
      AND COLUMN_NAME = 'status'
    LIMIT 1
);

SET @sql := IF(
    @oc_status_type IS NOT NULL AND @oc_status_type NOT LIKE '%''finalizado''%',
    'ALTER TABLE `tb_escala_ocorrencias` MODIFY `status` ENUM(''previsto'',''confirmado'',''em_andamento'',''finalizado'',''faltou'',''cancelado'',''substituido'') DEFAULT ''previsto''',
    'SELECT ''Status finalizado já existe ou tabela/coluna não encontrada'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
