<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_START', microtime(true));
define('BASE_URL', '/cuidarnolar/public');

$autoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
} else {
    require BASE_PATH . '/app/Core/Helpers.php';
    spl_autoload_register(static function (string $class): void {
        $prefixes = [
            'App\\' => BASE_PATH . '/app/',
            'Config\\' => BASE_PATH . '/config/',
        ];

        foreach ($prefixes as $prefix => $baseDir) {
            if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
                continue;
            }

            $relative = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require $file;
            }
        }
    });
}

if (class_exists(Dotenv\Dotenv::class)) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile) && file_exists(BASE_PATH . '/.env.example')) {
    copy(BASE_PATH . '/.env.example', $envFile);
}

load_env_file($envFile);

$debug = env('APP_DEBUG', 'false') === 'true';
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    set_exception_handler(static function (Throwable $e): void {
        error_log('[APP ERROR] ' . $e->getMessage());
        \App\Core\View::render('errors/500', ['message' => 'Erro interno do servidor.'], 'layouts/blank');
        exit;
    });
}

\App\Core\Session::start();

$router = require BASE_PATH . '/config/routes.php';
$router->dispatch();