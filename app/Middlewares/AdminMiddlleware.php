<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Enums\Role;

final class AdminMiddleware extends RoleMiddleware
{
    protected function role(): Role { return Role::ADMIN; }
}
