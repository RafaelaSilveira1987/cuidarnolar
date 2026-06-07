<?php
/**
 * Teste seguro de restauração de backup.
 *
 * Uso:
 *   php tools/restore_test.php --yes
 *   php tools/restore_test.php --file="storage/backups/backup_cuidar_no_lar_20260606_180855.sql" --yes
 *   php tools/restore_test.php --database="cuidar_no_lar_restore_test" --yes --keep
 *
 * O script NUNCA restaura por cima do banco principal. Ele cria um banco separado
 * com sufixo _restore_test, importa o backup e valida se as tabelas voltaram.
 */

$rootPath = dirname(__DIR__);

loadEnv($rootPath . DIRECTORY_SEPARATOR . '.env');

$options = parseArgs($argv ?? []);

$mainDb = envValue('DB_NAME') ?: envValue('DB_DATABASE');
$dbUser = envValue('DB_USER') ?: envValue('DB_USERNAME') ?: 'root';
$dbPass = envValue('DB_PASS') ?: envValue('DB_PASSWORD') ?: '';
$dbHost = envValue('DB_HOST') ?: 'localhost';
$dbPort = envValue('DB_PORT') ?: '3306';

if (!$mainDb) {
    fail('DB_NAME/DB_DATABASE não configurado no .env.');
}

$testDb = $options['database'] ?? ($mainDb . '_restore_test');

if ($testDb === $mainDb) {
    fail('Por segurança, o banco de teste não pode ter o mesmo nome do banco principal.');
}

$backupFile = $options['file'] ?? latestBackup($rootPath);

if (!$backupFile || !is_file($backupFile)) {
    fail('Nenhum arquivo de backup encontrado. Informe --file="caminho/do/backup.sql".');
}

$backupFile = realpath($backupFile) ?: $backupFile;
$mysqlBin = findMysqlBin();

if (!$mysqlBin) {
    fail('mysql.exe não encontrado. Adicione MYSQL_BIN no .env ou coloque o MySQL no PATH.');
}

$logDir = $rootPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'restore-tests';
ensureDir($logDir);
$logFile = $logDir . DIRECTORY_SEPARATOR . 'restore_test_' . date('Ymd_His') . '.txt';

$keep = isset($options['keep']);
$yes = isset($options['yes']);

printLine('Teste de restauração de backup');
printLine('Banco principal: ' . $mainDb);
printLine('Banco de teste:   ' . $testDb);
printLine('Backup:           ' . $backupFile);
printLine('MySQL CLI:        ' . $mysqlBin);
printLine('Log:              ' . $logFile);
printLine('');

if (!$yes) {
    printLine('Este teste vai APAGAR e RECRIAR apenas o banco de teste: ' . $testDb);
    printLine('Ele não mexe no banco principal: ' . $mainDb);
    printLine('Execute novamente com --yes para confirmar.');
    exit(0);
}

$startedAt = microtime(true);
$summary = [];

try {
    $createSql = sprintf(
        "DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;",
        str_replace('`', '``', $testDb),
        str_replace('`', '``', $testDb)
    );

    runMysql($mysqlBin, $dbHost, $dbPort, $dbUser, $dbPass, null, ['-e', $createSql], $logFile);
    $summary[] = 'Banco de teste recriado.';

    runMysqlImport($mysqlBin, $dbHost, $dbPort, $dbUser, $dbPass, $testDb, $backupFile, $logFile);
    $summary[] = 'Backup importado no banco de teste.';

    $tablesCount = trim(runMysqlCapture($mysqlBin, $dbHost, $dbPort, $dbUser, $dbPass, null, [
        '-N',
        '-B',
        '-e',
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . addslashes($testDb) . "';"
    ], $logFile));

    $tablesCountInt = (int)$tablesCount;
    if ($tablesCountInt <= 0) {
        fail('Restauração importou 0 tabelas. Verifique o arquivo SQL.', $logFile);
    }

    $summary[] = 'Tabelas restauradas: ' . $tablesCountInt;

    $importantTables = [
        'tb_usuarios',
        'tb_pacientes',
        'tb_cuidador',
        'tb_responsavel',
        'tb_financeiro',
        'tb_auditoria',
    ];

    $foundImportant = [];
    foreach ($importantTables as $table) {
        $exists = trim(runMysqlCapture($mysqlBin, $dbHost, $dbPort, $dbUser, $dbPass, $testDb, [
            '-N',
            '-B',
            '-e',
            "SHOW TABLES LIKE '" . addslashes($table) . "';"
        ], $logFile));

        if ($exists !== '') {
            $foundImportant[] = $table;
        }
    }

    $summary[] = 'Tabelas importantes encontradas: ' . ($foundImportant ? implode(', ', $foundImportant) : 'nenhuma');

    $duration = round(microtime(true) - $startedAt, 2);
    $summary[] = 'Duração: ' . $duration . 's';

    if (!$keep) {
        $dropSql = sprintf("DROP DATABASE IF EXISTS `%s`;", str_replace('`', '``', $testDb));
        runMysql($mysqlBin, $dbHost, $dbPort, $dbUser, $dbPass, null, ['-e', $dropSql], $logFile);
        $summary[] = 'Banco de teste removido após validação. Use --keep para manter.';
    } else {
        $summary[] = 'Banco de teste mantido para inspeção manual.';
    }

    appendLog($logFile, PHP_EOL . 'RESUMO:' . PHP_EOL . implode(PHP_EOL, $summary) . PHP_EOL);

    printLine('');
    printLine('Teste de restauração concluído com sucesso!');
    foreach ($summary as $line) {
        printLine('- ' . $line);
    }
    printLine('');
    printLine('Log salvo em: ' . $logFile);
    exit(0);
} catch (Throwable $e) {
    appendLog($logFile, PHP_EOL . 'ERRO: ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, 'Erro no teste de restauração: ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, 'Log salvo em: ' . $logFile . PHP_EOL);
    exit(1);
}

