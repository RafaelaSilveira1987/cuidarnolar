-- =====================================================
-- Ajuste Responsáveis dentro da ficha do Paciente
-- Cuidar no Lar
-- =====================================================

-- 1) Recomendado: permitir relacionamento seguro.
-- Se sua tabela tb_pacientes já estiver em InnoDB, ótimo.
-- Se estiver em MyISAM, o MySQL não aplica chave estrangeira de verdade.
ALTER TABLE tb_responsavel ENGINE = InnoDB;
ALTER TABLE tb_pacientes ENGINE = InnoDB;

-- 2) Garante índice no vínculo paciente -> responsável.
-- Rode apenas se ainda não existir índice para responsavel_id.
CREATE INDEX idx_pacientes_responsavel_id ON tb_pacientes (responsavel_id);

-- 3) Garante FK entre paciente e responsável.
-- Rode apenas se ainda não existir constraint parecida.
ALTER TABLE tb_pacientes
ADD CONSTRAINT fk_pacientes_responsavel
FOREIGN KEY (responsavel_id)
REFERENCES tb_responsavel(id)
ON UPDATE CASCADE
ON DELETE SET NULL;

-- Observação:
-- A aba nova também exibe os campos antigos do paciente:
-- responsavel_nome_texto, responsavel_parentesco, responsavel_telefone, responsavel_email.
-- Então você não perde informação já cadastrada.
