<?php

namespace App\Service;

use App\DTO\PaymentDataDTO;
use App\Entity\Merchant;
use App\Entity\Payment;
use App\Factory\PaymentStatusFactory;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Service\Mandarin\MerchantComponent;
use App\Service\Mandarin\Transaction\MandarinPayCallBackResponse;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;

/**
 * Сервис для работы с entity Payment
 */
class PaymentService
{
    /** @var Merchant|null merchant */
    private ?Merchant $merchant;

    /**
     * @param MerchantComponent $merchantComponent
     * @param PaymentRepository $paymentRepository
     * @param PaymentStatusFactory $paymentStatusFactory
     * @param ProjectRepository $projectRepository
     */
    public function __construct(
        MerchantComponent              $merchantComponent,
        protected PaymentRepository    $paymentRepository,
        protected PaymentStatusFactory $paymentStatusFactory,
        protected ProjectRepository    $projectRepository,

    ) {
        $this->merchant = $merchantComponent->getMerchant();
    }

    /**
     * Возвращает платеж(entity Payment)
     * Статус платежки ставится сразу Success так как первичный положительный ответ от Мандарина уже получен
     *
     * @param PaymentDataDTO $dto
     *
     * @return Payment
     */
    public function createPayment(PaymentDataDTO $dto): Payment
    {
        $project = null;
        if (null !== $dto->getProjectId()) {
            $project = $this->projectRepository->find($dto->getProjectId());
        }

        $payment = new Payment();
        $payment->setMerch($this->getMerchant());
        $payment->setStatus($this->paymentStatusFactory->makeSuccess());
        $payment->setProject($project);
        $payment->setRecurrentStatus($dto->isRecurrent());
        $payment->setDestination($dto->getDestination());
        $payment->setSystemId($dto->getTransactionId());
        $payment->setOrderId($dto->getOrderId());
        $payment->setEmail($dto->getEmail());
        $payment->setSum($dto->getPrice());
        $payment->setUser($dto->getUser());
        $payment->setCardId($dto->getCardId());

        return $payment;
    }

    /**
     * Заполнить данные платежки из коллбека
     * Платежку получаем из коллбека
     *
     * @throws NonUniqueResultException
     * @throws EntityNotFoundException
     */
    public function setPaymentFromCallbackData(MandarinPayCallBackResponse $callBackResponse): Payment
    {
        $payment = $this->paymentRepository->findByOrderId($callBackResponse->getOrderId());
        if (null === $payment) {
            throw new EntityNotFoundException('Не найдена платежка по SystemId = ' . $callBackResponse->getTransaction());
        }

        $payment->setCallback($callBackResponse->getDataJson());

        if ($callBackResponse->isSuccess()) {
            $payment->setStatus($this->paymentStatusFactory->makeCompleted());

            return $payment;
        }

        $payment->setStatus($this->paymentStatusFactory->makeFailed());

        return $payment;
    }

    /**
     * @return Merchant|null
     */
    protected function getMerchant(): ?Merchant
    {
        return $this->merchant;
    }
}
