<?php

namespace App\DBAL;

use App\ValueObject\Gender;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use UnexpectedValueException;

/**
 * class GenderType
 */
class GenderType extends Type
{
    /**
     * @param array $column
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return Gender
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): Gender
    {
        return new Gender($value);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return Types::INTEGER;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value instanceof Gender) {
            return $value->getValue();
        } elseif (null === $value) {
            return null;
        }

        throw new UnexpectedValueException("Недопустимое значение типа при преобразовании пола пользователя в формат БД. Пришедшее значение: " . var_export($value, true));
    }
}
