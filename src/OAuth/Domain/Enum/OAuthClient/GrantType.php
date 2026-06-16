<?php

declare(strict_types=1);

namespace App\OAuth\Domain\Enum\OAuthClient;

use App\Shared\Domain\Enum\BaseStringEnum;

enum GrantType: string
{
    use BaseStringEnum;

    case AUTHORIZATION_CODE = 'authorization_code';
    case CLIENT_CREDENTIALS = 'client_credentials';
    case REFRESH_TOKEN = 'refresh_token';
}
