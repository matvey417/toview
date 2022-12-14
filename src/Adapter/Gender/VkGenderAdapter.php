<?php

namespace App\Adapter\Gender;

use App\Enum\GenderEnum;

/**
 * Адаптер для пола пользователя Вконтакте, преобразует значения пола пользователя вк в стандартизированный формат
 */
class VkGenderAdapter implements GenderAdapterInterface
{
    /** @var GenderEnum gender */
    private GenderEnum $gender;

    /**
     * @param int $gender
     */
    public function __construct(int $gender)
    {
        $this->gender = match ($gender) {
            1 => GenderEnum::Female,
            2 => GenderEnum::Male,
            0 => GenderEnum::Unknown,
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
