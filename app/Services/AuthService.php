<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Session;
use App\Enums\Role;
use App\Models\Entities\User;
use App\Repositories\UserRepository;
use App\Exceptions\{AuthenticationException, ValidationException};

final class AuthService
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository();
    }

    public function currentUser(): ?User
    {
        $id = Session::get('user_id');
        return $id ? $this->users->find((int) $id) : null;
    }

    public function register(array $data): User
    {
        $errors = [];
        $name  = trim((string) ($data['name'] ?? ''));
        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $pass  = (string) ($data['password'] ?? '');

        if (mb_strlen($name) < 2) $errors['name'] = 'Nom trop court.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'E-mail invalide.';
        if (mb_strlen($pass) < 6) $errors['password'] = 'Mot de passe : 6 caractères minimum.';
        if (!$errors && $this->users->findByEmail($email)) $errors['email'] = 'E-mail déjà utilisé.';
        if ($errors) throw new ValidationException($errors);

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($pass);
        $user->setRole(Role::CITIZEN);
        $this->users->save($user);

        Session::set('user_id', $user->getId());
        return $user;
    }

    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !$user->verifyPassword($password)) {
            throw new AuthenticationException('Identifiants incorrects.');
        }
        Session::set('user_id', $user->getId());
        return $user;
    }

    public function logout(): void
    {
        Session::forget('user_id');
    }
}
