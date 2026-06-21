<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Enum\AuthorizationCode;

enum Scope: string
{
    case EMAIL = 'email';
    case PROFILE = 'profile';
}
