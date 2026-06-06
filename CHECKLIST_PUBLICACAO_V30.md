# Checklist de Publicação — Segurança v30

Use este checklist antes de colocar o Cuidar no Lar na web.

## Ambiente

- [ ] Domínio apontando para a pasta `public`.
- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_URL=https://seudominio.com.br`.
- [ ] HTTPS ativo e redirecionamento HTTP → HTTPS configurado.
- [ ] `SESSION_SECURE=auto` ou `true` em produção.

## Arquivos e servidor

- [ ] `.env` fora de acesso público.
- [ ] `app`, `vendor`, `config`, `database`, `storage` e backups bloqueados no navegador.
- [ ] Arquivos `.sql`, `.zip`, `.bak`, `.log` e `.env` bloqueados pelo servidor.
- [ ] Listagem de diretórios desativada.
- [ ] Logs técnicos não exibem detalhes na tela do usuário.

## Usuários e permissões

- [ ] Usuários de teste removidos ou inativados.
- [ ] Admin com senha forte e individual.
- [ ] Perfis revisados: Admin, Coordenação, Financeiro, Enfermagem/Técnico, Cuidador e Visualizador.
- [ ] Cuidador com `cuidador_id` vinculado quando for perfil Cuidador.
- [ ] Cuidador inativo não consegue logar.
- [ ] Usuário inativo não consegue logar.

## Rotas

- [ ] Rotas privadas com `auth`.
- [ ] Rotas POST com `csrf`.
- [ ] Rotas sensíveis com `can:*`.
- [ ] URLs públicas usando UUID, não ID sequencial.
- [ ] Acesso direto por URL testado com usuário sem permissão.

## Módulos críticos

- [ ] Financeiro não aparece para cuidador.
- [ ] Cuidador vê apenas pacientes/plantões do próprio escopo.
- [ ] Relatórios de plantão filtram cuidador corretamente.
- [ ] Configurações só aparecem para perfis autorizados.
- [ ] Baixa financeira exige permissão específica.
- [ ] Alteração de permissões exige permissão específica.

## Auditoria e backup

- [ ] `tb_auditoria` registrando ações críticas.
- [ ] Login, logout e acesso negado aparecem na auditoria.
- [ ] Edição de paciente, contrato, financeiro, escala e plano aparecem na auditoria.
- [ ] Backup automático do banco configurado.
- [ ] Backup dos arquivos do projeto configurado.
- [ ] Restauração de backup testada.
