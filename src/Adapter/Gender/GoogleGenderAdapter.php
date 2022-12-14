<?php

namespace App\Adapter\Gender;

use App\Enum\GenderEnum;

/**
 * Адаптер для гендера пользователя, переводит строковый тип получаемый от Google в стандартизированный тип
 */
class GoogleGenderAdapter implements GenderAdapterInterface
{
    /** @var GenderEnum gender */
    private GenderEnum $gender;

    /**
     * @param string|null $gender
     */
    public function __construct(?string $gender)
    {
        if (null !== $gender) {
            $gender = ucfirst($gender);
        }

        $this->gender = match ($gender) {
            null, "" => GenderEnum::Unknown,
            GenderEnum::Female->name => GenderEnum::Female,
            GenderEnum::Male->name => GenderEnum::Male,
            default => GenderEnum::Undefined,
        };
    }

    /**
     * @inheritDoc
     */
    public function get(): int
    {
        return $this->gender->value;
    }
}
