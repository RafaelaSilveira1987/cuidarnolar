<?php

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}
function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . ($_SESSION['_csrf'] ?? '') . '">';
}

function input($key): ?string
{
    return isset($_GET[$key]) && is_scalar($_GET[$key])
        ? trim(strip_tags($_GET[$key]))
        : null;
}