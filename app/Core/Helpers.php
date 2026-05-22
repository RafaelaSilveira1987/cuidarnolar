<?php

function load_env_file(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("{$key}={$value}");
    }
}

function env(string $key, mixed $default = null): mixed
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return $value === false || $value === null ? $default : $value;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = rtrim((string) env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('/assets/' . ltrim($path, '/'));
}

function dd(mixed ...$vars): never
{
    echo '<pre style="background:#1a1a1a;color:#0f0;padding:20px;font-size:13px">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }
    echo '</pre>';
    exit;
}

function formatCpf(string $cpf): string
{
    $cpf = preg_replace('/\D/', '', $cpf) ?? '';
    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf) ?? $cpf;
}

function formatMoney(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function formatDate(?string $date): string
{
    if (!$date) {
        return '';
    }

    return date('d/m/Y', strtotime($date));
}

function view(string $path, array $data = [])
{
    extract($data);

    require __DIR__ . '/../Views/' . $path . '.php';
}