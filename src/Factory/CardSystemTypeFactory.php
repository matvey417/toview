<?php

namespace App\Factory;

use App\Enum\CardSystemTypeEnum;
use App\ValueObject\CardSystemType;

class CardSystemTypeFactory
{
    /**
     * @return CardSystemType
     */
    public function makeMandarin(): CardSystemType
    {
        return new CardSystemType(CardSystemTypeEnum::Mandarin->value);
    }
}
