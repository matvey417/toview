<?php

namespace App\Service\Mandarin\Transaction;

use App\Entity\Commission;
use App\Entity\IncomingTransfer;

/**
 * Сервис для работы с комиссиями
 */
class CommissionService
{
    /**
     * @param CalcCommissionService $calcCommissionService
     */
    public function __construct(protected CalcCommissionService $calcCommissionService)
    {
    }

    /**
     * Создать комиссию по IncomingTransfer
     * Возвращает Commission
     *
     * @param IncomingTransfer $incomingTransfer
     *
     * @return Commission
     */
    public function createByIncoming(IncomingTransfer $incomingTransfer): Commission
    {
        $commission = new Commission();
        $commission->setProject($incomingTransfer->getProject());
        $commission->setUser($incomingTransfer->getProject()->getOwner());
        $commission->setSumFull($incomingTransfer->getSum());
        $sumFact = $this->calcCommissionService->calculateSumForPay($incomingTransfer->getSum(), $incomingTransfer->getProject()->getCurrentPercentRate());
        $commission->setSumFact($sumFact);
        $sumCommission = $this->calcCommissionService->calculateCommission($incomingTransfer->getSum(), $incomingTransfer->getProject()->getCurrentPercentRate());
        $commission->setSumCommission($sumCommission);
        $commission->setRate($incomingTransfer->getProject()->getCurrentPercentRate());
        $commission->setIncomingTransfer($incomingTransfer);

        return $commission;
    }
}
