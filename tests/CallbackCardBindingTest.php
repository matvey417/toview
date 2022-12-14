<?php

namespace App\Tests;

use App\DataFixtures\CardBindingFixtures;
use App\DataFixtures\CardBindingStatusSuccessFixtures;
use App\DataFixtures\CardFixtures;
use App\DataFixtures\MerchantFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Card;
use App\Entity\CardBinding;
use App\Enum\CardBindingStatusEnum;
use App\Enum\RecordActiveStatusEnum;
use App\Tests\Traits\PreparationTestTrait;
use App\ValueObject\CardBindingStatus;
use App\ValueObject\RecordActiveStatus;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Тестирование обработки коллбека от мандарина по привязке карты
 */
class CallbackCardBindingTest extends WebTestCase
{
    /** Подготовка KernelBrowser, entityManager, вызов создания фикстур */
    use PreparationTestTrait;

    /**
     * По клиенту уже привязана карта, при обработке коллбека происходит перепривязка на новую карту,
     * старая помечается как удаленная
     */
    public function testCardBindingCallback(): void
    {
        $systemId = '0532db42-f80b-4ae3-a7c1-7777';
        $fields = [
            'card_binding' => $systemId,
            'card_holder' => 'CARD HOLDER',
            'card_number' => '492950XXXXXX6878',
            'card_expiration_year' => '2023',
            'card_expiration_month' => '1',
            'object_type' => 'card_binding',
            'status' => 'success',
            'merchantId' => '114',
            'initial_hold_amount' => '1',
            '3dsecure' => 'true',
            'gw_id' => '77777-87cb-496e-b88c-874ca30e1588',
            '777777c-7c41-476c-a1a8-4c661c76bdcc' => '89e154e7-b28c-454f-9546-d7ff1e861d0b',
            'sign' => '4323d3a9cafc5b93aeec898926cd929777777a69c169f6ed3be3bd972977',
        ];
        $this->client->request('GET', 'http://77777.ru/api/cardBindingCallback', $fields);
        $cardBinding = $this->entityManager->getRepository(CardBinding::class)->findOneBy(['systemId' => $systemId]);
        $allCard = $this->entityManager->getRepository(Card::class)->findBy(['user' => $cardBinding->getUser()]);
        $this->assertCount(2, $allCard);
        $actualCard = $this->entityManager->getRepository(Card::class)->findBy([
            'user' => $cardBinding->getUser(),
            'active' => new RecordActiveStatus(RecordActiveStatusEnum::Active->value)
        ]);
        $this->assertCount(1, $actualCard);
        $this->assertEquals($cardBinding->getStatus(), new CardBindingStatus(CardBindingStatusEnum::Completed->value));
        $this->assertEquals($actualCard[0]->getActive(), new RecordActiveStatus(RecordActiveStatusEnum::Active->value));
        $this->assertEquals('492950', $actualCard[0]->getBin()->getValue());
        $this->assertEquals('6878', $actualCard[0]->getPan()->getValue());
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
        $loader->addFixture(new MerchantFixtures());
        $loader->addFixture(new CardBindingStatusSuccessFixtures());
        $loader->addFixture(new CardFixtures());
        $loader->addFixture(new CardBindingFixtures());
        $purger = new ORMPurger($this->entityManager);
        $executor = new ORMExecutor($this->entityManager, $purger);
        $executor->execute($loader->getFixtures());
    }
}
