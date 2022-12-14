<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Enum\PaymentDestinationEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * @param Payment $entity
     * @param bool $flush
     */
    public function add(Payment $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param Payment $entity
     * @param bool $flush
     */
    public function remove(Payment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Возвращает платежку по systemId
     *
     * @param string $systemId
     *
     * @return Payment|null
     * @throws NonUniqueResultException
     */
    public function findBySystemId(string $systemId): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->where('p.systemId = :systemId')
            ->setParameter('systemId', $systemId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Ищет платеж по orderId
     *
     * @param string $orderId
     * @return Payment|null
     * @throws NonUniqueResultException
     */
    public function findByOrderId(string $orderId): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->where('p.orderId = :orderId')
            ->setParameter('orderId', $orderId)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * Возвращает платежки с назначением outgoing
     */
    public function findAllOutgoings(): ?array
    {
        return $this->createQueryBuilder('p')
            ->where('p.destination = :outgoing')
            ->setParameter('outgoing', PaymentDestinationEnum::Outgoing->value)
            ->getQuery()
            ->getResult();
    }
}
