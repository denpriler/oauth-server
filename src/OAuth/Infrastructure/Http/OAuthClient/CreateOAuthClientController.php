<?php

namespace App\OAuth\Infrastructure\Http\OAuthClient;

use App\Identity\Domain\Entity\User;
use App\OAuth\Application\Command\CreateOAuthClient\CreateOAuthClientCommand;
use App\Shared\Infrastructure\Http\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/oauth/client', name: 'oauth_client')]
final class CreateOAuthClientController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/', name: 'create', methods: ['POST'])]
    public function __invoke(Request $request, MessageBusInterface $bus, ValidatorInterface $validator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $command = CreateOAuthClientCommand::fromRequest($request)->setOwnerId($user->getId());

        $violations = $validator->validate($command);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        /** @var array<string, string> $result */
        $result = $bus->dispatch($command)
            ->last(HandledStamp::class)
            ->getResult();

        return new JsonResponse($result, Response::HTTP_CREATED);
    }
}
