<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends RuntimeException
{
    public function __construct(
        public readonly ConstraintViolationListInterface $violations,
    ) {
        parent::__construct('Validation failed');
    }
}
