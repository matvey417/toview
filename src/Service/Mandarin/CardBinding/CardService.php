<?php

namespace App\Service\Mandarin\CardBinding;

use App\Entity\Card;
use App\Entity\User;
use App\Enum\RecordActiveStatusEnum;
use App\Factory\CardSystemTypeFactory;
use App\ValueObject\RecordActiveStatus;

/**
 * Сервис для работы с Card Entity
 */
class CardService
{
    public function __construct(protected CardSystemTypeFactory $systemTypeFactory)
    { }
    /**
     * Создать карту по коллбеку от мандарина
     *
     * @param MandarinCardBindingCallbackResponse $response
     * @param User $user
     *
     * @return Card
     */
    public function createCardByMandarinCallback(MandarinCardBindingCallbackResponse $response, User $user): Card
    {
        $cardNumber = $response->getCardNumber();

        $card = new Card();
        $card->setUser($user);
        $card->setSystemId($response->getCardBinding());
        $card->setBindingSystem($this->systemTypeFactory->makeMandarin());
        $card->setBin($cardNumber->getBin());
        $card->setPan($cardNumber->getPan());
        $card->setHolderName($response->getCardHolder());
        $card->setExpirationDate($response->getExpirationDate());
        $card->setActive(new RecordActiveStatus(RecordActiveStatusEnum::Active->value));

        return $card;
    }
}
