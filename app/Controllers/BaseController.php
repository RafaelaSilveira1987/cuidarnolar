<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Core\View;

abstract class BaseController
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    protected function view(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $data['_user'] = Session::get('user');
        $data['_flashes'] = Session::getFlashes();
        $data['_csrf'] = Session::getCsrfToken();
        View::render($template, $data, $layout);
    }

    protected function redirect(string $uri): void
    {
        header('Location: ' . url($uri));
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }

    protected function flash(string $type, string $message): void
    {
        Session::flash($type, $message);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
