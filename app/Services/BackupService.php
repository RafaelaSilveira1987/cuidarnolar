<?php

namespace App\Services;

use PDO;
use Throwable;

class BackupService
{
    private string $backupDir;

    public function __construct(?string $backupDir = null)
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $this->backupDir = $backupDir ?: $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        $this->garantirDiretorio();
    }

    public function gerarBackupBanco(): array
    {
        $this->garantirDiretorio();

        $pdo = $this->pdo();
        $dbName = (string)$this->env('DB_NAME', 'cuidarnolar');
        $stamp = date('Ymd_His');
        $filename = 'backup_' . $this->slug($dbName) . '_' . $stamp . '.sql';
        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Não foi possível criar o arquivo de backup.');
        }

        fwrite($handle, "-- Backup Cuidar no Lar\n");
        fwrite($handle, "-- Banco: {$dbName}\n");
        fwrite($handle, "-- Gerado em: " . date('Y-m-d H:i:s') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone = '+00:00';\n\n");

        $tables = $pdo->query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'')->fetchAll(PDO::FETCH_NUM);

        foreach ($tables as $tableRow) {
            $table = (string)($tableRow[0] ?? '');
            if ($table === '') {
                continue;
            }

            $quotedTable = $this->quoteIdentifier($table);
            fwrite($handle, "\n-- --------------------------------------------------------\n");
            fwrite($handle, "-- Estrutura da tabela {$table}\n\n");
            fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");

            $create = $pdo->query('SHOW CREATE TABLE ' . $quotedTable)->fetch(PDO::FETCH_ASSOC);
            $createSql = (string)($create['Create Table'] ?? array_values($create ?: [])[1] ?? '');
            fwrite($handle, $createSql . ";\n\n");

            fwrite($handle, "-- Dados da tabela {$table}\n\n");
            $stmt = $pdo->query('SELECT * FROM ' . $quotedTable);
            $columns = [];
            $insertBuffer = [];
            $rowCount = 0;

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($columns === []) {
                    $columns = array_keys($row);
                }

                $values = array_map(function (mixed $value) use ($pdo): string {
                    if ($value === null) {
                        return 'NULL';
                    }

                    if (is_int($value) || is_float($value)) {
                        return (string)$value;
                    }

                    return $pdo->quote((string)$value);
                }, array_values($row));

                $insertBuffer[] = '(' . implode(', ', $values) . ')';
                $rowCount++;

                if (count($insertBuffer) >= 100) {
                    fwrite($handle, $this->insertSql($table, $columns, $insertBuffer));
                    $insertBuffer = [];
                }
            }

            if ($insertBuffer !== []) {
                fwrite($handle, $this->insertSql($table, $columns, $insertBuffer));
            }

            fwrite($handle, "-- {$rowCount} registro(s) exportado(s) de {$table}\n");
        }

        fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return $this->infoArquivo($path);
    }

    public function listarBackups(): array
    {
        $this->garantirDiretorio();
        $files = glob($this->backupDir . DIRECTORY_SEPARATOR . '*.sql') ?: [];

        $items = array_map(fn(string $path): array => $this->infoArquivo($path), $files);

        usort($items, static function (array $a, array $b): int {
            return (int)($b['mtime'] ?? 0) <=> (int)($a['mtime'] ?? 0);
        });

        return $items;
    }

    public function excluirBackup(string $filename): bool
    {
        $path = $this->resolverArquivoSeguro($filename);
        if ($path === null || !is_file($path)) {
            return false;
        }

        return unlink($path);
    }

    public function resolverArquivoSeguro(string $filename): ?string
    {
        $filename = basename($filename);

        if (!preg_match('/^backup_[a-z0-9_\-]+_\d{8}_\d{6}\.sql$/i', $filename)) {
            return null;
        }

        $path = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        $realDir = realpath($this->backupDir);
        $realPath = realpath($path);

        if ($realDir === false || $realPath === false) {
            return null;
        }

        return str_starts_with($realPath, $realDir) ? $realPath : null;
    }

    public function limparAntigos(int $dias = 30): int
    {
        $dias = max(1, $dias);
        $limite = time() - ($dias * 86400);
        $removidos = 0;

        foreach ($this->listarBackups() as $backup) {
            if ((int)($backup['mtime'] ?? time()) < $limite) {
                if ($this->excluirBackup((string)$backup['filename'])) {
                    $removidos++;
                }
            }
        }

        return $removidos;
    }

    public function statusLogs(): array
    {
        $basePath = defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 2);
        $logDir = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
        $files = is_dir($logDir) ? (glob($logDir . DIRECTORY_SEPARATOR . '*') ?: []) : [];
        $totalBytes = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                $totalBytes += filesize($file) ?: 0;
            }
        }

        return [
            'path' => $logDir,
            'existe' => is_dir($logDir),
            'arquivos' => count(array_filter($files, 'is_file')),
            'bytes' => $totalBytes,
            'tamanho' => $this->formatBytes($totalBytes),
        ];
    }

    public function status(): array
    {
        $backups = $this->listarBackups();
        $ultimo = $backups[0] ?? null;

        return [
            'backup_dir' => $this->backupDir,
            'backup_dir_existe' => is_dir($this->backupDir),
            'total_backups' => count($backups),
            'ultimo_backup' => $ultimo,
            'logs' => $this->statusLogs(),
        ];
    }

    public function formatBytes(int|float $bytes): string
    {
        $bytes = (float)$bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return number_format($bytes, $i === 0 ? 0 : 2, ',', '.') . ' ' . $units[$i];
    }

    private function garantirDiretorio(): void
    {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0775, true);
        }

        $htaccess = $this->backupDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!is_file($htaccess)) {
            file_put_contents($htaccess, "Require all denied\nDeny from all\n");
        }

        $index = $this->backupDir . DIRECTORY_SEPARATOR . 'index.html';
        if (!is_file($index)) {
            file_put_contents($index, '');
        }
    }

    private function pdo(): PDO
    {
        $host = (string)$this->env('DB_HOST', '127.0.0.1');
        $port = (string)$this->env('DB_PORT', '3306');
        $db = (string)$this->env('DB_NAME', '');
        $user = (string)$this->env('DB_USER', 'root');
        $pass = (string)$this->env('DB_PASS', '');
        $charset = (string)$this->env('DB_CHARSET', 'utf8mb4');

        if ($db === '') {
            throw new \RuntimeException('DB_NAME não configurado.');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private function insertSql(string $table, array $columns, array $rows): string
    {
        if ($columns === [] || $rows === []) {
            return '';
        }

        $cols = implode(', ', array_map(fn(string $col): string => $this->quoteIdentifier($col), $columns));

        return 'INSERT INTO ' . $this->quoteIdentifier($table) . ' (' . $cols . ") VALUES\n"
            . implode(",\n", $rows) . ";\n";
    }

    private function quoteIdentifier(string $name): string
    {
        return '`' . str_replace('`', '``', $name) . '`';
    }

    private function infoArquivo(string $path): array
    {
        $mtime = is_file($path) ? (filemtime($path) ?: time()) : time();
        $size = is_file($path) ? (filesize($path) ?: 0) : 0;

        return [
            'filename' => basename($path),
            'path' => $path,
            'mtime' => $mtime,
            'data' => date('d/m/Y H:i', $mtime),
            'bytes' => $size,
            'tamanho' => $this->formatBytes($size),
        ];
    }

    private function slug(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_\-]+/', '_', $value) ?: 'banco';
        return trim($value, '_-') ?: 'banco';
    }

    private function env(string $key, mixed $default = null): mixed
    {
        if (function_exists('env')) {
            return env($key, $default);
        }

        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
