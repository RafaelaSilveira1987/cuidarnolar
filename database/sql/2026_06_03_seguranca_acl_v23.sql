-- Segurança v23 — ACL, auditoria e tentativas de login
-- Execute no HeidiSQL antes/depois de copiar os arquivos do patch.

CREATE TABLE IF NOT EXISTS tb_acl_permissoes (
    id INT NOT NULL AUTO_INCREMENT,
    chave VARCHAR(100) NOT NULL,
    modulo VARCHAR(60) NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao VARCHAR(255) NULL,
    ordem INT NOT NULL DEFAULT 100,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tb_acl_permissoes_chave (chave),
    KEY idx_tb_acl_permissoes_modulo (modulo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tb_acl_tipo_usuario_permissoes (
    tipo_usuario_id INT NOT NULL,
    permissao_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (tipo_usuario_id, permissao_id),
    KEY idx_acl_tipo_permissao (permissao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tb_acl_usuario_permissoes (
    usuario_id INT NOT NULL,
    permissao_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, permissao_id),
    KEY idx_acl_usuario_permissao (permissao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS tb_login_tentativas (
    id INT NOT NULL AUTO_INCREMENT,
    usuario_id INT NULL,
    username VARCHAR(100) NULL,
    ip VARCHAR(45) NULL,
    sucesso TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_tentativas_username (username),
    KEY idx_login_tentativas_ip (ip),
    KEY idx_login_tentativas_criado (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
    KEY idx_auditoria_data (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO tb_acl_permissoes (chave, modulo, nome, ordem) VALUES
('dashboard.ver', 'Dashboard', 'Ver dashboard', 10),
('pacientes.ver', 'Pacientes', 'Ver pacientes', 10),
('pacientes.criar', 'Pacientes', 'Criar pacientes', 20),
('pacientes.editar', 'Pacientes', 'Editar pacientes', 30),
('pacientes.inativar', 'Pacientes', 'Inativar pacientes', 40),
('responsaveis.ver', 'Responsáveis', 'Ver responsáveis', 10),
('responsaveis.criar', 'Responsáveis', 'Criar responsáveis', 20),
('responsaveis.editar', 'Responsáveis', 'Editar responsáveis', 30),
('cuidadores.ver', 'Cuidadores', 'Ver cuidadores', 10),
('cuidadores.criar', 'Cuidadores', 'Criar cuidadores', 20),
('cuidadores.editar', 'Cuidadores', 'Editar cuidadores', 30),
('agenda.ver', 'Agenda', 'Ver agenda', 10),
('agenda.editar', 'Agenda', 'Criar/editar agenda', 20),
('escala.ver', 'Escala', 'Ver escala', 10),
('escala.editar', 'Escala', 'Editar escala', 20),
('escala.aprovar', 'Escala', 'Aprovar escala', 30),
('escala.fechar', 'Escala', 'Fechar/cancelar fechamento da escala', 40),
('planos.ver', 'Planos de cuidado', 'Ver planos de cuidado', 10),
('planos.criar', 'Planos de cuidado', 'Criar planos de cuidado', 20),
('planos.editar', 'Planos de cuidado', 'Editar planos de cuidado', 30),
('planos.ativar', 'Planos de cuidado', 'Ativar planos de cuidado', 40),
('planos.pdf', 'Planos de cuidado', 'Gerar PDF do plano', 50),
('contratos.ver', 'Contratos', 'Ver contratos', 10),
('contratos.criar', 'Contratos', 'Criar contratos', 20),
('contratos.editar', 'Contratos', 'Editar contratos', 30),
('contratos.gerar_financeiro', 'Contratos', 'Gerar financeiro do contrato', 40),
('financeiro.ver', 'Financeiro', 'Ver financeiro', 10),
('financeiro.gerar', 'Financeiro', 'Gerar financeiro', 20),
('financeiro.baixar', 'Financeiro', 'Baixar recebimentos/pagamentos', 30),
('financeiro.editar', 'Financeiro', 'Editar lançamentos', 40),
('financeiro.relatorios', 'Financeiro', 'Ver relatórios financeiros', 50),
('relatorios.ver', 'Relatórios de plantão', 'Ver relatórios de plantão', 10),
('relatorios.editar', 'Relatórios de plantão', 'Criar/editar relatórios de plantão', 20),
('relatorios.pdf', 'Relatórios de plantão', 'Gerar PDF de relatórios', 30),
('configuracoes.ver', 'Configurações', 'Ver configurações', 10),
('configuracoes.editar', 'Configurações', 'Editar configurações', 20),
('usuarios.permissoes', 'Segurança', 'Gerenciar permissões de usuários', 10)
ON DUPLICATE KEY UPDATE
    modulo = VALUES(modulo),
    nome = VALUES(nome),
    ordem = VALUES(ordem),
    ativo = 1;

-- Admin recebe tudo.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p
WHERE LOWER(t.nome_tipo) = 'administrador';

-- Editor recebe operação, sem permissões sensíveis de segurança/configuração pesada.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver','pacientes.criar','pacientes.editar',
    'responsaveis.ver','responsaveis.criar','responsaveis.editar',
    'cuidadores.ver','cuidadores.criar','cuidadores.editar',
    'agenda.ver','agenda.editar',
    'escala.ver','escala.editar','escala.aprovar',
    'planos.ver','planos.criar','planos.editar','planos.ativar','planos.pdf',
    'contratos.ver','contratos.criar','contratos.editar','contratos.gerar_financeiro',
    'relatorios.ver','relatorios.editar','relatorios.pdf'
)
WHERE LOWER(t.nome_tipo) = 'editor';

-- Visualizador só vê áreas principais e PDFs/documentos, sem editar.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver',
    'responsaveis.ver',
    'cuidadores.ver',
    'agenda.ver',
    'escala.ver',
    'planos.ver','planos.pdf',
    'contratos.ver',
    'relatorios.ver','relatorios.pdf'
)
WHERE LOWER(t.nome_tipo) = 'visualizador';
