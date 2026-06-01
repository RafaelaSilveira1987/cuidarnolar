# Auditoria do projeto Cuidar no Lar

Data da análise: 31/05/2026

## Resultado geral

O projeto está estruturalmente válido para o ambiente atual `http://localhost/cuidarnolar/public`, com autoload, rotas principais, banco e views funcionando de forma coerente. O problema mais forte estava concentrado no módulo de escala/aprovação e em alguns pontos de integração entre controller, view e JavaScript.

## Validações realizadas

- Leitura completa do pacote `cuidarnolar(5).zip`.
- Leitura do dump `cuidarnolar.sql`.
- Conferência de `composer.json`, `.env`, `config/app.php`, `config/database.php`, `public/index.php` e `.htaccess`.
- Conferência de rotas em `config/routes.php` contra controllers e métodos existentes.
- Conferência das views chamadas pelos controllers.
- Conferência de models contra tabelas e colunas existentes no SQL.
- Validação de sintaxe PHP com `php -l` em `app`, `config` e `public`.
- Validação de sintaxe JavaScript com `node --check` no arquivo de escalas.

## Pontos que estavam corretos

- `.env` está coerente com WAMP/local:
  - `APP_URL=http://localhost/cuidarnolar/public`
  - `DB_DATABASE=cuidar_no_lar`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=` vazio
- O banco possui a tabela `tb_escala_aprovacoes`.
- A tabela `tb_escala_aprovacoes` já possui índice único por período:
  - `escala_base_id`, `paciente_id`, `data_inicio`, `data_fim`
- O dump já possui registro de aprovação para:
  - paciente `12`
  - escala base `3`
  - período `2026-05-31` até `2026-06-06`
  - status `aprovada`
- Após correções aplicadas no patch, todas as rotas declaradas apontam para métodos existentes.
- Após correções aplicadas no patch, todas as views chamadas pelos controllers existem.
- Após correções aplicadas no patch, os models conferidos não possuem campos `fillable` inexistentes no SQL.

## Problemas encontrados

### 1. EscalaController já estava usando EscalaAprovacao, mas a tela não exibia status da aprovação

A aprovação era registrada no banco, porém a tela de escala não consultava `tb_escala_aprovacoes` para mostrar visualmente que o período estava aprovado. Isso dava a sensação de que “não atualizou”.

Correção aplicada:

- `EscalaAprovacao::buscarPorPeriodo()` criado.
- `EscalaController::index()` agora busca a aprovação do período.
- `app/Views/escalas/index.php` exibe badge de aprovação e altera o botão para “Reconfirmar período” quando já existe aprovação.

### 2. Mensagens de sucesso/erro da escala não apareciam

O projeto usa flash messages via `Session::flash()`, mas o `EscalaController` salvava mensagens diretamente em `$_SESSION['success']`, `$_SESSION['error']` e `$_SESSION['erro']`. O layout lê apenas `_flashes`.

Correção aplicada:

- Troca das mensagens manuais por `$this->flash('success', ...)` e `$this->flash('error', ...)`.

### 3. EscalaController não chamava o construtor do BaseController

O controller sobrescrevia `__construct()` e não chamava `parent::__construct()`. Isso pode deixar propriedades herdadas sem inicialização.

Correção aplicada:

- `parent::__construct();` adicionado no construtor de `EscalaController`.

### 4. Rotas de escala estavam declaradas, mas métodos não existiam

As rotas abaixo existiam no `config/routes.php`, porém o controller não tinha todos os métodos correspondentes:

- `POST /escala/mover`
- `GET /escala/paciente/{uuid}`
- `GET /escala/colaborador/{uuid}`

Correção aplicada:

- Métodos `mover()`, `paciente()` e `colaborador()` adicionados em `EscalaController`.

### 5. JavaScript da escala não combinava com o HTML real

O JS procurava seletores como:

- `#modalEscalaCriar`
- `.esc-modal`
- `[data-escala-editar]`
- `[data-escala-substituir]`
- `.plantao-cell`

Mas o HTML real usa:

- `#modal-escala`
- `#modal-substituicao`
- `.modal-overlay`
- `.js-escala-editar`
- `.js-escala-substituir`
- `.escala-shift`

Resultado: editar, definir, substituir e arrastar/trocar não funcionavam direito.

Correção aplicada:

