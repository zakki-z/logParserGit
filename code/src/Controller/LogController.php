<?php

namespace App\Controller;

use App\Repository\LogEntriesRepository;
use App\Service\PdfMaker;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class LogController extends AbstractController
{
    #[Route('/log', name: 'app_log_view')]
    public function view(Request $request, LogEntriesRepository $repository): Response
    {
        $user = $this->getUser();
        $severityFilter = $request->query->get('severity', '');
        $queryBuilder = $repository->findByUserQueryBuilder($user);
        if (!empty($severityFilter)) {
            $queryBuilder->andWhere('l.type = :severity')
                ->setParameter('severity', $severityFilter);
        }
        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10); // Items per page
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        return $this->render('log/index.html.twig', [
            'logEntries' => $pagerfanta->getCurrentPageResults(),
            'pager' => $pagerfanta,
            'severityFilter' => $severityFilter
        ]);
    }
    #[Route('/pdf', name: 'log_pdf', methods: ['GET'])]
    public function pdf(LogEntriesRepository $logRepository, PdfMaker $pdfMaker): Response
    {
        $user = $this->getUser();
        $logEntries = $logRepository->findByUser($user);

        $logPdf = $pdfMaker->generateLogPdf($logEntries);

        return new Response($logPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="logs.pdf"'
        ]);
    }
    #[Route('/clear', name: 'log_clear', methods: ['POST'])]
    public function clear(LogEntriesRepository $logRepository): Response
    {
        $file = $this->getUser();
        $logRepository->clearAllByUser($file->getId());
        $this->addFlash('success', 'All your logs cleared');
        return $this->redirectToRoute('app_log_view');
    }
}
