<?php

namespace App\Enum;

/**
 * Гендер пользователя
 */
enum GenderEnum: int
{
    case Unknown = 0;
    case Male = 1;
    case Female = 2;
    case Undefined = 9;
}
