-- Segurança v25 — Auditoria ampliada de ações críticas
-- Este SQL é idempotente para criar a tabela caso a v23 ainda não tenha sido aplicada.

CREATE TABLE IF NOT EXISTS tb_auditoria (
    id BIGINT NOT NULL AUTO_INCREMENT,
    usuario_id INT NULL,
    acao VARCHAR(80) NOT NULL,
    modulo VARCHAR(80) NOT NULL DEFAULT 'sistema',
    entidade VARCHAR(80) NULL,
    entidade_id VARCHAR(80) NULL,
    ip VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    detalhes LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_auditoria_usuario (usuario_id),
    KEY idx_auditoria_modulo (modulo),
    KEY idx_auditoria_acao (acao),
    KEY idx_auditoria_data (created_at),
    KEY idx_auditoria_entidade (entidade, entidade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
