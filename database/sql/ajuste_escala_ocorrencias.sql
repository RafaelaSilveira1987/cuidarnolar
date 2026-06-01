-- =========================================================
-- Cuidar no Lar — ajustes para aprovação da escala
-- Rode no HeidiSQL antes de testar a aprovação novamente.
-- Faça backup antes. Banco de dados é igual coração de mãe: aguenta muito, mas não abuse.
-- =========================================================

-- 1) Garante a cor personalizada do cuidador, usada na escala.
-- Se a coluna já existir, pule este comando.
ALTER TABLE tb_cuidador
ADD COLUMN cor_avatar VARCHAR(20) NULL DEFAULT NULL AFTER contrato_horas;

-- 2) Remove duplicidades antigas de plantões geradas por aprovações repetidas.
-- Mantém o registro mais recente de cada mesmo paciente/escala/data/horário.
DELETE eo_antigo
FROM tb_escala_ocorrencias eo_antigo
INNER JOIN tb_escala_ocorrencias eo_novo
    ON eo_novo.escala_base_id = eo_antigo.escala_base_id
   AND eo_novo.paciente_id = eo_antigo.paciente_id
   AND eo_novo.data_plantao = eo_antigo.data_plantao
   AND eo_novo.inicio = eo_antigo.inicio
   AND eo_novo.fim = eo_antigo.fim
   AND eo_novo.id > eo_antigo.id;

-- 3) Impede que a aprovação crie plantão duplicado para o mesmo slot.
-- Se o índice já existir, pule este comando.
ALTER TABLE tb_escala_ocorrencias
ADD UNIQUE INDEX uq_escala_slot (
    escala_base_id,
    paciente_id,
    data_plantao,
    inicio,
    fim
);
