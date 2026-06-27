<?php
declare(strict_types=1);
namespace App\Core;

/**
 * Moteur de vues minimal : rend un template PHP dans un layout.
 */
final class View
{
    private static string $path = '';

    public static function setPath(string $path): void { self::$path = rtrim($path, '/'); }

    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): string
    {
        $content = self::partial($view, $data);
        if ($layout === '') {
            return $content;
        }
        return self::partial($layout, array_merge($data, ['content' => $content]));
    }

    public static function partial(string $view, array $data = []): string
    {
        $file = self::$path . '/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Vue introuvable : $view");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
