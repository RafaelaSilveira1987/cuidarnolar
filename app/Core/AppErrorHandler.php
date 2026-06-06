<?php

namespace App\Core;

use Throwable;

class AppErrorHandler
{
    public static function register(): void
    {
        $debug = self::isDebug();

        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        $logFile = self::logFile();
        if ($logFile !== '') {
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0775, true);
            }
            ini_set('error_log', $logFile);
        }

        set_error_handler(static function (int $severity, string $message, string $file, int $line) use ($debug): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $entry = sprintf('[PHP ERROR] %s in %s:%d', $message, $file, $line);
            error_log($entry);

            if ($debug) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(static function (Throwable $e) use ($debug): void {
            self::logThrowable($e);

            if (headers_sent()) {
                echo $debug
                    ? '<pre>' . htmlspecialchars((string)$e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>'
                    : 'Ocorreu um erro inesperado.';
                exit;
            }

            http_response_code(500);

            if ($debug) {
                View::render('errors/500', [
                    'message' => $e->getMessage(),
                    'details' => (string)$e,
                ], 'layouts/blank');
                exit;
            }

            View::render('errors/500', [
                'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            ], 'layouts/blank');
            exit;
        });

        register_shutdown_function(static function () use ($debug): void {
            $error = error_get_last();
            if (!$error) {
                return;
            }

            $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
            if (!in_array((int)$error['type'], $fatalTypes, true)) {
                return;
            }

            error_log(sprintf(
                '[PHP FATAL] %s in %s:%d',
                $error['message'] ?? 'Erro fatal',
                $error['file'] ?? 'arquivo desconhecido',
                (int)($error['line'] ?? 0)
            ));

            if ($debug || headers_sent()) {
                return;
            }

            http_response_code(500);
            View::render('errors/500', [
                'message' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            ], 'layouts/blank');
        });
    }

    public static function isDebug(): bool
    {
        return filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function isProduction(): bool
    {
        return strtolower((string)env('APP_ENV', 'local')) === 'production';
    }

    private static function logFile(): string
    {
        $configured = trim((string)env('APP_ERROR_LOG', ''));
        if ($configured !== '') {
            return str_starts_with($configured, DIRECTORY_SEPARATOR)
                ? $configured
                : BASE_PATH . DIRECTORY_SEPARATOR . ltrim($configured, DIRECTORY_SEPARATOR);
        }

        return BASE_PATH . '/storage/logs/app-' . date('Y-m-d') . '.log';
    }

    private static function logThrowable(Throwable $e): void
    {
        error_log(sprintf(
            "[APP EXCEPTION] %s: %s in %s:%d\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
    }
}
