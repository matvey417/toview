<?php

namespace App\Component\Mandarin;

use App\Component\RandomCode;
use App\Enum\PaymentActionTypeEnum;
use App\ValueObject\Money;
use JsonSerializable;

/**
 * Компонента для преобразования данных для оплаты по привязанной карте пользователя
 */
class MandarinTransactionSubscriptionPayComponent implements JsonSerializable, MandarinTransactionComponentInterface
{
    /** @var string $actionType Действие (платеж pay или выплата payout), актуально только для транзакций */
    private string $actionType;

    /** @var string $orderId Уникальный номер платежки */
    private string $orderId;

    /** @var string $cardSystemId поле карты systemId */
    private string $cardSystemId;

    /** @var Money $price Сумма платежа */
    private Money $price;

    /** @var bool $allowInteractive - если "true" то разрешено проведение рекуррентного платежа, не спрашивая дополнительной информации у плательщика */
    private bool $allowInteractive;

    /**
     * @param RandomCode $randomCode
     */
    public function __construct(protected RandomCode $randomCode)
    {
        $this->allowInteractive = true;
        $this->actionType = PaymentActionTypeEnum::Pay->value;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $this->orderId = $this->randomCode->getOrderId();

        $list = [];
        $list['payment']['action'] = $this->getActionType();
        $list['payment']['orderId'] = $this->getOrderId();
        $list['payment']['price'] = $this->getPrice()->getRoundRubForBD();
        $list['target']['card'] = $this->getCardSystemId();
        $list['allowinteractive'] = $this->isAllowInteractive();

        return $list;
    }

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getCardSystemId(): string
    {
        return $this->cardSystemId;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @param Money $price
     */
    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    /**
     * @return bool
     */
    public function isAllowInteractive(): bool
    {
        return $this->allowInteractive;
    }

    /**
     * @param string $cardSystemId
     */
    public function setCardSystemId(string $cardSystemId): void
    {
        $this->cardSystemId = $cardSystemId;
    }
}
