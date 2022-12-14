<?php

namespace App\Tests\Unit\DBAL;

use App\DBAL\BinCardType;
use App\ValueObject\BinCard;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class BinCardTypeTest extends TestCase
{
    /**
     * @covers \App\DBAL\BinCardType::getName()
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals(Types::STRING, (new BinCardType())->getName());
    }

    /**
     * @covers \App\DBAL\BinCardType::getSQLDeclaration()
     * @return void
     */
    public function testGetSQLDeclaration()
    {
        $must = new BinCardType();
        $sql = $must->getSQLDeclaration([], new MySqlPlatform());
        $this->assertEquals('VARCHAR(255)', $sql);
    }

    /**
     * @covers \App\DBAL\BinCardType::convertToDatabaseValue()
     * @return void
     */
    public function testFailConvertToDatabaseValue()
    {
        $this->expectException(UnexpectedValueException::class);
        $must = new BinCardType();
        $must->convertToDatabaseValue(100500, new MySqlPlatform());
    }

    /**
     * @covers \App\DBAL\BinCardType::convertToPHPValue()
     * @return void
     * @throws InvalidArgumentException
     */
    public function testConverter()
    {
        $must = new BinCardType();
        $bin = new BinCard('122234');
        $val = $must->convertToDatabaseValue($bin, new MySqlPlatform());
        $this->assertEquals($bin->getValue(), $val);

        $binFromDB = $must->convertToPHPValue($bin->getValue(), new MySqlPlatform());
        $this->assertTrue($binFromDB instanceof BinCard);
        $binFromDB->isEqualTo(new BinCard($bin->getValue()));
    }

    /**
     * @covers \App\DBAL\BinCardType::convertToDatabaseValue()
     * @return void
     */
    public function testSuccessConvertToDatabaseValue()
    {
        $must = new BinCardType();

        $bin = new BinCard('522554');
        $val = $must->convertToDatabaseValue($bin, new MySqlPlatform());
        $this->assertEquals($val, $bin->getValue(), 'При преобразовании Bin в формат БД не совпали коды значений');

        $nullVal = $must->convertToDatabaseValue(null, new MySqlPlatform());
        $this->assertNull($nullVal);
    }
}
