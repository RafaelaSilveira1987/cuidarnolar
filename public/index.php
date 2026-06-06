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

\App\Core\AppErrorHandler::register();
\App\Core\SecurityHeaders::send();
\App\Core\Session::start();

$router = require BASE_PATH . '/config/routes.php';
$router->dispatch();
