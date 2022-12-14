<?php

namespace App\Tests\Unit\Adapter\Gender;

use App\Adapter\Gender\VkGenderAdapter;
use App\Enum\GenderEnum;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Тесты адаптера для пола пользователя Vkontakte
 */
class VkGenderAdapterTest extends TestCase
{
    /**
     * Проверка на некорректный тип аргумента при создании адаптера
     *
     * @covers VkGenderAdapter::__construct
     *
     * @return void
     */
    public function testTypeError(): void
    {
        $this->expectException(TypeError::class);
        new VkGenderAdapter('helicopter');
    }

    /**
     * Тест на корректность преобразование возвращаемых данных
     *
     * @covers GoogleGenderAdapter::get
     *
     * @return void
     */
    public function testGetGender(): void
    {
        $male = (new VkGenderAdapter('2'))->get();
        $female = (new VkGenderAdapter(1))->get();
        $unknown = (new VkGenderAdapter(0))->get();
        $undefined = (new VkGenderAdapter(15))->get();

        $this->assertEquals($male, GenderEnum::Male->value);
        $this->assertEquals($female, GenderEnum::Female->value);
        $this->assertEquals($unknown, GenderEnum::Unknown->value);
        $this->assertEquals($undefined, GenderEnum::Undefined->value);
    }
}
