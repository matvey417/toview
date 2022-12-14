<?php

namespace App\ValueObject;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Value Object bin card(первые 6 цифр номера карты)
 */
class BinCard
{
    /**
     * @param string|null $bin
     *
     * @throws InvalidArgumentException
     */
    public function __construct(protected ?string $bin)
    {
        if (null === $this->bin) {
            return;
        }

        if (6 !== strlen($this->bin) || !preg_match('/^\d{6}$/u', $this->bin)) {
            throw new InvalidArgumentException('Некорректный bin карты' . $this->bin);
        }
    }

    /**
     * @param BinCard $bin
     *
     * @return bool
     */
    #[Pure] public function isEqualTo(BinCard $bin): bool
    {
        return $this->getValue() === $bin->getValue();
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->bin;
    }
}
