# ERP Cuidar no Lar

Base MVC moderna em PHP puro para migração gradual do ERP Cuidar no Lar.

## Requisitos

- PHP 8.1+
- Composer
- MySQL/MariaDB

## Execução local

Com WAMP, a aplicação pode ser aberta via Apache em:

```text
http://localhost/cuidarnolar/public/login
```

Também é possível usar o servidor embutido do PHP:

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe -S 127.0.0.1:8000 -t public
```

URL:

```text
http://127.0.0.1:8000/login
```

## Configuração

Copie `.env.example` para `.env` e ajuste banco, URL e sessão conforme o ambiente.

```powershell
copy .env.example .env
```

Quando o Composer estiver disponível no PATH:

```powershell
composer install
composer dump-autoload
```

Enquanto `vendor/autoload.php` ainda não existe, o `public/index.php` usa um autoloader fallback para as classes `App\`.

## Sprint 0

Entregue:

- Estrutura base de diretórios
- Front controller em `public/index.php`
- Router com suporte a parâmetros e middleware
- Core de sessão, request, response, view, database e helpers
- Layouts básicos `main`, `auth` e `blank`
- Middleware de autenticação, admin e CSRF
- Tela de login renderizando via MVC
- Dashboard protegido
- `.htaccess` da raiz e de `public`
- `.gitignore` para `.env`, `vendor` e arquivos gerados

Próximo passo natural:

- Implementar Sprint 1 completa com `tb_usuarios`, autenticação real, testes de CSRF e proteção de rotas.
