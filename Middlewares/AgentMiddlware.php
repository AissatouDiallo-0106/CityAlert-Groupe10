<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Enums\Role;

final class AgentMiddleware extends RoleMiddleware
{
    protected function role(): Role { return Role::AGENT; }
}
