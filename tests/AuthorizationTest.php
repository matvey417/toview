<?php

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use App\Tests\Traits\PreparationTestTrait;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorizationTest extends WebTestCase
{
    /** Подготовка KernelBrowser, entityManager, вызов создания фикстур */
    use PreparationTestTrait;

    /**
     * @return void
     */
    public function testEmailAuthorization(): void
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        $creator = $users[1];
        $this->client->request('GET', 'https://777.ru/login');
        /** Авторизация с некорректным паролем */
        $this->client->submitForm('Войти',
            [
                'login_form[username]' => $creator->getLogin(),
                'login_form[password]' => '1234'
            ]);
        $this->assertSelectorTextContains('body > main > div > div.container > div.alert-danger.rounded-pill.ps-4.pt-2.pb-2', 'Неправильный пароль');


        /** Авторизация с некорректным логином */
        $this->client->submitForm('Войти',
            [
                'login_form[username]' => 'petrushka',
                'login_form[password]' => '123'
            ]);
        $this->assertSelectorTextContains('body > main > div > div.container > div.alert-danger.rounded-pill.ps-4.pt-2.pb-2', 'Пользователь с указанным логином не зарегистрирован');

        /** Успешная авторизация */
        $this->client->submitForm('Войти',
            [
                'login_form[username]' => $creator->getLogin(),
                'login_form[password]' => '123'
            ]);

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('Кабинет');
        $this->assertSelectorTextContains('h3', $creator->getFirstName());

        /** Разлогиневаемся */
        $this->client->request('GET', 'https://7777.ru/logout');
        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('77777');
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
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
