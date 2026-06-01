-- Ajustes opcionais para a tela de escala.
-- Execute apenas se a coluna ainda não existir na sua base.

ALTER TABLE tb_cuidador
    ADD COLUMN cor_escala VARCHAR(7) NULL DEFAULT '#01948e' AFTER contrato_horas;

-- Caso sua tabela de ocorrências ainda não tenha esses campos, valide antes de executar.
-- Eles são usados pela tela operacional de escala.
-- ALTER TABLE tb_escala_ocorrencias ADD COLUMN tipo_plantao VARCHAR(30) NULL AFTER fim;
-- ALTER TABLE tb_escala_ocorrencias ADD COLUMN origem VARCHAR(30) NULL DEFAULT 'manual' AFTER status;
