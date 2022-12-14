<?php

namespace App\Service\Mandarin\Transaction;

use HttpResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Ответ от мандарина при транзакции по карте
 */
class MandarinTransactionResponse
{
    /** @var string $id */
    protected string $id;

    /**
     * @param ResponseInterface $response
     *
     * @throws HttpResponseException
     */
    public function __construct(ResponseInterface $response)
    {
        if (200 !== $response->getStatusCode()) {
            throw new HttpResponseException(
                'Пришел некорректный ответ mandarin при оплате подписки: ' . $response->getStatusCode() . '. Описание: ' . $response->getBody()->getContents(),
                400);
        }

        $stdClass = json_decode($response->getBody()->getContents());
        if (!isset($stdClass->id)) {
            throw new HttpResponseException(
                'Пришел ответ mandarin при оплате подписки, но отсутствует поле "id": ' . $response->getBody()->getContents(),
                400);
        }

        $this->id = $stdClass->id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
