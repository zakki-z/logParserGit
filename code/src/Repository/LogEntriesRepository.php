<?php

namespace App\Repository;

use App\Entity\LogEntries;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<LogEntries>
 */
class LogEntriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntries::class);
    }

    //    /**
    //     * @return LogEntries[] Returns an array of LogEntries objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LogEntries
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function findByUserQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('l')
            ->join('l.file', 'f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.date', 'DESC');
    }
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.date', 'DESC')  // Use 'date' field
            ->getQuery()
            ->getResult();
    }
    public function clearAllByUser(Uuid $userId): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.file IN (
            SELECT f.id FROM App\Entity\FileInfo f WHERE f.user = :userId
        )')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();

    }

}
