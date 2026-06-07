-- Segurança v33 — matriz final de perfis e permissões
START TRANSACTION;
INSERT INTO tb_acl_permissoes (chave, modulo, nome, descricao, ordem, ativo) VALUES
('medicacoes.ver','Medicações','Ver medicações','Permite visualizar medicações de pacientes.',10,1),
('medicacoes.editar','Medicações','Criar/editar medicações','Permite criar, editar, inativar e remover medicações.',20,1),
('anamneses.ver','Anamneses','Ver anamneses','Permite visualizar anamneses.',10,1),
('anamneses.editar','Anamneses','Criar/editar anamneses','Permite criar e editar anamneses.',20,1),
('historicos.ver','Históricos','Ver históricos','Permite visualizar históricos clínicos.',10,1),
('historicos.editar','Históricos','Criar/editar históricos','Permite criar e editar históricos clínicos.',20,1),
('diario.ver','Diário do paciente','Ver diário','Permite visualizar registros do diário do paciente.',10,1),
('diario.editar','Diário do paciente','Criar/editar diário','Permite criar e editar registros do diário do paciente.',20,1),
('configuracoes.ver','Configurações','Ver configurações','Permite acessar configurações não sensíveis.',10,1),
('configuracoes.editar','Configurações','Editar configurações','Permite alterar configurações e backups.',20,1),
('usuarios.gerenciar','Segurança','Gerenciar usuários','Permite criar, editar, ativar/inativar e resetar senhas de usuários.',20,1),
('usuarios.permissoes','Segurança','Gerenciar permissões','Permite alterar matriz de permissões.',10,1)
ON DUPLICATE KEY UPDATE modulo=VALUES(modulo), nome=VALUES(nome), descricao=VALUES(descricao), ordem=VALUES(ordem), ativo=1;

DELETE tp FROM tb_acl_tipo_usuario_permissoes tp JOIN tb_tipos_usuarios t ON t.id=tp.tipo_usuario_id
WHERE LOWER(t.nome_tipo) IN ('coordenação','coordenacao','financeiro','enfermagem/técnico','enfermagem/tecnico','cuidador','visualizador');

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p WHERE LOWER(t.nome_tipo)='administrador' AND p.ativo=1;

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p ON p.chave IN (
'dashboard.ver','pacientes.ver','pacientes.criar','pacientes.editar','pacientes.inativar','responsaveis.ver','responsaveis.criar','responsaveis.editar','cuidadores.ver','cuidadores.criar','cuidadores.editar','agenda.ver','agenda.editar','escala.ver','escala.editar','escala.aprovar','escala.fechar','planos.ver','planos.criar','planos.editar','planos.ativar','planos.pdf','medicacoes.ver','medicacoes.editar','anamneses.ver','anamneses.editar','historicos.ver','historicos.editar','diario.ver','diario.editar','contratos.ver','contratos.criar','contratos.editar','contratos.gerar_financeiro','relatorios.ver','relatorios.editar','relatorios.pdf','configuracoes.ver')
WHERE LOWER(t.nome_tipo) IN ('coordenação','coordenacao') AND p.ativo=1;

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p ON p.chave IN ('dashboard.ver','pacientes.ver','responsaveis.ver','contratos.ver','contratos.criar','contratos.editar','contratos.gerar_financeiro','financeiro.ver','financeiro.gerar','financeiro.baixar','financeiro.editar','financeiro.relatorios')
WHERE LOWER(t.nome_tipo)='financeiro' AND p.ativo=1;

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p ON p.chave IN ('dashboard.ver','pacientes.ver','pacientes.editar','responsaveis.ver','cuidadores.ver','agenda.ver','escala.ver','planos.ver','planos.criar','planos.editar','planos.ativar','planos.pdf','medicacoes.ver','medicacoes.editar','anamneses.ver','anamneses.editar','historicos.ver','historicos.editar','diario.ver','diario.editar','relatorios.ver','relatorios.editar','relatorios.pdf')
WHERE LOWER(t.nome_tipo) IN ('enfermagem/técnico','enfermagem/tecnico') AND p.ativo=1;

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p ON p.chave IN ('dashboard.ver','pacientes.ver','escala.ver','planos.ver','medicacoes.ver','relatorios.ver','relatorios.editar')
WHERE LOWER(t.nome_tipo)='cuidador' AND p.ativo=1;

INSERT IGNORE INTO tb_acl_tipo_usuario_permissoes (tipo_usuario_id, permissao_id)
SELECT t.id,p.id FROM tb_tipos_usuarios t JOIN tb_acl_permissoes p ON p.chave IN ('dashboard.ver','pacientes.ver','responsaveis.ver','cuidadores.ver','agenda.ver','escala.ver','planos.ver','planos.pdf','medicacoes.ver','anamneses.ver','historicos.ver','diario.ver','contratos.ver','relatorios.ver','relatorios.pdf')
WHERE LOWER(t.nome_tipo)='visualizador' AND p.ativo=1;
COMMIT;
