<?php

namespace App\Service\Mandarin\Transaction;

use App\Adapter\PaymentDataAdapter;
use App\Component\Mandarin\MandarinTransactionComponentInterface;
use App\Entity\Commission;
use App\Entity\Payment;
use App\Repository\CommissionRepository;
use App\Service\IncomingTransferService;
use App\Service\OutgoingTransferService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;

/**
 * Сервис для создания агрегата платежа, не действует для платежей с отдельным вводом карты
 */
class TransactionService
{
    /**
     * @param PaymentService $paymentService
     * @param IncomingTransferService $incomingTransferService
     * @param CommissionService $commissionService
     * @param PaymentDataAdapter $adapter
     * @param EntityManagerInterface $entityManager
     * @param OutgoingTransferService $outgoingTransferService
     * @param CommissionRepository $commissionRepository
     */
    public function __construct(
        protected PaymentService          $paymentService,
        protected IncomingTransferService $incomingTransferService,
        protected CommissionService       $commissionService,
        protected PaymentDataAdapter      $adapter,
        protected EntityManagerInterface  $entityManager,
        protected OutgoingTransferService $outgoingTransferService,
        protected CommissionRepository    $commissionRepository,
    ) {
    }

    /**
     * Создает и сохраняет в БД Payment, IncomingTransfer, Commission
     *
     * @param string $transactionId
     * @param MandarinTransactionComponentInterface $payDataComponent
     *
     * @param int|null $projectId
     *
     * @return Payment
     * @throws EntityNotFoundException
     */
    public function createPayment(
        string                                $transactionId,
        MandarinTransactionComponentInterface $payDataComponent,
        ?int                                  $projectId = null,
    ): Payment {
        $dto = $this->adapter->createDto($transactionId, $projectId, $payDataComponent)->getDto();

        $payment = $this->paymentService->createPayment($dto);
        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $payment;
    }

    /**
     * Создает и сохраняет в БД IncomingTransfer, Commission по существующему Payment'у
     * и добавляет сумму выплаты пользователю в walletSum
     *
     * @param Payment $payment
     *
     * @return void
     */
    public function createAggregateIncoming(Payment $payment): void
    {
        $incomingTransfer = $this->incomingTransferService->createByPayment($payment);
        $this->entityManager->persist($incomingTransfer);
        $this->entityManager->flush();

        $commission = $this->commissionService->createByIncoming($incomingTransfer);

        /** Прибавляем сумму для выплаты */
        $owner = $incomingTransfer->getProject()->getOwner();
        $owner->addWalletSum($commission->getSumFact());

        $this->entityManager->persist($commission);
        $this->entityManager->flush();
    }

    /**
     * @param Payment $payment
     *
     * @return void
     */
    public function createAggregateOutgoing(Payment $payment): void
    {
        $outgoing = $this->outgoingTransferService->createByPayment($payment);
        $this->entityManager->persist($outgoing);
        $this->entityManager->flush();

        /** Прибавляем сумму для выплаты */

        $payment->getUser()->diffWalletSum($outgoing->getSum());
        $this->entityManager->flush();

        $commissions = $this->commissionRepository->findCommissionsBeforePayoutByUser($payment->getCreatedAt(), $payment->getUser());
        /** @var Commission $commission */
        foreach ($commissions as $commission) {
            $commission->setOutgoingTransfer($outgoing);
        }
        $this->entityManager->flush();
    }
}
