<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\CardNumber;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CardNumberTest extends TestCase
{
    /**
     * Тест успешного создания объектов CardNumber
     *
     * @covers CardNumber::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testSuccessfullyCreation(): void
    {
        $cardNumber = new CardNumber('123312XXXXXXXX1234');
        $secondCardNumber = new CardNumber('123312XXXXXX1234');
        $this->assertEquals('123312XXXXXXXX1234', $cardNumber->getValue());
        $this->assertEquals('123312XXXXXX1234', $secondCardNumber->getValue());
        $this->assertEquals('123312', $secondCardNumber->getBin()->getValue());
        $this->assertEquals('1234', $secondCardNumber->getPan()->getValue());
    }

    /**
     * Тест некорректного создания объекта CardNumber "-" в номере
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CardNumber('1233-12XX-XXXX-1234');
    }

    /**
     * Тест некорректного создания объекта CardNumber, много цифр
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreationMoreNumbers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CardNumber('123312XXXXX11234');
    }

    /**
     * Тест некорректного создания объекта CardNumber иные буквы
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreationLetterInNumber(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CardNumber('12312SXXXXXXX1234');
    }

    /**
     * Тест сравнения объектов
     *
     * @covers CardNumber::isEqualTo()
     * @return void
     */
    public function testIsEqualTo(): void
    {
        $firstBin = new CardNumber('123312XXXXXXXX1234');
        $secondBin = new CardNumber('123312XXXXXX1235');

        $this->assertTrue($firstBin->isEqualTo(clone $firstBin));
        $this->assertFalse($firstBin->isEqualTo($secondBin));
    }
}
