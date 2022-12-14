<?php

namespace App\ValueObject;

use Doctrine\DBAL\Exception\InvalidArgumentException;
use JetBrains\PhpStorm\Pure;

/**
 * Value Object для номера карты, содержит VO для bin и pan
 */
class CardNumber
{
    /** @var BinCard bin */
    private BinCard $bin;
    /** @var PanCard pan */
    private PanCard $pan;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(protected ?string $cardNumber)
    {
        if (null === $cardNumber) {
            return;
        }

        if (!preg_match('/^\d{6}(X{6}|X{8}|X{9})\d{4}$/u', $cardNumber)) {
            throw new InvalidArgumentException('Некорректный номер карты' . $cardNumber);
        }

        $number = preg_replace('/\D/', '', $cardNumber);

        $this->bin = new BinCard(substr($number, 0, 6));
        $this->pan = new PanCard(substr($number, -4));
    }

    /**
     * @return BinCard
     */
    public function getBin(): BinCard
    {
        return $this->bin;
    }

    /**
     * @return PanCard
     */
    public function getPan(): PanCard
    {
        return $this->pan;
    }

    /**
     * @param CardNumber $number
     *
     * @return bool
     */
    #[Pure] public function isEqualTo(CardNumber $number): bool
    {
        return $this->getValue() === $number->getValue();
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->cardNumber;
    }
}
