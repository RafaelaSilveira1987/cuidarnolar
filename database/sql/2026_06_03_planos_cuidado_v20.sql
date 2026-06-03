-- Planos de cuidado por paciente
-- Cria tabela própria para plano ativo, rascunhos e histórico de versões.

CREATE TABLE IF NOT EXISTS tb_planos_cuidado (
    id INT NOT NULL AUTO_INCREMENT,
    uuid CHAR(36) NOT NULL,
    paciente_id INT NOT NULL,
    modelo_chave VARCHAR(80) NULL,
    titulo VARCHAR(180) NOT NULL,
    subtitulo VARCHAR(255) NULL,
    responsavel_tecnico VARCHAR(120) NULL,
    data_inicio DATE NOT NULL,
    data_revisao DATE NULL,
    status ENUM('Rascunho','Ativo','Revisado','Arquivado') NOT NULL DEFAULT 'Rascunho',
    versao INT NOT NULL DEFAULT 1,
    resumo_clinico TEXT NULL,
    objetivos TEXT NULL,
    monitoramento TEXT NULL,
    oxigenoterapia TEXT NULL,
    nebulizacao TEXT NULL,
    controle_ambiental TEXT NULL,
    alimentacao_hidratacao TEXT NULL,
    atividade_repouso TEXT NULL,
    medicamentos TEXT NULL,
    comunicacao_familia TEXT NULL,
    sinais_alerta TEXT NULL,
    observacoes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_planos_cuidado_uuid (uuid),
    KEY idx_planos_cuidado_paciente (paciente_id),
    KEY idx_planos_cuidado_status (status),
    KEY idx_planos_cuidado_modelo (modelo_chave),
    CONSTRAINT fk_planos_cuidado_paciente
        FOREIGN KEY (paciente_id) REFERENCES tb_pacientes(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tb_planos_cuidado_modelos (
    id INT NOT NULL AUTO_INCREMENT,
    chave VARCHAR(80) NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ordem INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_planos_cuidado_modelos_chave (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tb_planos_cuidado_modelos (chave, nome, descricao, ativo, ordem)
VALUES
('respiratorio', 'Paciente respiratório', 'Asma, DPOC, oxigênio, nebulização e monitoramento respiratório.', 1, 10),
('acamado', 'Paciente acamado', 'Mudança de decúbito, pele, higiene, prevenção de lesão e conforto.', 1, 20),
('gtt_sne', 'Paciente com GTT/SNE', 'Cuidados com dieta enteral, dispositivos e sinais de alerta.', 1, 30),
('demencia', 'Paciente com demência', 'Rotina, segurança, comunicação, sono e comportamento.', 1, 40),
('pos_operatorio', 'Pós-operatório', 'Dor, curativos, mobilização, sinais de infecção e evolução.', 1, 50),
('pediatrico', 'Plano pediátrico', 'Rotina infantil, família, segurança, alimentação e sinais de alerta.', 1, 60),
('acompanhante_hospitalar', 'Acompanhante hospitalar', 'Rotina hospitalar, comunicação com equipe e apoio ao paciente.', 1, 70),
('geral', 'Plano geral home care', 'Modelo inicial para pacientes sem perfil específico identificado.', 1, 99)
ON DUPLICATE KEY UPDATE
    nome = VALUES(nome),
    descricao = VALUES(descricao),
    ativo = VALUES(ativo),
    ordem = VALUES(ordem),
    updated_at = CURRENT_TIMESTAMP;
