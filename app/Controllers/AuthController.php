<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\{Request, Response, Session};
use App\Exceptions\{ValidationException, AuthenticationException};

final class AuthController extends AbstractController
{
    public function showRegister(Request $req): Response
    {
        return $this->render('auth/register', ['title' => 'Inscription'], 'layouts/auth');
    }

    public function register(Request $req): Response
    {
        try {
            $this->verifyCsrf($req);
            $this->auth->register($req->all());
            return $this->redirect('reports', 'Bienvenue sur CityAlert !');
        } catch (ValidationException $e) {
            $_SESSION['_old'] = $req->all();
            return $this->render('auth/register', ['title' => 'Inscription', 'errors' => $e->errors()], 'layouts/auth');
        }
    }

    public function showLogin(Request $req): Response
    {
        return $this->render('auth/login', ['title' => 'Connexion'], 'layouts/auth');
    }

    public function login(Request $req): Response
    {
        try {
            $this->verifyCsrf($req);
            $this->auth->login((string) $req->post('email'), (string) $req->post('password'));
            return $this->redirect('reports', 'Connexion réussie.');
        } catch (AuthenticationException $e) {
            return $this->render('auth/login', ['title' => 'Connexion', 'error' => $e->getMessage()], 'layouts/auth');
        }
    }

    public function logout(Request $req): Response
    {
        $this->auth->logout();
        return $this->redirect('login', 'Vous êtes déconnecté.');
    }
}
