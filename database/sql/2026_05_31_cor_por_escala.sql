-- Cuidar no Lar — cor do cuidador por escala/paciente
-- Execute uma vez no banco cuidar_no_lar antes de testar a tela.

SET @db_name := DATABASE();

SET @sql_add_cor := (
    SELECT IF(
        COUNT(*) = 0,
        'ALTER TABLE tb_escala_profissionais ADD COLUMN cor_escala VARCHAR(7) NULL AFTER principal_escala',
        'SELECT "Coluna tb_escala_profissionais.cor_escala já existe" AS info'
    )
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @db_name
      AND TABLE_NAME = 'tb_escala_profissionais'
      AND COLUMN_NAME = 'cor_escala'
);

PREPARE stmt_add_cor FROM @sql_add_cor;
EXECUTE stmt_add_cor;
DEALLOCATE PREPARE stmt_add_cor;

-- Migra as cores antigas do cadastro do cuidador para o vínculo da escala.
-- Depois do patch, a cor oficial da grade passa a ser esta coluna.
UPDATE tb_escala_profissionais ep
INNER JOIN tb_cuidador c ON c.id = ep.cuidador_id
SET ep.cor_escala = c.cor_escala
WHERE (ep.cor_escala IS NULL OR ep.cor_escala = '')
  AND c.cor_escala REGEXP '^#[0-9A-Fa-f]{6}$';

-- Opcional: só rode depois de confirmar que tudo funcionou.
-- A remoção física da coluna antiga não é obrigatória, porque o patch já remove a cor do cadastro.
-- ALTER TABLE tb_cuidador DROP COLUMN cor_escala;
