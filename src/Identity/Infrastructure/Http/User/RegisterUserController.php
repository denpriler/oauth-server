<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http\User;

use App\Identity\Application\Command\RegisterUser\RegisterUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/identity', name: 'identity')]
class RegisterUserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws ExceptionInterface
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function __invoke(RegisterUserCommand $command): JsonResponse
    {
        $this->bus->dispatch($command);

        return new JsonResponse(status: Response::HTTP_CREATED);
    }
}
