<?php
declare(strict_types=1);
namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    public static function set(string $k, mixed $v): void { $_SESSION[$k] = $v; }
    public static function get(string $k, mixed $d = null): mixed { return $_SESSION[$k] ?? $d; }
    public static function has(string $k): bool { return isset($_SESSION[$k]); }
    public static function forget(string $k): void { unset($_SESSION[$k]); }
    public static function destroy(): void { $_SESSION = []; session_destroy(); }

    public static function flash(string $type, string $msg): void { $_SESSION['_flash'][$type] = $msg; }
    public static function getFlash(string $type): ?string
    {
        $m = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        return $m;
    }
}
