ALTER TABLE tb_cuidador
ADD COLUMN cor_escala CHAR(7) NULL DEFAULT '#0f766e' AFTER contrato_horas;

UPDATE tb_cuidador
SET cor_escala = CASE MOD(id, 10)
    WHEN 0 THEN '#0f766e'
    WHEN 1 THEN '#2563eb'
    WHEN 2 THEN '#7c3aed'
    WHEN 3 THEN '#db2777'
    WHEN 4 THEN '#ea580c'
    WHEN 5 THEN '#16a34a'
    WHEN 6 THEN '#0891b2'
    WHEN 7 THEN '#ca8a04'
    WHEN 8 THEN '#dc2626'
    ELSE '#475569'
END
WHERE cor_escala IS NULL OR cor_escala = '';
