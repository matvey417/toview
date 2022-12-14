<?php

namespace App\Command;

use App\Component\Mandarin\MandarinTransactionPayoutComponent;
use App\Entity\User;
use App\Repository\TumblerRepository;
use App\Repository\UserRepository;
use App\Service\Mandarin\BaseApiMandarin;
use App\Service\Mandarin\Transaction\TransactionService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use HttpResponseException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CRON команда для выплаты пользователям
 */
#[AsCommand(
    name: 'app:payoutToUsers',
    description: 'Payout to users',
    hidden: false)
]
class PayOutCommand extends Command
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param MandarinTransactionPayoutComponent $payoutComponent
     * @param PaymentService $paymentService
     * @param TransactionService $transactionService
     * @param BaseApiMandarin $baseApiMandarin
     * @param UserRepository $userRepository
     * @param TumblerRepository $tumblerRepository
     */
    public function __construct(
        protected EntityManagerInterface             $entityManager,
        protected MandarinTransactionPayoutComponent $payoutComponent,
        protected PaymentService                     $paymentService,
        protected TransactionService                 $transactionService,
        protected BaseApiMandarin                    $baseApiMandarin,
        protected UserRepository                     $userRepository,
        protected TumblerRepository                  $tumblerRepository,
    ) {
        parent::__construct();
    }

    /**
     * Находим всех пользователей с ненулевой суммой для выплат,
     * и по каждому производим выплату и создаем в таблице Payment запись
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws EntityNotFoundException
     * @throws HttpResponseException
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $minSum = $this->tumblerRepository->findMinSumForPayout();

        $users = $this->userRepository->findUsersToPayOut($minSum);
        /** @var User $user */
        foreach ($users as $user) {
            $price = $user->getWalletSum();

            $this->payoutComponent->setPrice($price);
            $this->payoutComponent->setCardSystemId($user->getActualCard()->getSystemId());
            $mandarinDefaultResponse = $this->baseApiMandarin->payout($this->payoutComponent);
            $this->transactionService->createPayment($mandarinDefaultResponse->getId(), $this->payoutComponent);
        }

        return Command::SUCCESS;
    }
}
