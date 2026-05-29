-- =========================================================
-- MIGRAÇÃO: separar responsável do cadastro do paciente
-- =========================================================
-- Ideia:
-- 1) tb_responsavel guarda os dados do responsável.
-- 2) tb_pacientes guarda apenas responsavel_id, ou seja, o vínculo.
-- 3) Os campos antigos responsavel_nome_texto, responsavel_parentesco,
--    responsavel_telefone e responsavel_email deixam de ser usados pela tela.
--
-- Rode em ambiente de teste primeiro. Faça backup antes, sem heroísmo.

-- 1) Recomendado para relacionamentos futuros.
ALTER TABLE tb_responsavel ENGINE = InnoDB;
ALTER TABLE tb_pacientes ENGINE = InnoDB;

-- 2) Garante que tb_pacientes tenha responsavel_id.
ALTER TABLE tb_pacientes
    ADD COLUMN IF NOT EXISTS responsavel_id INT NULL AFTER plano_saude;

-- 3) Migra responsáveis que estavam escritos direto no paciente.
-- CPF é obrigatório e único na sua tabela tb_responsavel. Quando o paciente não tem CPF do responsável,
-- usamos um código técnico MIG-0000000000 só para permitir a migração.
INSERT INTO tb_responsavel (
    nome_completo,
    endereco,
    numero,
    bairro,
    cidade,
    estado,
    cep,
    cpf,
    email,
    telefone,
    grau_parentesco,
    status
)
SELECT
    TRIM(p.responsavel_nome_texto) AS nome_completo,
    COALESCE(NULLIF(TRIM(p.endereco_completo), ''), 'Não informado') AS endereco,
    NULL AS numero,
    NULL AS bairro,
    'Muriaé' AS cidade,
    'MG' AS estado,
    NULL AS cep,
    CONCAT('MIG-', LPAD(p.id, 10, '0')) AS cpf,
    NULLIF(TRIM(p.responsavel_email), '') AS email,
    NULLIF(TRIM(p.responsavel_telefone), '') AS telefone,
    NULLIF(TRIM(p.responsavel_parentesco), '') AS grau_parentesco,
    'Ativo' AS status
FROM tb_pacientes p
LEFT JOIN tb_responsavel r
    ON r.nome_completo = p.responsavel_nome_texto
   AND COALESCE(r.telefone, '') = COALESCE(p.responsavel_telefone, '')
WHERE p.responsavel_id IS NULL
  AND p.responsavel_nome_texto IS NOT NULL
  AND TRIM(p.responsavel_nome_texto) <> ''
  AND r.id IS NULL;

-- 4) Vincula o paciente ao responsável migrado ou já existente.
UPDATE tb_pacientes p
JOIN tb_responsavel r
    ON r.nome_completo = p.responsavel_nome_texto
   AND (
        COALESCE(r.telefone, '') = COALESCE(p.responsavel_telefone, '')
        OR r.cpf = CONCAT('MIG-', LPAD(p.id, 10, '0'))
   )
SET p.responsavel_id = r.id
WHERE p.responsavel_id IS NULL
  AND p.responsavel_nome_texto IS NOT NULL
  AND TRIM(p.responsavel_nome_texto) <> '';

-- 5) Índice para melhorar as consultas da ficha do paciente.
CREATE INDEX IF NOT EXISTS idx_tb_pacientes_responsavel_id ON tb_pacientes (responsavel_id);

-- 6) Chave estrangeira opcional.
-- Se der erro aqui por dados antigos inconsistentes, deixe comentado por enquanto.
-- ALTER TABLE tb_pacientes
--     ADD CONSTRAINT fk_pacientes_responsavel
--     FOREIGN KEY (responsavel_id) REFERENCES tb_responsavel(id)
--     ON UPDATE CASCADE
--     ON DELETE SET NULL;

-- 7) Conferência rápida.
SELECT
    p.id AS paciente_id,
    p.nome_completo AS paciente,
    p.responsavel_id,
    r.nome_completo AS responsavel,
    r.telefone,
    r.email
FROM tb_pacientes p
LEFT JOIN tb_responsavel r ON r.id = p.responsavel_id
ORDER BY p.nome_completo;

-- 8) Só depois que tudo estiver conferido e funcionando, se quiser limpar fisicamente:
-- ALTER TABLE tb_pacientes DROP COLUMN responsavel_nome_texto;
-- ALTER TABLE tb_pacientes DROP COLUMN responsavel_parentesco;
-- ALTER TABLE tb_pacientes DROP COLUMN responsavel_telefone;
-- ALTER TABLE tb_pacientes DROP COLUMN responsavel_email;
