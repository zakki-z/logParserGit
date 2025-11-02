<?php

namespace App\Controller;

use App\Repository\DashboardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(DashboardRepository $dashboardRepository): Response
    {
        $user = $this->getUser();

        // Get basic stats for initial load
        $stats = $dashboardRepository->getDashboardStats($user);
        $recentLogs = $dashboardRepository->getRecentLogs($user, 10);

        return $this->render('dashboard/index.html.twig', [
            'stats' => $stats,
            'recentLogs' => $recentLogs
        ]);
    }

    #[Route('/api/dashboard/data', name: 'api_dashboard_data')]
    #[IsGranted('ROLE_USER')]
    public function getDashboardData(DashboardRepository $dashboardRepository): JsonResponse
    {
        $user = $this->getUser();

        $stats = $dashboardRepository->getDashboardStats($user);
        $recentLogs = $dashboardRepository->getRecentLogs($user, 10);
        $formattedLogs = $dashboardRepository->formatLogsForApi($recentLogs);

        return $this->json([
            'stats' => $stats,
            'recentLogs' => $formattedLogs,
            'lastUpdate' => (new \DateTime())->format('H:i:s')
        ]);
    }
}
