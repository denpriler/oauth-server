<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\RequestData;

use App\Shared\Infrastructure\Http\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RequestDataResolver implements ValueResolverInterface
{
    public function __construct(
        private ValidatorInterface $validator,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if (!$type || !is_subclass_of($type, RequestDataInterface::class)) {
            return [];
        }

        $dto = $type::fromRequest($request);
        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return [$dto];
    }
}