- `public/assets/js/escalas.js` refeito para usar os seletores reais.
- Inclusão de `_csrf` na troca por drag/drop.
- Ação dos formulários ajustada para as rotas reais.

### 6. Modais da escala não tinham CSS funcional

Os modais tinham `display:none`/`hidden`, mas faltava CSS de overlay/caixa e o JS antigo não abria corretamente.

Correção aplicada:

- Estilos de modal adicionados em `public/assets/css/escala.css`.
- Estilo de badge de aprovação adicionado.
- Estilo visual de drag/drop adicionado.

### 7. Substituição de cuidador tinha erros graves

Problemas encontrados:

- `EscalaController::substituir()` chamava `createRecord()`, mas `EscalaSubstituicao` não possui esse método.
- `EscalaSubstituicao` não declarava `$table`.
- O model tentava gravar `origem = 'manual'`, mas o enum do SQL aceita `Manual`, `Automatica` e `Substituicao`.
- A consulta de substituições usava `INNER JOIN tb_escala_base`; isso ignora ocorrências manuais sem `escala_base_id`.

Correções aplicadas:

- `EscalaController::substituir()` agora chama `EscalaSubstituicao::registrar()`.
- `EscalaSubstituicao` recebeu `$table = 'tb_escala_substituicoes'`.
- `origem` corrigido para `Manual`.
- Consulta de substituições alterada para aceitar ocorrências manuais com `LEFT JOIN`.

### 8. BaseModel não tratava NULL corretamente no bind

O `BaseModel::query()` vinculava qualquer valor não inteiro como `PDO::PARAM_STR`. Isso pode transformar `NULL` em string vazia em alguns cenários.

Correção aplicada:

- Tratamento específico para `null` com `PDO::PARAM_NULL`.

### 9. View `pacientes/medicacoes/index.php` não existia

O `MedicacaoPacienteController` chamava `pacientes/medicacoes/index`, mas essa view não existia.

Correção aplicada:

- Criada view básica de listagem em `app/Views/pacientes/medicacoes/index.php`.

### 10. Arquivo legado `app/helpers.php` tinha risco de conflito

O projeto usa `app/Core/Helpers.php`, mas existia também `app/helpers.php` com funções duplicadas. Se alguém incluísse esse arquivo, poderia causar erro de função redeclarada.

Correção aplicada:

- `app/helpers.php` virou apenas um arquivo legado que faz `require_once app/Core/Helpers.php`.

## Arquivos alterados no patch

- `app/Controllers/EscalaController.php`
- `app/Models/BaseModel.php`
- `app/Models/EscalaAprovacao.php`
- `app/Models/EscalaSubstituicao.php`
- `app/Views/escalas/index.php`
- `app/Views/escalas/partials/modal_substituicao.php`
- `app/Views/escalas/modal_substituicao.php`
- `app/Views/pacientes/medicacoes/index.php`
- `app/helpers.php`
- `public/assets/css/escala.css`
- `public/assets/js/escalas.js`

## Como aplicar

1. Faça backup da pasta atual do projeto.
2. Extraia o arquivo `patch_validacao_cuidarnolar.zip`.
3. Copie as pastas `app` e `public` extraídas para dentro de:

```text
C:\wamp64\www\cuidarnolar
```

4. Quando o Windows perguntar, confirme para substituir os arquivos.
5. Reinicie o Apache/WAMP.
6. Acesse:

```text
http://localhost/cuidarnolar/public/escala?paciente=7ea9f297-59cc-11f1-b6c0-089798669242&modo=semana&periodo=2026-05-31
```

## Teste recomendado depois de aplicar

1. Entrar na tela da escala.
2. Selecionar o paciente Ana Luiza Martins.
3. Verificar se aparece o badge de período aprovado.
4. Clicar em “Reconfirmar período”.
5. Conferir se aparece mensagem de sucesso.
6. Testar “Definir”, “Editar” e “Substituir” em um plantão.
7. Testar arrastar um plantão confirmado para outro confirmado, caso queira trocar cuidadores.

## Observação importante

O dump SQL enviado já mostra `tb_escala_aprovacoes` com registro aprovado. Então, para esse caso específico, o backend já chegou a registrar a aprovação. O que estava faltando era principalmente refletir isso na tela e corrigir a integração do módulo de escala.
