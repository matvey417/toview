<?php

namespace App\Factory;

use App\Enum\PaymentStatusEnum;
use App\ValueObject\PaymentStatus;

class PaymentStatusFactory
{
    /**
     * @return PaymentStatus
     */
    public function makeCreated(): PaymentStatus
    {
        return new PaymentStatus(PaymentStatusEnum::Created->value);
    }

    /**
     * @return PaymentStatus
     */
    public function makeSuccess(): PaymentStatus
    {
        return new PaymentStatus(PaymentStatusEnum::Success->value);
    }

    /**
     * @return PaymentStatus
     */
    public function makeFailed(): PaymentStatus
    {
        return new PaymentStatus(PaymentStatusEnum::Failed->value);
    }

    /**
     * @return PaymentStatus
     */
    public function makeCompleted(): PaymentStatus
    {
        return new PaymentStatus(PaymentStatusEnum::Completed->value);
    }
}
