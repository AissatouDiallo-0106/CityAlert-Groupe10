<?php
declare(strict_types=1);
namespace App\Services;

use App\Interfaces\NotifiableInterface;
use App\Models\Entities\User;

/**
 * Notifie un utilisateur (ici par e-mail simulé / journalisé).
 * Démontre l'implémentation d'une interface.
 */
final class NotificationService implements NotifiableInterface
{
    public function notify(User $user, string $subject, string $message): void
    {
        // En production : mail($user->getEmail(), $subject, $message);
        $line = sprintf("[%s] À %s <%s> — %s : %s\n",
            date('Y-m-d H:i:s'), $user->getName(), $user->getEmail(), $subject, $message);
        @file_put_contents(dirname(__DIR__, 2) . '/storage/notifications.log', $line, FILE_APPEND);
    }
}
