<?php

namespace App\ValueObject;

use App\Enum\CardBindingStatusEnum;
use Throwable;
use UnexpectedValueException;

/**
 * Статус привязки карты
 */
class CardBindingStatus
{
    /**
     * @param string|null $status
     */
    public function __construct(protected ?string $status)
    {
        try {
            CardBindingStatusEnum::from($this->status);
        } catch (Throwable $e) {
            throw new UnexpectedValueException('Невозможно создать объект CardBindingStatus со статусом: ' . $this->status);
        }
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return CardBindingStatusEnum::Success->value === $this->status;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return CardBindingStatusEnum::Failed->value === $this->status;
    }

    /**
     * @return bool
     */
    public function isAckFailed(): bool
    {
        return CardBindingStatusEnum::AckFailed->value === $this->status;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return CardBindingStatusEnum::Completed->value === $this->status;
    }

    /**
     * @param CardBindingStatus $type
     *
     * @return bool
     */
    public function isEqualTo(CardBindingStatus $type): bool
    {
        return $this->status === $type->getValue();
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->status;
    }
}
