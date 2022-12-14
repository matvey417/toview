<?php

namespace App\Enum;

/**
 * Статус ответа от Мандарина, Только статус "success" однозначно указывает на успешность операции!
 */
enum MandarinResponseStatusEnum: string
{
    case Success = 'success';
    case Failed = 'failed';
    case PayoutOnly = 'payout-only';
}
