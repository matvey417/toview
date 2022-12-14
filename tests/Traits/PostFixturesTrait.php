<?php

namespace App\Tests\Traits;

use App\DataFixtures\LevelFixtures;
use App\DataFixtures\ProjectFixtures;
use App\DataFixtures\RateFixtures;
use App\DataFixtures\UserFixtures;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 * Создание фикстур для постов
 */
trait PostFixturesTrait
{
    /**
     * Создаем фикстуры для проведения теста
     *
     * @return void
     */
    protected function createFixtures(): void
    {
        $loader = new Loader();
        $loader->addFixture(new UserFixtures());
        $loader->addFixture(new ProjectFixtures());
        $loader->addFixture(new LevelFixtures());
        $loader->addFixture(new RateFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}