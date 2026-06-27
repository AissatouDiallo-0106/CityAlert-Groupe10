<?php
declare(strict_types=1);
namespace App\Core;

final class Request
{
    public function method(): string { return $_SERVER['REQUEST_METHOD'] ?? 'GET'; }
    public function isPost(): bool { return $this->method() === 'POST'; }

    public function path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return '/' . trim($path, '/');
    }

    public function get(string $k, mixed $d = null): mixed { return $_GET[$k] ?? $d; }
    public function post(string $k, mixed $d = null): mixed { return $_POST[$k] ?? $d; }
    public function all(): array { return $_POST + $_GET; }
    public function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_starts_with($this->path(), '/api/');
    }
}
