<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\LogEntriesRepository;
use Doctrine\ORM\QueryBuilder;

class LogFilterService
{
    public function __construct(
        private readonly LogEntriesRepository $logRepository
    ) {}
    public function applyFilters(User $user, array $filters): QueryBuilder
    {
        return $this->logRepository->findByUserWithFilters($user, $filters);
    }
}
