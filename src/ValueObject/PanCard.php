<?php

namespace App\ValueObject;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Value Object для pan card(последние 4 цифры номера карты)
 */
class PanCard
{
    /**
     * @param string|null $pan
     *
     * @throws InvalidArgumentException
     */
    public function __construct(protected ?string $pan)
    {
        if (null === $this->pan) {
            return;
        }

        if (4 !== strlen($this->pan) || !preg_match('/^\d{4}$/u', $this->pan)) {
            throw new InvalidArgumentException('Некорректный pan карты' . $this->pan);
        }
    }

    /**
     * @param PanCard $pan
     *
     * @return bool
     */
    #[Pure] public function isEqualTo(PanCard $pan): bool
    {
        return $this->getValue() === $pan->getValue();
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->pan;
    }
}
