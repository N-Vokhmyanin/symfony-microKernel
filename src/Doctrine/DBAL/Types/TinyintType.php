<?php

namespace Core\Doctrine\DBAL\Types;

use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TinyintType extends Type
{
    private const TINYINT = 'tinyint';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $fieldDeclaration = array_merge([
            'length' => 1,
        ], $column);

        return sprintf("TINYINT(%d)",
            $fieldDeclaration['length']
        );
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): int
    {
        return (int) $value;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): int
    {
        return (int) $value;
    }

    public function getName(): string
    {
        return self::TINYINT;
    }

    public function getBindingType(): int
    {
        return ParameterType::INTEGER;
    }
}