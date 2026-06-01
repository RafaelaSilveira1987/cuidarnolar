-- Execute somente se a coluna de cor ainda não existir no seu banco.
-- Ela permite escolher uma cor fixa para cada cuidador e refletir isso na grade da escala.

ALTER TABLE tb_cuidador
  ADD COLUMN cor_escala VARCHAR(7) NULL DEFAULT NULL AFTER contrato_horas;

-- Sugestão opcional para quem já tem cuidadores cadastrados sem cor definida.
-- Altere as cores livremente pelo cadastro do cuidador depois.
UPDATE tb_cuidador
SET cor_escala = CASE MOD(id, 10)
    WHEN 0 THEN '#0f766e'
    WHEN 1 THEN '#2563eb'
    WHEN 2 THEN '#7c3aed'
    WHEN 3 THEN '#dc2626'
    WHEN 4 THEN '#ea580c'
    WHEN 5 THEN '#0891b2'
    WHEN 6 THEN '#be123c'
    WHEN 7 THEN '#16a34a'
    WHEN 8 THEN '#9333ea'
    ELSE '#0d9488'
END
WHERE cor_escala IS NULL OR cor_escala = '';
