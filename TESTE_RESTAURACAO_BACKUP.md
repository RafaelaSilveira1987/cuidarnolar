# Teste de restauração de backup — Cuidar no Lar

Este procedimento valida se o backup gerado pelo sistema consegue ser restaurado em um banco separado.

## Regra de ouro

Nunca restaure teste por cima do banco principal.

O script `tools/restore_test.php` cria um banco separado com sufixo `_restore_test`, importa o backup e valida se as tabelas voltaram.

## Uso básico

```bash
php tools/restore_test.php --yes
```

Sem informar arquivo, o script usa o backup `.sql` mais recente da pasta:

```text
storage/backups
```

## Manter o banco restaurado para inspeção

```bash
php tools/restore_test.php --yes --keep
```

Depois você pode abrir no HeidiSQL o banco:

```text
cuidar_no_lar_restore_test
```

## Testar um arquivo específico

```bash
php tools/restore_test.php --file="storage/backups/backup_cuidar_no_lar_20260606_180855.sql" --yes
```

## Banco de teste com outro nome

```bash
php tools/restore_test.php --database="cuidar_no_lar_teste_restore" --yes --keep
```

## Se o mysql.exe não for encontrado

Adicione no `.env`:

```env
MYSQL_BIN=C:\wamp64\bin\mysql\mysql8.4.3\bin\mysql.exe
```

Ajuste a versão conforme sua instalação do WAMP.

## Validação esperada

Ao final, deve aparecer algo parecido com:

```text
Teste de restauração concluído com sucesso!
- Banco de teste recriado.
- Backup importado no banco de teste.
- Tabelas restauradas: 40
- Tabelas importantes encontradas: tb_usuarios, tb_pacientes, tb_cuidador...
```

## Frequência recomendada

- Fazer backup automático diariamente.
- Testar restauração pelo menos 1 vez por semana durante implantação.
- Depois de estabilizar, testar restauração 1 vez por mês.
