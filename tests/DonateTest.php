<?php

namespace App\Tests;

use App\DataFixtures\MerchantFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\UserAnonFixtures;
use App\Entity\Payment;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\PaymentStatusEnum;
use App\Enum\RecurrentStatusEnum;
use App\Tests\Traits\PreparationTestTrait;
use App\ValueObject\Money;
use App\ValueObject\PaymentStatus;
use App\ValueObject\RecurrentStatus;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тесты по проведению Донат платежа
 */
class DonateTest extends WebTestCase
{
    /** Подготовка KernelBrowser, entityManager, вызов создания фикстур */
    use PreparationTestTrait;

    /**
     * @return void
     */
    public function testDonatePay(): void
    {
        $projects = $this->entityManager->getRepository(Project::class)->findAll();

        $this->client->request('POST', '/project/donate', ['projectId' => $projects[0]->getId()]);
        $this->client->submitForm('Подтвердить',
            [
                'donate_form[email]' => 'testt@mail.ru',
                'donate_form[sum][amount]' => 1234
            ]);

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => 'anonymous']);
        $payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['user' => $user]);
        $this->assertNotEmpty($payment);
        $this->assertEquals($payment->getStatus(), new PaymentStatus(PaymentStatusEnum::Success->value));
        $this->assertEquals($payment->getRecurrentStatus(), new RecurrentStatus(RecurrentStatusEnum::NonRecurrent->value));
        $this->assertEquals($payment->getSum(), new Money(123400));
    }

    /**
     * Создаем фикстуры для проведения теста
     *
     * @return void
     */
    protected function createFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserAnonFixtures());
        $loader->addFixture(new ProjectFixtures());
        $loader->addFixture(new MerchantFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
