<?php

namespace App\Enum;

/**
 * Статусы платежей
 */
enum PaymentStatusEnum: string
{
    case Created = 'created';
    case Success = 'success';
    case Completed = 'completed';
    case Failed = 'failed';
}
