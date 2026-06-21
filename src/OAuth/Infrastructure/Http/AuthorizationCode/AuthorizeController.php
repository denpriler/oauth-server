<?php

declare(strict_types=1);

namespace App\OAuth\Infrastructure\Http\AuthorizationCode;

use App\Identity\Domain\Entity\User;
use App\OAuth\Application\Command\Authorize\AuthorizeCommand;
use App\Shared\Infrastructure\Http\Exception\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AuthorizeController extends AbstractController
{
    /**
     * @throws ExceptionInterface
     */
    #[Route('/oauth/authorize', name: 'oauth_authorize', methods: ['GET'])]
    public function __invoke(Request $request, MessageBusInterface $bus, ValidatorInterface $validator): RedirectResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $command = AuthorizeCommand::fromRequest($request)->setUserId($user->getId());

        $violations = $validator->validate($command);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        /** @var array<string, string> $result */
        $result = $bus->dispatch($command)
            ->last(HandledStamp::class)
            ->getResult();

        return new RedirectResponse($result['redirect_url']);
    }
}
