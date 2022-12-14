<?php

namespace App\Tests\Unit\DBAL;

use App\DBAL\CardBindingStatusType;
use App\Enum\CardBindingStatusEnum;
use App\ValueObject\CardBindingStatus;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Types\Types;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CardBindingStatusTypeTest extends TestCase
{
    /**
     * @covers CardBindingStatusType::getName()
     * @return void
     */
    public function testGetName(): void
    {
        $this->assertEquals(Types::STRING, (new CardBindingStatusType())->getName());
    }

    /**
     * @covers CardBindingStatusType::getSQLDeclaration()
     * @return void
     */
    public function testGetSQLDeclaration(): void
    {
        $must = new CardBindingStatusType();
        $sql = $must->getSQLDeclaration([], new MySqlPlatform());
        $this->assertEquals('VARCHAR(255)', $sql);
    }

    /**
     * @covers CardBindingStatusType::convertToPHPValue()
     * @return void
     */
    public function testConverter(): void
    {
        $cardBindingStatusEnums = CardBindingStatusEnum::cases();
        foreach ($cardBindingStatusEnums as $enum) {
            $allCardBindingStatuses[] = new CardBindingStatus(status: $enum->value);
        }

        $must = new CardBindingStatusType();

        foreach ($allCardBindingStatuses as $cardBindingStatus) {
            $val = $must->convertToDatabaseValue($cardBindingStatus, new MySqlPlatform());
            $this->assertEquals($cardBindingStatus->getValue(), $val);
            $cardBindingStatusFromDB = $must->convertToPHPValue($cardBindingStatus->getValue(), new MySqlPlatform());
            $this->assertTrue($cardBindingStatusFromDB instanceof CardBindingStatus);
            $cardBindingStatusFromDB->isEqualTo(new CardBindingStatus($cardBindingStatus->getValue()));
        }
    }

    /**
     * @covers CardBindingStatusType::convertToDatabaseValue()
     * @return void
     */
    public function testFailedConvertToDatabaseValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $must = new CardBindingStatusType();
        $must->convertToDatabaseValue('инвалид валуес', new MySqlPlatform());
    }

    /**
     * @covers CardBindingStatusType::convertToDatabaseValue()
     * @return void
     */
    public function testSuccessConvertToDatabaseValue(): void
    {
        $must = new CardBindingStatusType();

        $statusCreated = new CardBindingStatus(CardBindingStatusEnum::Created->value);
        $val = $must->convertToDatabaseValue($statusCreated, new MySqlPlatform());
        $this->assertEquals($val, $statusCreated->getValue(), 'При преобразовании CardBindingStatus в формат БД не совпали коды значений');

        $nullVal = $must->convertToDatabaseValue(null, new MySqlPlatform());
        $this->assertNull($nullVal);

        $stringValCreated = $must->convertToDatabaseValue(new CardBindingStatus(CardBindingStatusEnum::Created->value), new MySqlPlatform());
        $this->assertEquals(CardBindingStatusEnum::Created->value, $stringValCreated);
    }
}
