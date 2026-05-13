-- Evolução financeira homecare (MySQL / MariaDB)
-- Execute uma vez no banco do projeto. Se alguma coluna já existir, comente a linha correspondente do ALTER.

-- Camada 2 — categorias (entrada / saída)
CREATE TABLE IF NOT EXISTS tb_categorias_financeiro (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    tipo ENUM('entrada', 'saida') NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uq_categorias_nome_tipo (nome, tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO tb_categorias_financeiro (nome, tipo, ativo) VALUES
('Mensalidade', 'entrada', 1),
('Plantão / extra', 'entrada', 1),
('Reembolso insumo', 'entrada', 1),
('Salário cuidador', 'saida', 1),
('Encargos trabalhistas', 'saida', 1),
('Insumos e materiais', 'saida', 1),
('Transporte', 'saida', 1),
('Despesas administrativas', 'saida', 1);

-- Camada 1 — contrato por paciente (parcelas automáticas: fase posterior)
CREATE TABLE IF NOT EXISTS tb_contratos_paciente (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    tipo_servico VARCHAR(120) NOT NULL,
    valor_mensal DECIMAL(12,2) NOT NULL,
    dia_vencimento TINYINT UNSIGNED NOT NULL DEFAULT 10,
    forma_pagamento VARCHAR(40) NULL,
    vigencia_inicio DATE NOT NULL,
    vigencia_fim DATE NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Ativo',
    observacoes TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_contratos_paciente (paciente_id),
    KEY idx_contratos_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opcional: FK do contrato para paciente
-- ALTER TABLE tb_contratos_paciente ADD CONSTRAINT fk_contratos_paciente FOREIGN KEY (paciente_id) REFERENCES tb_pacientes (id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Camada 2/3 — campos adicionais em lançamentos (nullable = compatível com legado)
ALTER TABLE tb_financeiro
    ADD COLUMN categoria_id INT UNSIGNED NULL DEFAULT NULL AFTER tipo_transacao,
    ADD COLUMN data_vencimento DATE NULL DEFAULT NULL AFTER data,
    ADD COLUMN data_pagamento DATE NULL DEFAULT NULL AFTER data_vencimento,
    ADD KEY idx_financeiro_categoria (categoria_id),
    ADD KEY idx_financeiro_vencimento (data_vencimento);

-- Opcional: integridade referencial (descomente se desejar FK)
-- ALTER TABLE tb_financeiro ADD CONSTRAINT fk_financeiro_categoria FOREIGN KEY (categoria_id) REFERENCES tb_categorias_financeiro (id) ON DELETE SET NULL ON UPDATE CASCADE;
