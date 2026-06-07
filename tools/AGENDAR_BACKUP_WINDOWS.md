# Agendar backup automático no Windows

Com o projeto em `C:\wamp64\www\cuidarnolar`, você pode criar uma tarefa no Agendador de Tarefas do Windows.

Programa/script:

```text
C:\wamp64\bin\php\php8.3.28\php.exe
```

Argumentos:

```text
C:\wamp64\www\cuidarnolar\tools\backup_database.php
```

Iniciar em:

```text
C:\wamp64\www\cuidarnolar
```

Sugestão: rodar todos os dias às 23:00.

Depois de criar, teste manualmente no PowerShell:

```powershell
php tools/backup_database.php
```

O backup será salvo em:

```text
storage/backups
```

Guarde uma cópia fora do servidor. Backup no mesmo computador ajuda, mas não salva em caso de perda do disco.
