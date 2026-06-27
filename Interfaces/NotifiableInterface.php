<?php
declare(strict_types=1);
namespace App\Interfaces;

use App\Models\Entities\User;

/**
 * Contrat pour les services capables de notifier un utilisateur.
 */
interface NotifiableInterface
{
    public function notify(User $user, string $subject, string $message): void;
}
