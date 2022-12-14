<?php

namespace App\Adapter\Gender;

/**
 * Интерфейс для адаптеров пола пользователей
 */
interface GenderAdapterInterface
{
    /**
     * Возвращает пол пользователя в стандартном значении (0-не указан; 1-мужской; 2-женский; 3-пол не определен)
     *
     * @return int
     */
    public function get(): int;
}
