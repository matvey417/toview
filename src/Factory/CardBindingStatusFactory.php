<?php

namespace App\Factory;

use App\Enum\CardBindingStatusEnum;
use App\ValueObject\CardBindingStatus;

class CardBindingStatusFactory
{
    /**
     * @return CardBindingStatus
     */
    public function makeCreated(): CardBindingStatus
    {
        return new CardBindingStatus(CardBindingStatusEnum::Created->value);
    }

    /**
     * @return CardBindingStatus
     */
    public function makeSuccess(): CardBindingStatus
    {
        return new CardBindingStatus(CardBindingStatusEnum::Success->value);
    }

    /**
     * @return CardBindingStatus
     */
    public function makeFailed(): CardBindingStatus
    {
        return new CardBindingStatus(CardBindingStatusEnum::Failed->value);
    }

    /**
     * @return CardBindingStatus
     */
    public function makeAckFailed(): CardBindingStatus
    {
        return new CardBindingStatus(CardBindingStatusEnum::AckFailed->value);
    }

    /**
     * @return CardBindingStatus
     */
    public function makeCompleted(): CardBindingStatus
    {
        return new CardBindingStatus(CardBindingStatusEnum::Completed->value);
    }
}
