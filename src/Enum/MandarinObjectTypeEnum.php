<?php

namespace App\Enum;

/**
 * Тип ответа от Мандарина
 */
enum MandarinObjectTypeEnum: string
{
    case CardBinding = 'card_binding';
    case Transaction = 'transaction';
}
