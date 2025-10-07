<?php

namespace App\Controller;

use App\Repository\LogEntriesRepository;
use App\Service\LogFilterService;
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
    public function view(Request $request, LogEntriesRepository $repository, LogFilterService $filterService): Response
    {
        $user = $this->getUser();

        $filters = [
            'type' => $request->query->get('type', ''),
            'channel' => $request->query->get('channel', ''),
            'fileName' => $request->query->get('fileName', ''),
            'search' => $request->query->get('search', ''),
            'dateFrom' => $request->query->get('dateFrom', ''),
            'dateTo' => $request->query->get('dateTo', '')
        ];
        $queryBuilder = $filterService->applyFilters($user, $filters);

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        return $this->render('log/index.html.twig', [
            'logEntries' => $pagerfanta->getCurrentPageResults(),
            'pager' => $pagerfanta,
            'filters' => $filters,
        ]);
    }
    #[Route('/pdf', name: 'log_pdf', methods: ['GET'])]
    public function pdf(Request $request,
                        LogEntriesRepository $logRepository,
                        LogFilterService $filterService,
                        PdfMaker $pdfMaker): Response
    {
        $user = $this->getUser();
        $filters = [
            'type' => $request->query->get('type', ''),
            'channel' => $request->query->get('channel', ''),
            'fileName' => $request->query->get('fileName', ''),
            'search' => $request->query->get('search', ''),
            'dateFrom' => $request->query->get('dateFrom', ''),
            'dateTo' => $request->query->get('dateTo', '')
        ];
        $queryBuilder = $filterService->applyFilters($user, $filters);
        $logEntries = $queryBuilder->getQuery()->getResult();

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
