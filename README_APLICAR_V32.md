# Patch Segurança v32 — Teste de restauração e recuperação

## Arquivos novos

Copie para o projeto:

```text
tools/restore_test.php
docs/TESTE_RESTAURACAO_BACKUP.md
storage/restore-tests/.htaccess
storage/restore-tests/index.html
```

## Teste

No terminal, na raiz do projeto:

```bash
php -l tools/restore_test.php
php tools/restore_test.php --yes
```

Se quiser manter o banco restaurado para conferir no HeidiSQL:

```bash
php tools/restore_test.php --yes --keep
```

## Segurança

O script não restaura por cima do banco principal. Ele bloqueia caso o banco de teste tenha o mesmo nome do banco principal.
