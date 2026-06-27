<?php
declare(strict_types=1);
namespace App\Middlewares;

use App\Core\{Request, Response, Session};

final class AuthMiddleware
{
    public function handle(Request $req): ?Response
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Veuillez vous connecter.');
            return Response::redirect(url('login'));
        }
        return null;
    }
}