<?php

namespace App\Shared\Infrastructure\Http\RequestData;

use Symfony\Component\HttpFoundation\Request;

interface RequestDataInterface
{
    public static function fromRequest(Request $request): self;
}
