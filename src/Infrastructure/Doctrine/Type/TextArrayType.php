<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TextArrayType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TEXT[]';
    }

    public function getName(): string
    {
        return '_text';
    }

    /**
     * @param array<string> $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
    {
        if (!is_array($value) || $value === []) {
            return '{}';
        }

        $escaped = array_map(
            static fn (string $v): string => '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $v) . '"',
            $value,
        );

        return '{' . implode(',', $escaped) . '}';
    }

    /**
     * @return array<string>
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): array
    {
        if ($value === null || $value === '{}') {
            return [];
        }

        $inner = substr((string) $value, 1, -1);

        if ($inner === '') {
            return [];
        }

        preg_match_all('/"(?:[^"\\\\]|\\\\.)*"|[^,]+/', $inner, $matches);

        return array_map(
            static function (string $item): string {
                if (str_starts_with($item, '"') && str_ends_with($item, '"')) {
                    $item = substr($item, 1, -1);
                    $item = str_replace(['\\"', '\\\\'], ['"', '\\'], $item);
                }

                return $item;
            },
            $matches[0],
        );
    }
}
