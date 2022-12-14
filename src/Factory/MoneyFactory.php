<?php

namespace App\Factory;

use App\ValueObject\Money;

/**
 * Класс для конструирования объекта Money
 */
class MoneyFactory
{
    /**
     * Возвращает VO Money из суммы в рублях
     *
     * @param float $rub - Сумма в рублях
     *
     * @return Money
     */
    public function fromRub(float $rub): Money
    {
        return new Money($rub * 100);
    }

    /**
     * Возвращает VO Money из суммы в копейках
     *
     * @param int $cent - Сумма в копейках
     *
     * @return Money
     */
    public function fromCent(int $cent): Money
    {
        return new Money($cent);
    }
}
