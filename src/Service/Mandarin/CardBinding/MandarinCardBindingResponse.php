<?php

namespace App\Service\Mandarin\CardBinding;

use App\ValueObject\CardNumber;
use App\ValueObject\MandarinResponseStatus;
use Doctrine\DBAL\Exception\InvalidArgumentException;
use Google\Service\Resource;
use HttpResponseException;

/**
 * Ответ от мандарина по привязке карты
 */
class MandarinCardBindingResponse
{
    /** @var string jsonData */
    private string $jsonData;

    /** @var MandarinResponseStatus|null code */
    private ?MandarinResponseStatus $code;

    /** @var string|null bindingId */
    private ?string $bindingId;

    /** @var CardNumber|null cardNumber */
    private ?CardNumber $cardNumber;

    /** @var string|null errorCode */
    private ?string $errorCode;

    /**
     * @throws HttpResponseException
     * @throws InvalidArgumentException
     */
    public function __construct(string|resource $response)
    {
        $this->jsonData = $response;

        $data = json_decode($response);
        $this->code = isset($data->code) ? new MandarinResponseStatus($data->code) : null;
        $this->errorCode = $data->errorCode ?? null;
        $this->bindingId = $data->binding->id ?? null;
        $this->cardNumber = new CardNumber($data->cardInfo->maskedCardNumber ?? null);

        if (null === $this->getCode() && null === $this->getErrorCode()) {
            throw new HttpResponseException(
                'Пришел некорректный ответ от Mandarin при попытке привязки карты: ' . $data, 400);
        }
    }

    /**
     * @return MandarinResponseStatus|null
     */
    protected function getCode(): ?MandarinResponseStatus
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    protected function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        if (null === $this->getCode()) {
            return false;
        }

        return $this->getCode()->isSuccess();
    }

    /**
     * @return bool
     */
    public function isError(): bool
    {
        return null !== $this->getErrorCode();
    }

    /**
     * @return string
     */
    public function getJsonData(): string
    {
        return $this->jsonData;
    }

    /**
     * @return string
     */
    public function getBindingId(): string
    {
        return $this->bindingId;
    }

    /**
     * @return CardNumber|null
     */
    public function getCardNumber(): ?CardNumber
    {
        return $this->cardNumber;
    }
}
