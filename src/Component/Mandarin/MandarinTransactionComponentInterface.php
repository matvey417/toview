<?php

namespace App\Component\Mandarin;

/**
 * Интерфейс для транзакций с Мандарином
 */
interface MandarinTransactionComponentInterface
{
    public function jsonSerialize(): array;
}
