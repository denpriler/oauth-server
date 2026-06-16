<?php

namespace App\Shared\Domain\Enum;

trait BaseStringEnum
{
    /**
     * @return array<string>
     */
    public static function getStringValues(): array
    {
        return array_map(fn (self $gt) => $gt->value, self::cases());
    }
}
