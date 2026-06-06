# Segurança v30 — Checklist de publicação

Este patch é propositalmente conservador: adiciona a tela de checklist, um serviço de auditoria e uma ferramenta CLI.

## Copiar arquivos novos

Copie para o projeto:

- `app/Services/PublicationChecklist.php`
- `app/Views/configuracoes/checklist-publicacao.php`
- `public/assets/css/checklist_publicacao_v30.css`
- `docs/security/CHECKLIST_PUBLICACAO_V30.md`
- `tools/security_audit.php`

## Aplicar 3 pequenos ajustes

### 1. ConfiguracaoController.php

Use o trecho em:

- `_snippets/ConfiguracaoController_v30_metodo.php`

### 2. config/routes.php

Use o trecho em:

- `_snippets/routes_v30.php`

### 3. app/Views/configuracoes/_subnav.php

Use o trecho em:

- `_snippets/subnav_v30_item.php`

## Testar

Acesse:

`/configuracoes/checklist-publicacao`

E rode no terminal:

`php tools/security_audit.php`

## Observação

A tela mostra avisos. Nem todo aviso é quebra imediata; ele indica pontos para revisar antes da publicação.
