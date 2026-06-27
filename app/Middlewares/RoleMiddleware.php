<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Core\{Request, Response};
use App\Enums\Role;
use App\Services\AuthService;

/**
 * Base abstraite : protège une route selon un rôle requis.
 */
abstract class RoleMiddleware
{
    abstract protected function role(): Role;

    public function handle(Request $req): ?Response
    {
        $user = (new AuthService())->currentUser();
        if (!$user || !$user->hasRole($this->role())) {
            return Response::html('<h1>403 — Accès refusé</h1>', 403);
        }
        return null;
    }
}
