<?php

namespace App\Service\Mandarin\Transaction;

use App\Entity\Merchant;
use App\Factory\MoneyFactory;
use App\Service\Mandarin\MerchantComponent;
use App\ValueObject\CardNumber;
use App\ValueObject\MandarinObjectType;
use App\ValueObject\MandarinResponseStatus;
use App\ValueObject\Money;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use http\Exception\UnexpectedValueException;

/**
 * CallBack Ответ на транзакцию платежа от мандарина
 */
class MandarinPayCallBackResponse
{
    /** @var string merchantId */
    private string $merchantId;

    /** @var string orderId */
    private string $orderId;

    /** @var string orderActualTill */
    private string $orderActualTill;

    /** @var string email */
    private string $email;

    /** @var Money price */
    protected Money $price;

    /** @var string action */
    private string $action;

    /** @var string customer_fullName */
    private string $customerFullName;

    /** @var string customer_phone */
    private string $customerPhone;

    /** @var string customer_email */
    private string $customerEmail;

    /** @var string transaction */
    private string $transaction;

    /** @var MandarinObjectType object_type */
    private MandarinObjectType $objectType;

    /** @var string payment_system */
    private string $paymentSystem;

    /** @var MandarinResponseStatus status */
    private MandarinResponseStatus $status;

    /** @var false|string dataJson */
    private string|false $dataJson;

    /** @var CardNumber card_number */
    private CardNumber $cardNumber;

    /** @var string cb_customer_creditcard_number */
    private string $cbCustomerCreditcardNumber;

    /** @var Merchant|null merchant */
    private ?Merchant $merchant;

    /** @var string sign */
    private string $sign;

    /**
     * @param $response
     * @param MerchantComponent $merchantComponent
     * @param MoneyFactory $moneyFactory
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        $response,
        protected MerchantComponent $merchantComponent,
        protected MoneyFactory $moneyFactory
    ) {
        if (!isset($response['merchantId'])) {
            throw new UnexpectedValueException('Не пришел параметр merchantId. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['orderId'])) {
            throw new UnexpectedValueException('Не пришел параметр orderId. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['email'])) {
            throw new UnexpectedValueException('Не пришел параметр email. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['price'])) {
            throw new UnexpectedValueException('Не пришел параметр price. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['action'])) {
            throw new UnexpectedValueException('Не пришел параметр action. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['customer_fullName'])) {
            throw new UnexpectedValueException('Не пришел параметр customer_fullName. Ответ: ' . print_r($response, true), 400);
        }
        /** todo телефон то необязательно будет, проверить если не передадим им номер, как будет выглядеть ответ */
        if (!isset($response['customer_phone'])) {
            throw new UnexpectedValueException('Не пришел параметр customer_phone. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['customer_email'])) {
            throw new UnexpectedValueException('Не пришел параметр customer_email. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['transaction'])) {
            throw new UnexpectedValueException('Не пришел параметр transaction. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['object_type'])) {
            throw new UnexpectedValueException('Не пришел параметр object_type. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['status'])) {
            throw new UnexpectedValueException('Не пришел параметр status. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['payment_system'])) {
            throw new UnexpectedValueException('Не пришел параметр payment_system. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['card_number'])) {
            throw new UnexpectedValueException('Не пришел параметр card_number. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['cb_customer_creditcard_number'])) {
            throw new UnexpectedValueException('Не пришел параметр cb_customer_creditcard_number. Ответ: ' . print_r($response, true), 400);
        }

        if (!isset($response['sign'])) {
            throw new UnexpectedValueException('Не пришел параметр sign. Ответ: ' . print_r($response, true), 400);
        }

        $this->dataJson = json_encode($response);

        $this->merchantId = $response['merchantId'];
        $this->orderId = $response['orderId'];
        $this->email = $response['email'];
        $this->orderActualTill = $response['orderActualTill'];
        $this->price = $moneyFactory->fromRub($response['price']);
        $this->action = $response['action'];
        $this->customerFullName = $response['customer_fullName'];
        $this->customerPhone = $response['customer_phone'];
        $this->customerEmail = $response['customer_email'];
        $this->transaction = $response['transaction'];
        $this->objectType = new MandarinObjectType($response['object_type']);
        $this->status = new MandarinResponseStatus($response['status']);
        $this->paymentSystem = $response['payment_system'];
        $this->cardNumber = new CardNumber($response['card_number']);
        $this->cbCustomerCreditcardNumber = $response['cb_customer_creditcard_number'];
        $this->sign = $response['sign'];

        $this->merchant = $merchantComponent->getMerchant();
        $this->checkSign();
    }

    /**
     * Дополнительная проверка хэшей callback'а
     *
     * @return bool
     */
    protected function checkSign(): bool
    {
        $array = json_decode($this->dataJson);
        $array2 = [];
        foreach ($array as $key => $value) {
            if ($key !== 'sign') {
                $array2[$key] = $value;
            }
        }
        ksort($array2);

        $str = implode('-', $array2);
        $merchPass = $this->merchant->getPassword();

        $str = $str . '-' . $merchPass;

        return $this->sign === hash("sha256", $str);
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->status->isSuccess();
    }


    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
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
    public function getOrderActualTill(): string
    {
        return $this->orderActualTill;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return Money
     */
    public function getPrice(): Money
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getCustomerFullName(): string
    {
        return $this->customerFullName;
    }

    /**
     * @return string
     */
    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * @return string
     */
    public function getTransaction(): string
    {
        return $this->transaction;
    }

    /**
     * @return MandarinObjectType
     */
    public function getObjectType(): MandarinObjectType
    {
        return $this->objectType;
    }

    /**
     * @return string
     */
    public function getPaymentSystem(): string
    {
        return $this->paymentSystem;
    }

    /**
     * @return MandarinResponseStatus
     */
    public function getStatus(): MandarinResponseStatus
    {
        return $this->status;
    }

    /**
     * @return false|string
     */
    public function getDataJson(): bool|string
    {
        return $this->dataJson;
    }

    /**
     * @return CardNumber
     */
    public function getCardNumber(): CardNumber
    {
        return $this->cardNumber;
    }

    /**
     * @return string
     */
    public function getCbCustomerCreditcardNumber(): string
    {
        return $this->cbCustomerCreditcardNumber;
    }

    /**
     * @return Merchant|null
     */
    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }

    /**
     * @return string
     */
    public function getSign(): string
    {
        return $this->sign;
    }
}
