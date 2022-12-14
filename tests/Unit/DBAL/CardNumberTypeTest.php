<?php

namespace App\Tests\Unit\DBAL;

use App\DBAL\CardNumberType;
use App\ValueObject\CardNumber;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CardNumberTypeTest extends TestCase
{
    /**
     * @covers CardNumberType::getName()
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals(Types::STRING, (new CardNumberType())->getName());
    }

    /**
     * @covers CardNumberType::getSQLDeclaration()
     * @return void
     */
    public function testGetSQLDeclaration()
    {
        $must = new CardNumberType();
        $sql = $must->getSQLDeclaration([], new MySqlPlatform());
        $this->assertEquals('VARCHAR(255)', $sql);
    }

    /**
     * @covers \App\DBAL\CardNumberType::convertToDatabaseValue()
     * @return void
     */
    public function testFailConvertToDatabaseValue()
    {
        $this->expectException(UnexpectedValueException::class);
        $must = new CardNumberType();
        $must->convertToDatabaseValue('492950XXXXXX6878', new MySqlPlatform());
    }

    /**
     * @covers CardNumberType::convertToPHPValue()
     * @return void
     * @throws InvalidArgumentException
     */
    public function testConverter()
    {
        $must = new CardNumberType();
        $number = new CardNumber('492950XXXXXX6878');
        $val = $must->convertToDatabaseValue($number, new MySqlPlatform());
        $this->assertEquals($number->getValue(), $val);

        $numberFromDB = $must->convertToPHPValue($number->getValue(), new MySqlPlatform());
        $this->assertTrue($numberFromDB instanceof CardNumber);
        $numberFromDB->isEqualTo(new CardNumber($number->getValue()));
    }

    /**
     * @covers CardNumberType::convertToDatabaseValue()
     * @return void
     */
    public function testSuccessConvertToDatabaseValue()
    {
        $must = new CardNumberType();

        $number = new CardNumber('492950XXXXXX6878');
        $val = $must->convertToDatabaseValue($number, new MySqlPlatform());
        $this->assertEquals($val, $number->getValue(), 'При преобразовании number в формат БД не совпали коды значений');

        $nullVal = $must->convertToDatabaseValue(null, new MySqlPlatform());
        $this->assertNull($nullVal);
    }
}
