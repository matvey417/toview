<?php

namespace App\Service\Mandarin\Transaction;

use App\Entity\Rate;
use App\ValueObject\Money;

/**
 * Сервис для расчета сумм с учетом комиссий
 */
class CalcCommissionService
{
    /**
     * Рассчитать сумму комиссии
     *
     * @param Money $sum - сумма платежа
     * @param Rate $rate - Процент комиссии
     *
     * @return Money
     */
    public function calculateCommission(Money $sum, Rate $rate): Money
    {
        return $sum->multiply($rate->getPercent() / 100);
    }

    /**
     * Рассчитать сумму необходимую для выплаты(сумма минус комиссия)
     *
     * @param Money $sum - Полная сумма платежа
     * @param Rate $rate - Процент комиссии
     *
     * @return Money
     */
    public function calculateSumForPay(Money $sum, Rate $rate): Money
    {
        $commissionSum = $this->calculateCommission($sum, $rate);

        return $sum->diff($commissionSum);
    }
}
