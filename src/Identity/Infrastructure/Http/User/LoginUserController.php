<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Http\User;

use App\Identity\Application\Command\LoginUser\LoginUserCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/identity', name: 'identity')]
final class LoginUserController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function __invoke(LoginUserCommand $command, MessageBusInterface $bus): JsonResponse
    {
        $envelope = $bus->dispatch($command);
        /** @var string $token */
        $token = $envelope->last(HandledStamp::class)->getResult();

        return new JsonResponse(
            data: [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ],
            status: Response::HTTP_OK
        );
    }
}
