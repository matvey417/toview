<?php

namespace App\Tests;

use App\DataFixtures\CardFixtures;
use App\DataFixtures\LevelFixtures;
use App\DataFixtures\PaymentIncomingFixtures;
use App\DataFixtures\PostFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\ProjectRateFixtures;
use App\DataFixtures\StaffRobotFixtures;
use App\DataFixtures\SubscriptionCreatedStatusFixtures;
use App\DataFixtures\TumblerFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Check;
use App\Entity\IncomingTransfer;
use App\Entity\Payment;
use App\Entity\Subscription;
use App\Enum\PaymentStatusEnum;
use App\Enum\SubscriptionStatusEnum;
use App\Tests\Traits\PreparationTestTrait;
use App\ValueObject\PaymentStatus;
use App\ValueObject\SubscriptionStatus;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тестирование обработки колбека платежки
 */
class CallbackPayTest extends WebTestCase
{
    /** Подготовка KernelBrowser, entityManager, вызов создания фикстур */
    use PreparationTestTrait;

    /**
     * @return void
     */
    public function testCallback(): void
    {
        $payments = $this->entityManager->getRepository(Payment::class)->findAll();
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
                'customer_phone' => '+79577777799',
                'customer_email' => 'd@vshleme.com',
                'transaction' => $payment->getSystemId(),
                'object_type' => 'transaction',
                'status' => 'success',
                'payment_system' => 'mandarinpayv1',
                'sandbox' => 'true',
                'cb_processed_at' => '2022-05-11T11:49:36.2322892Z',
                'card_number' => '469206XXXXXX9192',
                'cb_customer_creditcard_number' => '469206XXXXXX9192',
                'gw_channel' => 'internal',
                'gw_id' => '14741-7c9a-43de-777a-d795d207db45',
                'e2c0bb67c141-43d4-8061-eb6de0c87bf7' => 'b8c77700-5ae7-44d1-9b67-694f858b1cef',
                'sign' => '78c8424e599bbcc50f95523ae2777a71700cd97f06715b4a277069987',
            ];
            $this->client->request('GET', 'http://777.ru/api/transactionPayCallback', $fields);
            $newPayment = $this->entityManager->getRepository(Payment::class)->find($payment->getId());
            $incoming = $this->entityManager->getRepository(IncomingTransfer::class)->findOneBy(['payment' => $payment->getId()]);

            $this->assertEquals($incoming->getSum(), $newPayment->getSum());
            $this->assertEquals($newPayment->getStatus(), new PaymentStatus(PaymentStatusEnum::Completed->value));
            $this->assertEquals($incoming->getCommission()->getSumFull(), $newPayment->getSum());
            $checks = $this->entityManager->getRepository(Check::class)->findBy(['payment' => $newPayment->getId()]);
            $this->assertCount(1, $checks);

            $subscription = $this->entityManager->getRepository(Subscription::class)->findBy(['payment' => $newPayment->getId()]);
            $this->assertCount(1, $checks);
            $this->assertEquals($subscription[0]->getStatus(), new SubscriptionStatus(SubscriptionStatusEnum::Active->value));
        }
    }

    /**
     * Создаем фикстуры для проведения теста
     *
     * @return void
     */
    protected function createFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new CardFixtures());
        $loader->addFixture(new ProjectFixtures());
        $loader->addFixture(new ProjectRateFixtures());
        $loader->addFixture(new PostFixtures());
        $loader->addFixture(new LevelFixtures());
        $loader->addFixture(new PaymentIncomingFixtures());
        $loader->addFixture(new StaffRobotFixtures());
        $loader->addFixture(new SubscriptionCreatedStatusFixtures());

        $loader->addFixture(new TumblerFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
