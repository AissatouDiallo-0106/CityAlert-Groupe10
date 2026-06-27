<?php
declare(strict_types=1);
namespace App\Models\Entities;

use App\Enums\Role;

final class User extends AbstractEntity
{
    private string $name = '';
    private string $email = '';
    private string $passwordHash = '';
    private Role $role = Role::CITIZEN;

    public function getName(): string { return $this->name; }
    public function setName(string $v): void { $this->name = trim($v); }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): void { $this->email = strtolower(trim($v)); }

    public function getPasswordHash(): string { return $this->passwordHash; }
    public function setPasswordHash(string $v): void { $this->passwordHash = $v; }
    public function setPassword(string $plain): void { $this->passwordHash = password_hash($plain, PASSWORD_DEFAULT); }
    public function verifyPassword(string $plain): bool { return password_verify($plain, $this->passwordHash); }

    public function getRole(): Role { return $this->role; }
    public function setRole(Role|string $v): void { $this->role = $v instanceof Role ? $v : Role::from($v); }
    public function hasRole(Role $r): bool { return $this->role === $r; }
    public function isAdmin(): bool { return $this->role === Role::ADMIN; }
    public function isAgent(): bool { return $this->role === Role::AGENT; }

    protected function hydrate(array $row): void
    {
        $this->id = isset($row['id']) ? (int) $row['id'] : null;
        $this->setName($row['name'] ?? '');
        $this->setEmail($row['email'] ?? '');
        $this->passwordHash = $row['password_hash'] ?? '';
        $this->setRole($row['role'] ?? Role::CITIZEN->value);
        $this->setCreatedAt($row['created_at'] ?? null);
        $this->setUpdatedAt($row['updated_at'] ?? null);
    }
}
