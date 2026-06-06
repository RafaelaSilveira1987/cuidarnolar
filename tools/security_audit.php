<?php

/**
 * Auditoria rápida de rotas para publicação.
 * Uso: php tools/security_audit.php
 */

declare(strict_types=1);

$basePath = dirname(__DIR__);
$routeFile = $basePath . '/config/routes.php';

if (!is_file($routeFile)) {
    fwrite(STDERR, "config/routes.php não encontrado.\n");
    exit(1);
}

$content = (string)file_get_contents($routeFile);
$pattern = '/\$router->(get|post)\(\s*[\'\"]([^\'\"]+)[\'\"]\s*,\s*[\'\"]([^\'\"]+)[\'\"]\s*(?:,\s*(\[[^\)]*?\]))?\s*\);/s';
preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

$issues = [];

$has = static fn(string $raw, string $mw): bool => preg_match('/[\'\"]' . preg_quote($mw, '/') . '[\'\"]/', $raw) === 1;
$isSensitive = static function (string $path): bool {
    foreach (['/pacientes', '/cuidadores', '/responsaveis', '/financeiro', '/configuracoes', '/escala', '/relatorio-plantao', '/medicacoes', '/anamneses', '/historicos'] as $prefix) {
        if (str_starts_with($path, $prefix)) {
            return true;
        }
    }
    return false;
};

foreach ($matches as $match) {
    $method = strtolower($match[1]);
    $path = $match[2];
    $handler = $match[3];
    $middlewares = $match[4] ?? '';
    $publicLogin = $path === '/login';

    if (!$publicLogin && !$has($middlewares, 'auth')) {
        $issues[] = ['ERRO', strtoupper($method), $path, $handler, 'Rota privada sem auth'];
    }

    if ($method === 'post' && !$has($middlewares, 'csrf')) {
        $issues[] = ['ERRO', strtoupper($method), $path, $handler, 'POST sem csrf'];
    }

    if (str_contains($path, '{id}')) {
        $issues[] = ['AVISO', strtoupper($method), $path, $handler, 'Rota expõe {id}; preferir UUID'];
    }

    if ($isSensitive($path) && !$publicLogin && !str_contains($middlewares, 'can:')) {
        $issues[] = ['AVISO', strtoupper($method), $path, $handler, 'Rota sensível sem can:*'];
    }
}

echo "Rotas auditadas: " . count($matches) . PHP_EOL;
echo "Achados: " . count($issues) . PHP_EOL . PHP_EOL;

foreach ($issues as [$level, $method, $path, $handler, $message]) {
    echo "[$level] $method $path -> $handler | $message" . PHP_EOL;
}

exit($issues === [] ? 0 : 2);
