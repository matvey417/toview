<?php

namespace App\Enum;

enum PaymentActionTypeEnum: string
{
    case Pay = 'pay';
    case Payout = 'payout';
}
