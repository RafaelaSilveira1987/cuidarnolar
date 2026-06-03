-- =============================================================
-- Cuidar no Lar — Financeiro v10
-- Geração de contas a pagar dos cuidadores a partir da escala
-- =============================================================

-- Vínculo entre lançamento financeiro e plantão da escala.
SET @col_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_financeiro'
      AND COLUMN_NAME = 'escala_ocorrencia_id'
);

SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `tb_financeiro` ADD COLUMN `escala_ocorrencia_id` INT NULL AFTER `contrato_paciente_id`',
    'SELECT ''Coluna escala_ocorrencia_id já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'tb_financeiro'
      AND INDEX_NAME = 'idx_financeiro_escala_ocorrencia'
);

SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE `tb_financeiro` ADD INDEX `idx_financeiro_escala_ocorrencia` (`escala_ocorrencia_id`)',
    'SELECT ''Índice idx_financeiro_escala_ocorrencia já existe'' AS info'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Garante categoria de saída para pagamentos dos cuidadores.
INSERT INTO `tb_categorias_financeiro` (`nome`, `tipo`, `ativo`)
SELECT 'Salário cuidador', 'saida', 1
WHERE NOT EXISTS (
    SELECT 1 FROM `tb_categorias_financeiro`
    WHERE `nome` = 'Salário cuidador' AND `tipo` = 'saida'
);
