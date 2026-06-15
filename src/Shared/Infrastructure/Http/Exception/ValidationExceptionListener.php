<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exception;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ValidationException) {
            $errors = [];
            foreach ($exception->violations as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            $event->setResponse(new JsonResponse(['errors' => $errors], 422));
        }
    }
}
