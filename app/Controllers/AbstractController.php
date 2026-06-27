<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Request, Response, View, Session};
use App\Enums\Role;
use App\Models\Entities\User;
use App\Services\AuthService;
use App\Exceptions\{AuthenticationException, AuthorizationException};

abstract class AbstractController
{
    protected AuthService $auth;

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): Response
    {
        $data += [
            'currentUser'  => $this->auth->currentUser(),
            'flashSuccess' => Session::getFlash('success'),
            'flashError'   => Session::getFlash('error'),
        ];
        return Response::html(View::render($view, $data, $layout));
    }

    protected function redirect(string $path, string $flash = '', string $type = 'success'): Response
    {
        if ($flash !== '') Session::flash($type, $flash);
        return Response::redirect(url($path));
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function user(): ?User { return $this->auth->currentUser(); }

    protected function requireAuth(): User
    {
        $u = $this->auth->currentUser();
        if (!$u) throw new AuthenticationException('Non authentifié.');
        return $u;
    }

    protected function requireRole(Role $role): User
    {
        $u = $this->requireAuth();
        if (!$u->hasRole($role)) throw new AuthorizationException('Rôle insuffisant.');
        return $u;
    }

    protected function verifyCsrf(Request $req): void
    {
        if (!csrf_check($req->post('_csrf'))) {
            throw new AuthorizationException('Jeton CSRF invalide.');
        }
    }
}
