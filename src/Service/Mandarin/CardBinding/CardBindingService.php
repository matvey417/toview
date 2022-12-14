<?php

namespace App\Service\Mandarin\CardBinding;

use App\Entity\CardBinding;
use App\Entity\Merchant;
use App\Factory\CardBindingStatusFactory;
use App\Repository\CardBindingRepository;
use App\Service\Mandarin\MerchantComponent;
use App\ValueObject\CardSystemType;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Сервис для работы с привязкой карты
 */
class CardBindingService
{
    /** @var Merchant|null merchant */
    private ?Merchant $merchant;

    /**
     * @param MerchantComponent $merchantComponent
     * @param CardBindingRepository $cardBindingRepository
     * @param CardBindingStatusFactory $bindingStatusFactory
     */
    public function __construct(
        MerchantComponent                  $merchantComponent,
        protected CardBindingRepository    $cardBindingRepository,
        protected CardBindingStatusFactory $bindingStatusFactory,
    ) {
        $this->merchant = $merchantComponent->getMerchant();
    }

    /**
     * Создать объект CardBinding, статус "created"
     *
     * @param $user
     *
     * @return CardBinding
     */
    public function createCardBinding($user): CardBinding
    {
        $cardBind = new CardBinding();
        $cardBind->setCardBindingSystem(new CardSystemType('MANDARIN'));
        $cardBind->setStatus($this->bindingStatusFactory->makeCreated());
        $cardBind->setMerch($this->getMerchant());
        $cardBind->setUser($user);

        return $cardBind;
    }

    /**
     * Заполнить данные по привязке карты данными из ответа от мандарина
     *
     * @param CardBinding $cardBind
     * @param MandarinCardBindingResponse $response
     *
     * @return CardBinding
     */
    public function setBindingCard(CardBinding $cardBind, MandarinCardBindingResponse $response): CardBinding
    {
        if ($response->isSuccess()) {
            $cardBind->setStatus($this->bindingStatusFactory->makeSuccess());
            $cardBind->setSystemId($response->getBindingId());
            $cardBind->setCardNumber($response->getCardNumber());
        } elseif ($response->isError()) {
            $cardBind->setStatus($this->bindingStatusFactory->makeFailed());
        }

        return $cardBind;
    }

    /**
     * Заполняем данные привязки карты по коллбеку от Мандарина
     *
     * @throws NonUniqueResultException
     */
    public function updateBindingCardByCallback(MandarinCardBindingCallbackResponse $response): CardBinding
    {
        $cardBind = $this->cardBindingRepository->getBindWithSuccessStatusBySystemId($response->getCardBinding());

        if (null === $cardBind) {
            throw new NotFoundHttpException('Не найдена привязка в статусе "Success" по номеру карты: ' . $response->getCardNumber()->getValue());
        }

        $cardBind->setJsonData($response->getDataJson());

        if ($response->isFailed()) {
            $cardBind->setStatus($this->bindingStatusFactory->makeFailed());

            return $cardBind;
        }

        if ($response->isAckFailed()) {
            $cardBind->setStatus($this->bindingStatusFactory->makeAckFailed());

            return $cardBind;
        }
        $cardBind->setStatus($this->bindingStatusFactory->makeCompleted());

        return $cardBind;
    }

    /**
     * @return Merchant|null
     */
    public function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }
}
