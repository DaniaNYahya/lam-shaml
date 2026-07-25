<?php
declare(strict_types=1);

namespace LamShaml\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'layout'): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require APP_PATH . '/Views/' . $view . '.php';
        $content = ob_get_clean();
        if ($layout === '') {
            return (string)$content;
        }
        ob_start();
        require APP_PATH . '/Views/' . $layout . '.php';
        return (string)ob_get_clean();
    }
}
