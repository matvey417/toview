<?php

namespace App\DTO;

use App\ValueObject\Money;
use App\ValueObject\PaymentDestinationType;
use App\ValueObject\RecurrentStatus;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ДТО для создания платежки
 */
class PaymentDataDTO
{
    public function __construct(
        protected UserInterface          $user,
        protected ?int                   $projectId,
        protected string                 $email,
        protected string                 $orderId,
        protected Money                  $price,
        protected string                 $transactionId,
        protected PaymentDestinationType $destination,
        protected RecurrentStatus        $isRecurrent,
        protected ?int                   $cardId,
    ) {
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return int|null
     */
    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
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
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @return int|null
     */
    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    /**
     * @return PaymentDestinationType
     */
    public function getDestination(): PaymentDestinationType
    {
        return $this->destination;
    }

    /**
     * @return RecurrentStatus
     */
    public function isRecurrent(): RecurrentStatus
    {
        return $this->isRecurrent;
    }
}
