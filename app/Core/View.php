<?php

namespace App\Core;

class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewBase = BASE_PATH . '/app/Views/';
        $viewFile = $viewBase . $template . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View '{$template}' nao encontrada em {$viewFile}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $layoutFile = $viewBase . $layout . '.php';
        if (!file_exists($layoutFile)) {
            echo $content;
            return;
        }

        include $layoutFile;
    }
}
