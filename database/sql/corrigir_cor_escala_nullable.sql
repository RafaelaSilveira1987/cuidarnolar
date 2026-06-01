-- Garante que a coluna aceita a opção "sem cor" como NULL.
ALTER TABLE tb_escala_profissionais
MODIFY cor_escala VARCHAR(7) NULL DEFAULT NULL;

-- Opcional: limpar vínculos existentes que tenham string vazia.
UPDATE tb_escala_profissionais
SET cor_escala = NULL
WHERE TRIM(COALESCE(cor_escala, '')) = '';