function loadEnv(string $envPath): void
{
    if (!is_file($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
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

function envValue(string $key): ?string
{
    $value = getenv($key);
    if ($value !== false) {
        return (string)$value;
    }

    return isset($_ENV[$key]) ? (string)$_ENV[$key] : null;
}

function parseArgs(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (!str_starts_with($arg, '--')) {
            continue;
        }
        $arg = substr($arg, 2);
        if (str_contains($arg, '=')) {
            [$key, $value] = explode('=', $arg, 2);
            $out[$key] = trim($value, "\"'");
        } else {
            $out[$arg] = true;
        }
    }
    return $out;
}

function latestBackup(string $rootPath): ?string
{
    $dir = $rootPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
    $files = glob($dir . DIRECTORY_SEPARATOR . '*.sql') ?: [];
    if (!$files) {
        return null;
    }

    usort($files, static fn(string $a, string $b): int => filemtime($b) <=> filemtime($a));
    return $files[0];
}

function findMysqlBin(): ?string
{
    $fromEnv = envValue('MYSQL_BIN') ?: envValue('MYSQL_PATH');
    if ($fromEnv && is_file($fromEnv)) {
        return $fromEnv;
    }

    $candidates = [
        'mysql',
        'mysql.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.4.3\\bin\\mysql.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.3.0\\bin\\mysql.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysql.exe',
        'C:\\xampp\\mysql\\bin\\mysql.exe',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate) || in_array($candidate, ['mysql', 'mysql.exe'], true)) {
            return $candidate;
        }
    }

    $glob = glob('C:\\wamp64\\bin\\mysql\\mysql*\\bin\\mysql.exe') ?: [];
    if ($glob) {
        usort($glob, static fn(string $a, string $b): int => strcmp($b, $a));
        return $glob[0];
    }

    return null;
}

function mysqlArgs(string $host, string $port, string $user, string $pass, ?string $database = null): array
{
    $args = ['-h', $host, '-P', $port, '-u', $user];
    if ($pass !== '') {
        $args[] = '--password=' . $pass;
    }
    if ($database) {
        $args[] = $database;
    }
    return $args;
}

function runMysql(string $bin, string $host, string $port, string $user, string $pass, ?string $db, array $extraArgs, string $logFile): void
{
    $cmd = array_merge([$bin], mysqlArgs($host, $port, $user, $pass, $db), $extraArgs);
    runCommand($cmd, null, $logFile);
}

function runMysqlCapture(string $bin, string $host, string $port, string $user, string $pass, ?string $db, array $extraArgs, string $logFile): string
{
    $cmd = array_merge([$bin], mysqlArgs($host, $port, $user, $pass, $db), $extraArgs);
    return runCommand($cmd, null, $logFile, true);
}

function runMysqlImport(string $bin, string $host, string $port, string $user, string $pass, string $db, string $file, string $logFile): void
{
    $cmd = array_merge([$bin], mysqlArgs($host, $port, $user, $pass, $db));
    runCommand($cmd, $file, $logFile);
}

function runCommand(array $cmd, ?string $stdinFile, string $logFile, bool $capture = false): string
{
    appendLog($logFile, 'CMD: ' . implode(' ', array_map('maskArg', $cmd)) . PHP_EOL);

    $descriptors = [
        0 => $stdinFile ? ['file', $stdinFile, 'r'] : ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Não foi possível iniciar o processo mysql.');
    }

    if (!$stdinFile && isset($pipes[0]) && is_resource($pipes[0])) {
        fclose($pipes[0]);
    }

    $stdout = isset($pipes[1]) ? stream_get_contents($pipes[1]) : '';
    $stderr = isset($pipes[2]) ? stream_get_contents($pipes[2]) : '';

    if (isset($pipes[1]) && is_resource($pipes[1])) {
        fclose($pipes[1]);
    }
    if (isset($pipes[2]) && is_resource($pipes[2])) {
        fclose($pipes[2]);
    }

    $code = proc_close($process);
    appendLog($logFile, 'EXIT: ' . $code . PHP_EOL);
    if ($stdout !== '') {
        appendLog($logFile, 'STDOUT:' . PHP_EOL . $stdout . PHP_EOL);
    }
    if ($stderr !== '') {
        appendLog($logFile, 'STDERR:' . PHP_EOL . $stderr . PHP_EOL);
    }

    if ($code !== 0) {
        throw new RuntimeException(trim($stderr) ?: 'Comando mysql retornou código ' . $code);
    }

    return $capture ? $stdout : '';
}

function ensureDir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function appendLog(string $file, string $text): void
{
    file_put_contents($file, '[' . date('Y-m-d H:i:s') . '] ' . $text, FILE_APPEND);
}

function fail(string $message, ?string $logFile = null): never
{
    if ($logFile) {
        appendLog($logFile, 'FALHA: ' . $message . PHP_EOL);
    }
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

function printLine(string $text): void
{
    echo $text . PHP_EOL;
}

function maskArg(string $arg): string
{
    if (str_starts_with($arg, '--password=')) {
        return '--password=***';
    }
    return $arg;
}
