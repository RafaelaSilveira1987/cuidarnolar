<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function put(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('PUT', $uri, $action, $middleware);
    }

    public function delete(string $uri, string $action, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    private function addRoute(string $method, string $uri, string $action, array $middleware): self
    {
        [$controller, $methodName] = explode('@', $action);

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'action' => $methodName,
            'middleware' => $middleware,
            'pattern' => $this->buildPattern($uri),
        ];

        return $this;
    }

    private function buildPattern(string $uri): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->parseUri();

        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper((string) $_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method || !preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            $this->runMiddleware($route['middleware']);
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $controllerClass = str_contains($route['controller'], '\\')
                ? 'App\\' . $route['controller']
                : 'App\\Controllers\\' . $route['controller'];

            if (!class_exists($controllerClass)) {
                $this->abort(500, "Controller {$controllerClass} nao encontrado.");
            }

            $controller = new $controllerClass();
            $action = $route['action'];

            if (!method_exists($controller, $action)) {
                $this->abort(500, "Metodo {$action} nao existe em {$controllerClass}.");
            }

            $controller->$action(...array_values($params));
            return;
        }

        $this->abort(404);
    }

    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $basePath = parse_url((string) env('APP_URL', ''), PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        $uri = '/' . ltrim(rtrim($uri, '/'), '/');
        return $uri === '' ? '/' : $uri;
    }

    private function runMiddleware(array $middlewares): void
    {
        $map = [
            'auth' => \App\Middleware\AuthMiddleware::class,
            'admin' => \App\Middleware\AdminMiddleware::class,
            'csrf' => \App\Middleware\CsrfMiddleware::class,
            'api' => \App\Api\Middleware\ApiAuthMiddleware::class,
        ];

        foreach ($middlewares as $alias) {
            $class = $map[$alias] ?? $alias;
            if (!class_exists($class)) {
                $this->abort(500, "Middleware {$class} nao encontrado.");
            }

            (new $class())->handle();
        }
    }

    private function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        View::render("errors/{$code}", ['message' => $message], 'layouts/blank');
        exit;
    }
}
