<?php

namespace App\Tests\Unit\ValueObject;

use App\Enum\GenderEnum;
use App\ValueObject\Gender;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class GenderTest extends TestCase
{
    /**
     * Тест успешного создания объектов Gender
     *
     * @covers Gender::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testSuccessCreation(): void
    {
        $genderEnums = GenderEnum::cases();

        foreach ($genderEnums as $genderEnum) {
            $gender = new Gender(gender: $genderEnum->value);
            $this->assertEquals($genderEnum->value, $gender->getValue());
        }
    }

    /**
     * Тест некорректного создания объекта Gender
     *
     * @covers Gender::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testDisabledCreation(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new Gender(gender: 145);
    }

    /**
     * Тест сравнения объектов
     *
     * @covers Gender::isEqualTo()
     * @return void
     */
    public function testIsEqualTo(): void
    {
        $genderUnknown = new Gender(gender: GenderEnum::Unknown->value);
        $genderMale = new Gender(gender: GenderEnum::Male->value);

        $this->assertTrue($genderUnknown->isEqualTo(clone $genderUnknown));
        $this->assertFalse($genderUnknown->isEqualTo($genderMale));
    }

    /**
     * Тест, что у объекта гендер Unknown
     *
     * @covers Gender::isUnknownGender()
     * @return void
     */
    public function testIsUnknownGender(): void
    {
        $genderUnknown = new Gender(gender: GenderEnum::Unknown->value);
        $this->assertTrue($genderUnknown->isUnknownGender());
    }

    /**
     * Тест, что у объекта гендер Male
     *
     * @covers Gender::isMale
     * @return void
     */
    public function testisMale(): void
    {
        $genderMale = new Gender(gender: GenderEnum::Male->value);
        $this->assertTrue($genderMale->isMale());
    }

    /**
     * Тест, что у объекта гендер Female
     *
     * @covers Gender::isFemale
     * @return void
     */
    public function testisFemale(): void
    {
        $genderFemale = new Gender(gender: GenderEnum::Female->value);
        $this->assertTrue($genderFemale->isFemale());
    }

    /**
     * Тест, что у объекта гендер Undefined
     *
     * @covers Gender::isUndefined
     * @return void
     */
    public function testisUndefined(): void
    {
        $genderUndefined = new Gender(gender: GenderEnum::Undefined->value);
        $this->assertTrue($genderUndefined->isUndefined());
    }
}
