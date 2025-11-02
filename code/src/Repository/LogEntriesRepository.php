<?php

namespace App\Repository;

use App\Entity\LogEntries;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
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
    public function findByUserWithFilters(User $user, array $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->join('l.file', 'f')
            ->andWhere('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.date', 'DESC');

        // Filter by type/severity
        if (!empty($filters['type'])) {
            $queryBuilder->andWhere('l.type = :type')
                ->setParameter('type', $filters['type']);
        }

        // Filter by channel
        if (!empty($filters['channel'])) {
            $queryBuilder->andWhere('l.channel = :channel')
                ->setParameter('channel', $filters['channel']);
        }

        // Filter by file name
        if (!empty($filters['fileName'])) {
            $queryBuilder->andWhere('f.fileName = :fileName')
                ->setParameter('fileName', $filters['fileName']);
        }

        // Search in information field
        if (!empty($filters['search'])) {
            $queryBuilder->andWhere('l.information LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Date range filters
        if (!empty($filters['dateFrom'])) {
            try {
                $dateFrom = new \DateTime($filters['dateFrom']);
                $dateFrom->setTime(0, 0, 0);
                $queryBuilder->andWhere('l.date >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        if (!empty($filters['dateTo'])) {
            try {
                $dateTo = new \DateTime($filters['dateTo']);
                $dateTo->setTime(23, 59, 59);
                $queryBuilder->andWhere('l.date <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            } catch (\Exception $e) {
                // Invalid date format, skip filter
            }
        }

        return $queryBuilder;
    }


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
    /**
     * Count total logs for a user
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count logs by user and type
     */
    public function countByUserAndType(User $user, string $type): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->andWhere('l.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent logs for a user
     */
    public function findRecentByUser(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->orderBy('l.date', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find logs within date range for a user
     */
    public function findByUserAndDateRange(User $user, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->andWhere('l.date BETWEEN :startDate AND :endDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('l.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get log statistics by type for a user
     */
    public function getLogStatsByType(User $user): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.type', 'COUNT(l.id) as count')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->groupBy('l.type')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
