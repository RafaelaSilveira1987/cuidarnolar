-- Financeiro v2: geração de contas a receber a partir dos contratos
-- Rode no HeidiSQL antes de substituir/testar os arquivos deste patch.

ALTER TABLE tb_financeiro
MODIFY tipo_transacao ENUM('Entrada','Saída') NOT NULL;

UPDATE tb_financeiro
SET status = 'Pendente'
WHERE status = 'Transporte';

ALTER TABLE tb_financeiro
MODIFY status ENUM('Pendente','Pago','Cancelado') NOT NULL DEFAULT 'Pendente';

ALTER TABLE tb_financeiro
MODIFY observacoes TINYTEXT NULL;

SET @schema_name = DATABASE();

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD COLUMN contrato_paciente_id INT NULL AFTER paciente_id',
        'SELECT "contrato_paciente_id ja existe"'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND COLUMN_NAME = 'contrato_paciente_id'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD COLUMN mes_referencia CHAR(7) NULL AFTER data_vencimento',
        'SELECT "mes_referencia ja existe"'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND COLUMN_NAME = 'mes_referencia'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD COLUMN origem VARCHAR(30) NULL DEFAULT NULL AFTER detalhes',
        'SELECT "origem ja existe"'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND COLUMN_NAME = 'origem'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE tb_financeiro
SET origem = 'manual'
WHERE origem IS NULL;

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD INDEX idx_financeiro_contrato_paciente (contrato_paciente_id)',
        'SELECT "idx_financeiro_contrato_paciente ja existe"'
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND INDEX_NAME = 'idx_financeiro_contrato_paciente'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD INDEX idx_financeiro_mes_referencia (mes_referencia)',
        'SELECT "idx_financeiro_mes_referencia ja existe"'
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND INDEX_NAME = 'idx_financeiro_mes_referencia'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(COUNT(*) = 0,
        'ALTER TABLE tb_financeiro ADD UNIQUE KEY uq_financeiro_contrato_mes (contrato_paciente_id, mes_referencia, tipo_transacao)',
        'SELECT "uq_financeiro_contrato_mes ja existe"'
    )
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name
      AND TABLE_NAME = 'tb_financeiro'
      AND INDEX_NAME = 'uq_financeiro_contrato_mes'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
