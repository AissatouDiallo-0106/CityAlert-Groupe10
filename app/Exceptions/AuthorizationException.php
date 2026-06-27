<?php
declare(strict_types=1);
namespace App\Exceptions;

/** Accès refusé (rôle insuffisant). */
final class AuthorizationException extends AppException {}