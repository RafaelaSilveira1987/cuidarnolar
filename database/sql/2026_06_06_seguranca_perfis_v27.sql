-- Segurança v27 — matriz real de perfis/papéis
-- Execute no HeidiSQL depois da Segurança v23/v26.
-- Este SQL cria/ajusta papéis reais do Cuidar no Lar e aplica uma matriz inicial de permissões.
-- Observação: para os papéis abaixo, as permissões existentes serão substituídas pela matriz sugerida.

START TRANSACTION;

-- Garante permissões usadas pela matriz atual.
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
('usuarios.permissoes', 'Segurança', 'Gerenciar permissões de usuários', 10),
('usuarios.gerenciar', 'Segurança', 'Gerenciar usuários e senhas', 20),
('auditoria.ver', 'Segurança', 'Ver auditoria do sistema', 30)
ON DUPLICATE KEY UPDATE
    modulo = VALUES(modulo),
    nome = VALUES(nome),
    ordem = VALUES(ordem),
    ativo = 1;

-- Mantém o Administrador como papel absoluto.
UPDATE tb_tipos_usuarios
SET nome_tipo = 'Administrador',
    descricao = 'Acesso total ao sistema, segurança, usuários, permissões, financeiro, operação e configurações.',
    prioridade = 1
WHERE id = 1;

-- Converte o antigo Editor em Coordenação, para evitar papel genérico solto.
UPDATE tb_tipos_usuarios
SET nome_tipo = 'Coordenação',
    descricao = 'Gerencia operação assistencial, pacientes, cuidadores, escala, contratos operacionais, planos e relatórios; sem baixa financeira nem permissões de segurança.',
    prioridade = 2
WHERE id = 2
  AND LOWER(nome_tipo) IN ('editor', 'coordenacao', 'coordenação');

-- Mantém/ajusta Visualizador.
UPDATE tb_tipos_usuarios
SET nome_tipo = 'Visualizador',
    descricao = 'Acesso somente leitura às áreas principais autorizadas.',
    prioridade = 90
WHERE id = 3
  AND LOWER(nome_tipo) IN ('visualizador', 'viewer');

-- Cria papéis reais se ainda não existirem.
INSERT INTO tb_tipos_usuarios (uuid, nome_tipo, descricao, prioridade)
SELECT UUID(), 'Financeiro', 'Gerencia contratos financeiros, contas a receber, contas a pagar, baixas e relatórios financeiros.', 3
WHERE NOT EXISTS (SELECT 1 FROM tb_tipos_usuarios WHERE LOWER(nome_tipo) = 'financeiro');

INSERT INTO tb_tipos_usuarios (uuid, nome_tipo, descricao, prioridade)
SELECT UUID(), 'Enfermagem/Técnico', 'Acompanha dados clínicos, medicações, planos de cuidado, relatórios assistenciais e rotinas de plantão.', 4
WHERE NOT EXISTS (SELECT 1 FROM tb_tipos_usuarios WHERE LOWER(nome_tipo) IN ('enfermagem/técnico', 'enfermagem/tecnico', 'técnico', 'tecnico'));

INSERT INTO tb_tipos_usuarios (uuid, nome_tipo, descricao, prioridade)
SELECT UUID(), 'Cuidador', 'Acesso operacional restrito para consultar escala, plano de cuidado e registrar/acompanhar relatórios de plantão.', 5
WHERE NOT EXISTS (SELECT 1 FROM tb_tipos_usuarios WHERE LOWER(nome_tipo) = 'cuidador');

-- Limpa permissões dos papéis reais não-admin para aplicar a matriz sugerida.
DELETE tp
FROM tb_acl_tipo_usuario_permissoes tp
JOIN tb_tipos_usuarios t ON t.id = tp.tipo_usuario_id
WHERE LOWER(t.nome_tipo) IN (
    'coordenação', 'coordenacao',
    'financeiro',
    'enfermagem/técnico', 'enfermagem/tecnico',
    'cuidador',
    'visualizador'
);

-- Admin recebe tudo.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p
WHERE LOWER(t.nome_tipo) = 'administrador'
  AND p.ativo = 1;

-- Coordenação: operação completa, sem baixar financeiro e sem segurança.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver','pacientes.criar','pacientes.editar','pacientes.inativar',
    'responsaveis.ver','responsaveis.criar','responsaveis.editar',
    'cuidadores.ver','cuidadores.criar','cuidadores.editar',
    'agenda.ver','agenda.editar',
    'escala.ver','escala.editar','escala.aprovar','escala.fechar',
    'planos.ver','planos.criar','planos.editar','planos.ativar','planos.pdf',
    'contratos.ver','contratos.criar','contratos.editar',
    'relatorios.ver','relatorios.editar','relatorios.pdf',
    'configuracoes.ver'
)
WHERE LOWER(t.nome_tipo) IN ('coordenação', 'coordenacao')
  AND p.ativo = 1;

-- Financeiro: financeiro e contratos, com visão básica de paciente/responsável.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver',
    'responsaveis.ver',
    'contratos.ver','contratos.criar','contratos.editar','contratos.gerar_financeiro',
    'financeiro.ver','financeiro.gerar','financeiro.baixar','financeiro.editar','financeiro.relatorios'
)
WHERE LOWER(t.nome_tipo) = 'financeiro'
  AND p.ativo = 1;

-- Enfermagem/Técnico: assistência, planos, medicações/relatórios via telas do paciente e escala em leitura.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver','pacientes.editar',
    'responsaveis.ver',
    'cuidadores.ver',
    'agenda.ver',
    'escala.ver',
    'planos.ver','planos.criar','planos.editar','planos.ativar','planos.pdf',
    'relatorios.ver','relatorios.editar','relatorios.pdf'
)
WHERE LOWER(t.nome_tipo) IN ('enfermagem/técnico', 'enfermagem/tecnico')
  AND p.ativo = 1;

-- Cuidador: operação restrita. A limitação por cuidador específico fica para a etapa de escopo por usuário.
INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id, p.id
FROM tb_tipos_usuarios t
JOIN tb_acl_permissoes p ON p.chave IN (
    'dashboard.ver',
    'pacientes.ver',
    'escala.ver',
    'planos.ver',
    'relatorios.ver','relatorios.editar'
)
WHERE LOWER(t.nome_tipo) = 'cuidador'
  AND p.ativo = 1;

-- Visualizador: leitura geral, sem financeiro e sem edição.
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
WHERE LOWER(t.nome_tipo) = 'visualizador'
  AND p.ativo = 1;

COMMIT;

-- Conferência rápida:
SELECT t.id, t.nome_tipo, COUNT(tp.permissao_id) AS total_permissoes
FROM tb_tipos_usuarios t
LEFT JOIN tb_acl_tipo_usuario_permissoes tp ON tp.tipo_usuario_id = t.id
GROUP BY t.id, t.nome_tipo
ORDER BY t.prioridade ASC, t.nome_tipo ASC;
