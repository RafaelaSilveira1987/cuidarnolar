-- Execute apenas se sua tabela tb_cuidador ainda NÃO tiver o campo cor_escala.
ALTER TABLE tb_cuidador
ADD COLUMN IF NOT EXISTS cor_escala VARCHAR(20) NULL DEFAULT NULL AFTER contrato_horas;
