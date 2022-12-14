<?php

namespace App\Repository;

use App\Entity\Card;
use App\Enum\RecordActiveStatusEnum;
use App\ValueObject\RecordActiveStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    /**
     * @param Card $entity
     * @param bool $flush
     */
    public function add(Card $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Card $entity
     * @param bool $flush
     */
    public function remove(Card $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Возвращает актуальную карту по systemId
     *
     * @param string $systemId
     *
     * @return Card|null
     * @throws NonUniqueResultException
     */
    public function findBySystemId(string $systemId): ?Card
    {
        return $this->createQueryBuilder('c')
            ->where('c.systemId = :systemId AND c.active = :active')
            ->setParameter('systemId', $systemId)
            ->setParameter('active', (new RecordActiveStatus(RecordActiveStatusEnum::Active->value))->getValue())
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
