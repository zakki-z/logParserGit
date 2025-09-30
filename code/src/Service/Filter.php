<?php

namespace App\Service;

use App\Repository\LogEntriesRepository;
use Mpdf\PsrHttpMessageShim\Request;

class Filter
{
    public function filter(Request $request, LogEntriesRepository $repository, $user)
    {
        $severityFilter = $request->query->get('severity', '');
        $queryBuilder = $repository->findByUserQueryBuilder($user);
        if (!empty($severityFilter)) {

            $queryBuilder->andWhere('l.type = :severity')
                ->setParameter('severity', $severityFilter);
        }
        return $severityFilter;
    }
}
