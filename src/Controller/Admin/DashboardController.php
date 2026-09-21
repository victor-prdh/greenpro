<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Service\DashboardChartBuilder;
use App\Service\DashboardStatsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin', name: 'app_admin_')]
#[IsGranted('ROLE_MANAGER')]
class DashboardController extends AbstractController
{
    #[Route(path: '', name: 'dashboard')]
    public function index(DashboardStatsProvider $statsProvider, DashboardChartBuilder $chartBuilder): Response
    {
        $stats = $statsProvider->getStats();

        return $this->render('admin/dashboard.html.twig', [
            'stats' => $stats,
            'materialsChart' => $chartBuilder->buildMaterialsByStatusChart($stats),
            'locationsChart' => $chartBuilder->buildLocationsByStatusChart($stats),
            'activityChart' => $chartBuilder->buildMonthlyActivityChart($stats),
        ]);
    }
}
