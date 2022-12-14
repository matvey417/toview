<?php

namespace App\Tests\Command;

use App\DataFixtures\CardFixtures;
use App\DataFixtures\CommissionFixtures;
use App\DataFixtures\ProjectRateFixtures;
use App\DataFixtures\StaffRobotFixtures;
use App\DataFixtures\TumblerFixtures;
use App\Entity\Check;
use App\Entity\Commission;
use App\Entity\OutgoingTransfer;
use App\Entity\Payment;
use App\Entity\Tumbler;
use App\Enum\PaymentStatusEnum;
use App\ValueObject\Money;
use App\ValueObject\PaymentStatus;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Тест для команды выплат пользователям, а также тест для обработки коллбеков созданных в команде платежей
 */
class PayOutTest extends WebTestCase
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;

    /** @var KernelBrowser client */
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $kernel = $this->client->getKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->client->followRedirects();
    }

    /**
     * Выполняем команду по выплатам
     *
     * @return array
     */
    public function testExecuteCommand(): array
    {
        $this->createFixtures();
        $kernel = $this->client->getKernel();
        $application = new Application($kernel);
        $command = $application->find('app:payoutToUsers');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $payments = $this->entityManager->getRepository(Payment::class)->findAllOutgoings();
        $minSum = $this->entityManager->getRepository(Tumbler::class)->findMinSumForPayout();

        /** Проверяем, что создано 3 платежа */
        $this->assertCount(3, $payments);

        /** Проверяем, что сумму всех платежей больше минимальной суммы и равны WalletSum у пользователя */
        foreach ($payments as $payment) {
            $this->assertLessThanOrEqual($payment->getSum()->getRub(), $minSum);
            $this->assertEquals($payment->getUser()->getWalletSum(), $payment->getSum());;
        }

        return $payments;
    }

    /**
     * По созданным командой платежам получаем и обрабатываем коллбеки
     *
     * @depends testExecuteCommand
     *
     * @param array $payments
     *
     * @return void
     */
    public function testCallback(array $payments): void
    {
        $kernel = $this->client->getKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();

        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $fields = [
                'merchantId' => '114',
                'orderId' => $payment->getOrderId(),
                'email' => $payment->getEmail(),
                'orderActualTill' => '2022-05-13 11:49:35Z',
                'price' => $payment->getSum()->getRub(),
                'action' => 'payout',
                'customer_fullName' => '',
                'customer_phone' => '+797774999',
                'customer_email' => 'dea@veme.com',
                'transaction' => $payment->getSystemId(),
                'object_type' => 'transaction',
                'status' => 'success',
                'payment_system' => 'mandarinpayv1',
                'sandbox' => 'true',
                'cb_processed_at' => '2022-05-11T11:49:36.2322892Z',
                'card_number' => '469206XXXXXX9192',
                'cb_customer_creditcard_number' => '469206XXXXXX9192',
                'gw_channel' => 'internal',
                'gw_id' => 'b3ac2161-7c9a-43de-95fa-d795d207db45',
                'e2c0bb67c141-43d4-8061-eb6de0c87bf7' => 'b8c66000-5ae7-44d1-9b67-694f858b1cef',
                'sign' => '78c8424e599bbcc50f95523ae2d794e235c9a71700cd97f06715b4a277069987',
            ];
            $this->client->request('GET', 'http://77777.ru/api/transactionPayoutCallback', $fields);
            $this->assertResponseIsSuccessful();
            $newPayment = $this->entityManager->getRepository(Payment::class)->find($payment->getId());

            $this->assertEquals($newPayment->getStatus(), new PaymentStatus(PaymentStatusEnum::Completed->value));
            $this->assertEquals($newPayment->getUser()->getWalletSum(), new Money(0));

            $outgoingTransfer = $this->entityManager->getRepository(OutgoingTransfer::class)->findOneBy(['payment' => $payment->getId()]);
            $this->assertEquals($outgoingTransfer->getSum(), $newPayment->getSum());
            $commissions = $this->entityManager->getRepository(Commission::class)->findBy([
                'outgoingTransfer' => $outgoingTransfer->getId()
            ]);
            $this->assertCount(1, $commissions);
            $checks = $this->entityManager->getRepository(Check::class)->findBy(['payment' => $newPayment->getId()]);
            $this->assertCount(1, $checks);
        }
    }

    /**
     * Создание фикстур
     *
     * @return void
     */
    protected function createFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new CardFixtures());
        $loader->addFixture(new StaffRobotFixtures());
        $loader->addFixture(new TumblerFixtures());
        $loader->addFixture(new ProjectRateFixtures());
        $loader->addFixture(new CommissionFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
