<?php

$rootPath = dirname(__DIR__);
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);

        $value = trim($value, "\"'");

        if ($key !== '') {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

$bootstrap = __DIR__ . '/../config/bootstrap.php';
if (is_file($bootstrap)) {
    require_once $bootstrap;
}

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

try {
    $service = new \App\Services\BackupService();
    $backup = $service->gerarBackupBanco();
    $removidos = $service->limparAntigos((int)(getenv('BACKUP_KEEP_DAYS') ?: 30));

    echo "Backup gerado com sucesso:\n";
    echo $backup['path'] . "\n";
    echo "Tamanho: " . $backup['tamanho'] . "\n";

    if ($removidos > 0) {
        echo "Backups antigos removidos: {$removidos}\n";
    }

    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "Erro ao gerar backup: " . $e->getMessage() . "\n");
    exit(1);
}