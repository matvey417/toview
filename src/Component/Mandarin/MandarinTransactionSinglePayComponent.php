<?php

namespace App\Component\Mandarin;

use App\Component\RandomCode;
use App\DTO\Mandarin\MandarinUserDataDTO;
use App\Enum\PaymentActionTypeEnum;
use App\ValueObject\Money;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Компонента для преобразования данных для интерактивной оплаты(оплата с вводом данных карты)
 */
class MandarinTransactionSinglePayComponent implements JsonSerializable, MandarinTransactionComponentInterface, MandarinTransactionSinglePayComponentInterface
{
    /** @var string $actionType Действие (платеж pay или выплата payout), актуально только для транзакций */
    private string $actionType;

    /** @var string $orderId Уникальный номер платежки */
    private string $orderId;

    /** @var Money|null $price Сумма платежа */
    private ?Money $price;

    /** @var string|null $email */
    private ?string $email;

    /** @var string|null $phone */
    private ?string $phone;

    /**
     * @param MandarinUserDataDTO|null $dataDTO
     * @param RandomCode $randomCode
     */
    public function __construct(protected ?MandarinUserDataDTO $dataDTO, protected RandomCode $randomCode)
    {
        $this->actionType = PaymentActionTypeEnum::Pay->value;
        $this->setPrice(null);

        $this->email = $dataDTO->getEmail();
        $this->phone = $dataDTO->getPhone()->getValueWithPlus();

    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        if (null === $this->getPrice()) {
            throw new InvalidArgumentException('Не установлена цена для разового платежа ');
        }

        $this->orderId = $this->randomCode->getOrderId();

        $list = [];
        $list['payment']['action'] = $this->getActionType();
        $list['payment']['orderId'] = $this->getOrderId();
        $list['payment']['price'] = $this->getPrice()->getRoundRubForBD();

        $list['customerInfo']['email'] = $this->getEmail();
        $list['customerInfo']['phone'] = $this->getPhone();

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
     * @return Money|null
     */
    public function getPrice(): ?Money
    {
        return $this->price;
    }

    /**
     * @param Money|null $price
     */
    public function setPrice(?Money $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    protected function getPhone(): string
    {
        return $this->phone;
    }
}
