<?php

namespace App\Service\Mandarin\Transaction;

use App\Component\Mandarin\MandarinTransactionComponentInterface;
use App\Service\Mandarin\BaseMandarinConfig;
use App\Service\Mandarin\InterfaceMandarinRequest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Конфиг мандарина для транзакций оплаты
 */
class MandarinTransactionConfig extends BaseMandarinConfig implements InterfaceMandarinRequest
{
    /**
     * @param MandarinTransactionComponentInterface $component
     */
    public function __construct(protected MandarinTransactionComponentInterface $component)
    {
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->getUrlTransaction();
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
       return Request::METHOD_POST;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->component->jsonSerialize();
    }
}
