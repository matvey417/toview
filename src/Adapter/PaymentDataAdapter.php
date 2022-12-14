<?php

namespace App\Adapter;

use App\Component\Mandarin\MandarinTransactionComponentInterface;
use App\Component\Mandarin\MandarinTransactionSinglePayComponentInterface;
use App\Component\Mandarin\MandarinTransactionSubscriptionPayComponent;
use App\DTO\PaymentDataDTO;
use App\Enum\PaymentActionTypeEnum;
use App\Factory\PaymentDestinationTypeFactory;
use App\Factory\PaymentStatusFactory;
use App\Factory\RecurrentStatusFactory;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * Адаптер данных платежки из компоненты транзакции
 */
class PaymentDataAdapter
{
    /** @var PaymentDataDTO dto */
    protected PaymentDataDTO $dto;

    /**
     * @param CardRepository $cardRepository
     * @param Security $security
     * @param UserRepository $userRepository
     * @param PaymentDestinationTypeFactory $destinationTypeFactory
     * @param PaymentStatusFactory $paymentStatusFactory
     * @param RecurrentStatusFactory $recurrentStatusFactory
     */
    public function __construct(
        protected CardRepository                $cardRepository,
        protected Security                      $security,
        protected UserRepository                $userRepository,
        protected PaymentDestinationTypeFactory $destinationTypeFactory,
        protected PaymentStatusFactory          $paymentStatusFactory,
        protected RecurrentStatusFactory        $recurrentStatusFactory,
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws EntityNotFoundException
     */
    public function createDto(
        string                                $transactionId,
        ?int                                  $projectId,
        MandarinTransactionComponentInterface $transactionComponent
    ): static {
        /** @todo придумать как это все нормально сделать */
        if ($transactionComponent instanceof MandarinTransactionSinglePayComponentInterface) {
            $user = $this->security->getUser();
            if (null === $user) {
                $user = $this->userRepository->getAnonymous();
            }

            $cardId = null;
            $email = $transactionComponent->getEmail();
        } else {
            $card = $this->cardRepository->findBySystemId($transactionComponent->getCardSystemId());
            if (null === $card) {
                throw new EntityNotFoundException('Не найдена актуальная карта по systemId: ' . $transactionComponent->getCardSystemId(), 400);
            }

            $cardId = $card->getId();
            $user = $card->getUser();
            $email = $user->getEmail();
        }

        if (null === $user) {
            throw new NotFoundHttpException('Ошибка получения данных. Не удалось получить пользователя!');
        }

        /** todo recurrent у нас только доля платежке по подписке? а донат и одиночный платеж */
        if ($transactionComponent instanceof MandarinTransactionSubscriptionPayComponent) {
            $isRecurrent = $this->recurrentStatusFactory->makeRecurrent();
        } else {
            $isRecurrent = $this->recurrentStatusFactory->makeNonRecurrent();
        }

        if (PaymentActionTypeEnum::Pay->value === $transactionComponent->getActionType()) {
            $destination = $this->destinationTypeFactory->makeIncoming();
        } else {
            $destination = $this->destinationTypeFactory->makeOutgoing();
        }

        $this->dto = new PaymentDataDTO(
            $user,
            $projectId,
            $email,
            $transactionComponent->getOrderId(),
            $transactionComponent->getPrice(),
            $transactionId,
            $destination,
            $isRecurrent,
            $cardId,
        );

        return $this;
    }

    /**
     * @return PaymentDataDTO
     */
    public function getDto(): PaymentDataDTO
    {
        return $this->dto;
    }
}
