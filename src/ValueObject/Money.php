<?php

namespace App\ValueObject;


/**
 * Value Object для Money, значение в классе храниться в копейках, в базу сохраняем рубли
 */
class Money
{
    /**
     * @param int $amount - Сумма в копейках
     */
    public function __construct(public int $amount)
    {
    }

    /**
     * @param Money $money
     *
     * @return bool
     */
    public function isEqualTo(Money $money): bool
    {
        return $this->amount === $money->getCent();
    }

    /**
     * Возвращает сумму в копейках
     *
     * @return int
     */
    public function getCent(): int
    {
        return $this->amount;
    }

    /**
     * Возвращает сумму в рублях(сумма не округляется)
     *
     * @return float
     */
    public function getRub(): float
    {
        $amount = $this->amount / 100;
        if (0 === ($this->amount % 100)) {
            $amount = (int)$amount;
        }

        return $amount;
    }

    /**
     * Возвращает округленную сумму в рублях(без копеек)
     * В базе хранятся деньги в рублях, через этот метод
     * округление идет в большую сторону с 0.50
     *
     * @return int
     */
    public function getRoundRubForBD(): int
    {
        $amount = $this->amount / 100;

        return round($amount);
    }

    /**
     * Умножает на $value (умножатор)
     *
     * @param float $value - значение, на которое необходимо умножить(умножаться будет на рубли)
     *
     * @return Money
     */
    public function multiply(float $value): self
    {
        return new self($this->getCent() * $value);
    }

    /**
     * Вычитает $value (минусатор)
     *
     * @param Money $value - значение, которое необходимо вычесть
     *
     * @return Money
     */
    public function diff(Money $value): self
    {
        return new self($this->getCent() - $value->getCent());
    }

    /**
     * Суммирование $value (прибавлятор)
     *
     * @param Money $value - значение, которое необходимо прибавить
     *
     * @return Money
     */
    public function add(Money $value): Money
    {
        return new self($this->getCent() + $value->getCent());
    }
}
