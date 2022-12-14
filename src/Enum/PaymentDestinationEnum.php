<?php

namespace App\Enum;

enum PaymentDestinationEnum: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
}
