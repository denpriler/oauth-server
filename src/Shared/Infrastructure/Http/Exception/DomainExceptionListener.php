<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Exception;

use DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class DomainExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HandlerFailedException) {
            $exception = $exception->getPrevious();
        }

        if ($exception instanceof DomainException) {
            $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], 409));
        }
    }
}
