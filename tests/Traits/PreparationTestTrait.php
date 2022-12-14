<?php

namespace App\Tests\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Трейт для тестов, сюда инкапсулировано создание, KernelBrowser и entityManager, !вызов создания фикстур
 */
trait PreparationTestTrait
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
        /** todo Можно вынести из этого метода в сами тесты, одним тестам может не понадобится */
        $this->createFixtures();
        $this->client->followRedirects();
    }
}