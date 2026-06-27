<?php
declare(strict_types=1);
namespace App\Exceptions;

/** Données invalides (formulaire). Porte la liste des erreurs. */
final class ValidationException extends AppException
{
    /** @param array<string,string> $errors */
    public function __construct(private array $errors, string $message = 'Données invalides')
    {
        parent::__construct($message);
    }
    /** @return array<string,string> */
    public function errors(): array { return $this->errors; }