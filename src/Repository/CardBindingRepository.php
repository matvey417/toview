<?php

namespace App\Repository;

use App\Entity\CardBinding;
use App\Enum\CardBindingStatusEnum;
use App\ValueObject\CardBindingStatus;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method CardBinding|null find($id, $lockMode = null, $lockVersion = null)
 * @method CardBinding|null findOneBy(array $criteria, array $orderBy = null)
 * @method CardBinding[]    findAll()
 * @method CardBinding[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardBindingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CardBinding::class);
    }

    /**
     * @param CardBinding $entity
     * @param bool $flush
     */
    public function add(CardBinding $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @param CardBinding $entity
     * @param bool $flush
     */
    public function remove(CardBinding $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Возвращает записи по которым ещё идет обработка(в статусе created или success)
     *
     * @param $user
     *
     * @return CardBinding|null
     * @throws NonUniqueResultException
     */
    public function getPendingRecordsByUser($user): ?CardBinding
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user AND c.status IN (:created, :success)')
            ->setParameter('user', $user)
            ->setParameter('created', (new CardBindingStatus(CardBindingStatusEnum::Created->value))->getValue())
            ->setParameter('success', (new CardBindingStatus(CardBindingStatusEnum::Success->value))->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает запись по пользователю в статусе "created"
     *
     * @param UserInterface $user
     *
     * @return CardBinding|null
     * @throws NonUniqueResultException
     */
    public function getBindWithCreatedStatusByUser(UserInterface $user): ?CardBinding
    {
        return $this->createQueryBuilder('c')
            ->where('c.user = :user AND c.status = :created')
            ->setParameter('user', $user)
            ->setParameter('created', (new CardBindingStatus(CardBindingStatusEnum::Created->value))->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает запись по systemId в статусе "success"
     *
     * @param string $systemId
     *
     * @return CardBinding|null
     * @throws NonUniqueResultException
     */
    public function getBindWithSuccessStatusBySystemId(string $systemId): ?CardBinding
    {
        return $this->createQueryBuilder('c')
            ->where('c.systemId = :systemId AND c.status = :success')
            ->setParameter('systemId', $systemId)
            ->setParameter('success', (new CardBindingStatus(CardBindingStatusEnum::Success->value))->getValue())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Возвращает количество привязок по клиенту за текущий день
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countBindTodayByUser($user): int
    {
        $begin = (new DateTimeImmutable())->setTime(0, 0);
        $end = (new DateTimeImmutable())->setTime(23, 59);

        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.user = :user AND c.status = :completed AND c.updatedAt >= :begin AND c.updatedAt <= :end')
            ->setParameter('user', $user)
            ->setParameter('completed', (new CardBindingStatus(CardBindingStatusEnum::Completed->value))->getValue())
            ->setParameter('begin', $begin)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
