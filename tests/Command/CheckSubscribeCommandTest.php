<?php

namespace App\Tests\Command;

use App\DataFixtures\CardFixtures;
use App\DataFixtures\LevelFixtures;
use App\DataFixtures\PostFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\ProjectRateFixtures;
use App\DataFixtures\StaffRobotFixtures;
use App\DataFixtures\SubscriptionFixtures;
use App\DataFixtures\TumblerFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Card;
use App\Entity\Subscription;
use App\Enum\SubscriptionStatusEnum;
use App\ValueObject\SubscriptionStatus;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Тесты на продление подписки
 */
class CheckSubscribeCommandTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;

    /**
     * Тестирование успешного продления подписок
     */
    public function testExecuteCommand(): void
    {
        $this->entityManager = $this->bootKernel()->getContainer()->get('doctrine')->getManager();
        $this->createFixtures();
        $this->entityManager->flush();
        $application = new Application($this->bootKernel());
        $command = $application->find('app:checkSubscription');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $failedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Failed->value)
        ]);
        $this->assertCount(0, $failedSubscription);
        $activeSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Created->value)
        ]);
        $this->assertCount(4, $activeSubscription);
        $finishedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Finished->value)
        ]);
        $this->assertCount(4, $finishedSubscription);
    }

    /**
     * Тестирование на продление прописки при просроченной карте
     */
    public function testErrorExpirationDate(): void
    {
        $this->entityManager = $this->bootKernel()->getContainer()->get('doctrine')->getManager();
        $this->createFixtures();
        /** @var Card $card */
        $card = $this->entityManager->getRepository(Card::class)->findAll()[2];
        $card->setExpirationDate((new DateTimeImmutable())->modify('-1 day'));
        $this->entityManager->flush();
        $application = new Application($this->bootKernel());
        $command = $application->find('app:checkSubscription');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $failedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Failed->value)
        ]);
        $this->assertCount(1, $failedSubscription);
        $activeSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Created->value)
        ]);
        $this->assertCount(3, $activeSubscription);
        $finishedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Finished->value)
        ]);
        $this->assertCount(3, $finishedSubscription);

    }

    /**
     * Тестирование подписки при ошибке оплаты
     */
    public function testErrorPay(): void
    {
        $this->entityManager = $this->bootKernel()->getContainer()->get('doctrine')->getManager();
        $this->createFixtures();
        /** @var Card $card */
        $card = $this->entityManager->getRepository(Card::class)->findAll()[2];
        $card->setSystemId(null);
        $this->entityManager->flush();
        $application = new Application($this->bootKernel());
        $command = $application->find('app:checkSubscription');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $failedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Failed->value)
        ]);
        $this->assertCount(1, $failedSubscription);

        $activeSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Created->value)
        ]);
        $this->assertCount(3, $activeSubscription);

        $finishedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::Finished->value)
        ]);
        $this->assertCount(3, $finishedSubscription);

        $ackFailedSubscription = $this->entityManager->getRepository(Subscription::class)->findBy([
            'status' => new SubscriptionStatus(SubscriptionStatusEnum::AckFailed->value)
        ]);
        $this->assertCount(1, $ackFailedSubscription);
    }

    /**
     * Создает фикстуры в базе
     *
     * @return void
     */
    protected function createFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new ProjectFixtures());
        $loader->addFixture(new ProjectRateFixtures());
        $loader->addFixture(new PostFixtures());
        $loader->addFixture(new CardFixtures());
        $loader->addFixture(new LevelFixtures());
        $loader->addFixture(new StaffRobotFixtures());
        $loader->addFixture(new TumblerFixtures());
        $loader->addFixture(new SubscriptionFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
