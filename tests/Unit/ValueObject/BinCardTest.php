<?php

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\BinCard;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class BinCardTest extends TestCase
{
    /**
     * Тест успешного создания объектов BinCard
     *
     * @covers BinCard::__construct
     * @return void
     * @throws UnexpectedValueException
     * @throws InvalidArgumentException
     */
    public function testSuccessfullyCreation(): void
    {
        $bin = new BinCard('456412');
        $secondBin = new BinCard(785243);
        $this->assertEquals('456412', $bin->getValue());
        $this->assertEquals('785243', $secondBin->getValue());
    }

    /**
     * Тест некорректного создания объекта BinCard пробел в номере
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreationSpaceCharacter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BinCard('444 44');
    }

    /**
     * Тест некорректного создания объекта BinCard, много цифр
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreationMoreNumbers(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BinCard('244483452');

    }

    /**
     * Тест некорректного создания объекта BinCard буква в номере
     *
     * @covers PanCard::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreationLetterInPan(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new BinCard('244d22');
    }

    /**
     * Тест сравнения объектов
     *
     * @covers BinCard::isEqualTo()
     * @return void
     * @throws InvalidArgumentException
     */
    public function testIsEqualTo(): void
    {
        $firstBin = new BinCard('456412');
        $secondBin = new BinCard(785243);

        $this->assertTrue($firstBin->isEqualTo(clone $firstBin));
        $this->assertFalse($firstBin->isEqualTo($secondBin));
    }
}
