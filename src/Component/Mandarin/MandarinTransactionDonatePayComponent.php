<?php

namespace App\Component\Mandarin;

use App\Component\RandomCode;
use App\Enum\PaymentActionTypeEnum;
use App\ValueObject\Money;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Компонента для преобразования данных для интерактивной оплаты(оплата с вводом данных карты)
 */
class MandarinTransactionDonatePayComponent implements JsonSerializable, MandarinTransactionComponentInterface, MandarinTransactionSinglePayComponentInterface
{
    /** @var string $actionType Действие (платеж pay или выплата payout), актуально только для транзакций */
    private string $actionType;

    /** @var string $orderId Уникальный номер платежки */
    private string $orderId;

    /** @var Money|null $price Сумма платежа */
    private ?Money $price;

    /** @var string|null $email */
    private ?string $email;

    /** @var string|null $urlReturn Страница на которую редиректить с сайта оплаты */
    private ?string $urlReturn;

    /**
     * @param RandomCode $randomCode
     */
    public function __construct(protected RandomCode $randomCode)
    {
        $this->actionType = PaymentActionTypeEnum::Pay->value;
        $this->setPrice(null);
        $this->setEmail(null);
        $this->setUrlReturn(null);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $this->checkParameters();

        $this->orderId = $this->randomCode->getOrderId();

        $list = [];
        $list['payment']['action'] = $this->getActionType();
        $list['payment']['orderId'] = $this->getOrderId();
        $list['payment']['price'] = $this->getPrice()->getRoundRubForBD();

        $list['customerInfo']['email'] = $this->getEmail();

        if (null !== $this->getUrlReturn()) {
            $list['urls']['return'] = $this->getUrlReturn();
        }

        return $list;
    }

    /**
     * Проверить, что обязательные параметры заполнены
     *
     * @return void
     * @todo либо убрать метод либо переделать
     */
    public function checkParameters(): void
    {
        if (null === $this->getPrice()) {
            throw new InvalidArgumentException('Не установлена цена для разового платежа ');
        }

        if (null === $this->getEmail()) {
            throw new InvalidArgumentException('Не установлен email для разового платежа');
        }
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
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getUrlReturn(): ?string
    {
        return $this->urlReturn;
    }

    /**
     * @param string|null $urlReturn
     */
    public function setUrlReturn(?string $urlReturn): void
    {
        $this->urlReturn = $urlReturn;
    }
}
