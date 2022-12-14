<?php

namespace App\Tests\Command;

use App\DataFixtures\CardFixtures;
use App\DataFixtures\CommissionFixtures;
use App\DataFixtures\DeliveredSmsFixtures;
use App\DataFixtures\ProjectRateFixtures;
use App\DataFixtures\SmsForCheckFixtures;
use App\DataFixtures\StaffRobotFixtures;
use App\DataFixtures\TumblerFixtures;
use App\Entity\Sms;
use App\Enum\SmsStatusEnum;
use App\ValueObject\SmsStatus;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Тест для CRON команды для актуализации данных смс сообщений
 */
class CheckSmsCommandTest extends KernelTestCase
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $entityManager;

    /**
     * @return void
     */
    public function testExecuteCommand(): void
    {
        $this->entityManager = $this->bootKernel()->getContainer()->get('doctrine')->getManager();
        $this->createFixtures();
        $application = new Application($this->bootKernel());
        $command = $application->find('app:checkSms');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();

        $smses = $this->entityManager->getRepository(Sms::class)->findBy([
            'status' => new SmsStatus(SmsStatusEnum::Delivered->value),
            'price' => 3]);
        $this->assertCount(4, $smses);
    }

    /**
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
        $loader->addFixture(new SmsForCheckFixtures());
        $loader->addFixture(new DeliveredSmsFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
