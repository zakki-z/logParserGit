<?php

namespace App\Repository;

use App\Entity\LogEntries;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DashboardRepository extends ServiceEntityRepository
{
    private LogEntriesRepository $logEntriesRepository;

    public function __construct(ManagerRegistry $registry, LogEntriesRepository $logEntriesRepository)
    {
        parent::__construct($registry, LogEntries::class);
        $this->logEntriesRepository = $logEntriesRepository;
    }

    /**
     * Get recent log entries for a user
     *
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getRecentLogs(User $user, int $limit = 10): array
    {
        return $this->logEntriesRepository->findByUserQueryBuilder($user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get dashboard statistics for a user
     *
     * @param User $user
     * @return array
     */
    public function getDashboardStats(User $user): array
    {
        return [
            'totalLogs' => $this->getTotalLogsCount($user),
            'errorCount' => $this->getLogCountByType($user, 'ERROR'),
            'warningCount' => $this->getLogCountByType($user, 'WARNING'),
            'infoCount' => $this->getLogCountByType($user, 'INFO'),
            'debugCount' => $this->getLogCountByType($user, 'DEBUG'),
            'topChannels' => $this->getTopChannels($user, 5)
        ];
    }

    /**
     * Get total log count for a user
     *
     * @param User $user
     * @return int
     */
    public function getTotalLogsCount(User $user): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get log count by type for a user
     *
     * @param User $user
     * @param string $type
     * @return int
     */
    public function getLogCountByType(User $user, string $type): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->andWhere('l.type = :type')
            ->setParameter('user', $user)
            ->setParameter('type', $type);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get top channels by log count for a user
     *
     * @param User $user
     * @param int $limit
     * @return array
     */
    public function getTopChannels(User $user, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('l')
            ->select('l.channel', 'COUNT(l.id) as count')
            ->join('l.file', 'f')
            ->where('f.user = :user')
            ->setParameter('user', $user)
            ->groupBy('l.channel')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Format log entries for API response
     *
     * @param array $logs
     * @return array
     */
    public function formatLogsForApi(array $logs): array
    {
        return array_map(function($log) {
            return [
                'id' => $log->getId(),
                'date' => $log->getDate()->format('Y-m-d H:i:s'),
                'channel' => $log->getChannel(),
                'type' => $log->getType(),
                'information' => substr($log->getInformation(), 0, 100) . '...',
                'fileName' => $log->getFile() ? $log->getFile()->getFileName() : 'Unknown'
            ];
        }, $logs);
    }
}
