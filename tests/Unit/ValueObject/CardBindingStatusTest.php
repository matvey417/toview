<?php

namespace App\Tests\Unit\ValueObject;

use App\Enum\CardBindingStatusEnum;
use App\ValueObject\CardBindingStatus;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class CardBindingStatusTest extends TestCase
{
    /**
     * Тест успешного создания объектов CardBindingStatus
     *
     * @covers CardBindingStatus::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testSuccessCreation(): void
    {
        $enums = CardBindingStatusEnum::cases();

        foreach ($enums as $enum) {
            $status = new CardBindingStatus(status: $enum->value);
            $this->assertEquals($enum->value, $status->getValue());
        }
    }

    /**
     * Тест некорректного создания объекта CardBindingStatus
     *
     * @covers CardBindingStatus::__construct
     * @return void
     * @throws UnexpectedValueException
     */
    public function testFailureCreation(): void
    {
        $this->expectException(UnexpectedValueException::class);
        new CardBindingStatus(status: 'Отобрал гном');
    }

    /**
     * Тест, что статус "Success"
     *
     * @covers CardBindingStatus::isSuccess
     * @return void
     * @throws UnexpectedValueException
     */
    public function testIsSuccess(): void
    {
        $status = new CardBindingStatus(CardBindingStatusEnum::Success->value);
        $this->assertTrue($status->isSuccess());
    }

    /**
     * Тест сравнения объектов
     *
     * @covers CardBindingStatus::isEqualTo()
     * @return void
     */
    public function testIsEqualTo(): void
    {
        $statusFailed = new CardBindingStatus(status: CardBindingStatusEnum::Failed->value);
        $statusCreated = new CardBindingStatus(status: CardBindingStatusEnum::Created->value);

        $this->assertTrue($statusFailed->isEqualTo(clone $statusFailed));
        $this->assertFalse($statusCreated->isEqualTo($statusFailed));
    }
}
