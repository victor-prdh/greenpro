<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DashboardStats;
use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\MaterialStatusEnum;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class DashboardChartBuilder
{
    private const array MATERIAL_COLORS = [
        MaterialStatusEnum::AVAILABLE->value => '#198754',
        MaterialStatusEnum::RENTED->value => '#0d6efd',
        MaterialStatusEnum::MAINTENANCE->value => '#ffc107',
        MaterialStatusEnum::RETIRED->value => '#6c757d',
    ];

    private const array LOCATION_COLORS = [
        LocationStatusEnum::DRAFT->value => '#6c757d',
        LocationStatusEnum::CONFIRMED->value => '#0dcaf0',
        LocationStatusEnum::IN_PROGRESS->value => '#0d6efd',
        LocationStatusEnum::FINISHED->value => '#198754',
        LocationStatusEnum::CANCELLED->value => '#dc3545',
    ];

    public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    public function buildMaterialsByStatusChart(DashboardStats $stats): Chart
    {
        return $this->buildStatusDoughnut($stats->materialsByStatus, self::MATERIAL_COLORS);
    }

    public function buildLocationsByStatusChart(DashboardStats $stats): Chart
    {
        return $this->buildStatusDoughnut($stats->locationsByStatus, self::LOCATION_COLORS);
    }

    public function buildMonthlyActivityChart(DashboardStats $stats): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chart->setData([
            'labels' => array_map(static fn ($month) => $month->label, $stats->locationsPerMonth),
            'datasets' => [
                [
                    'label' => 'Locations',
                    'data' => array_map(static fn ($month) => $month->locationCount, $stats->locationsPerMonth),
                    'backgroundColor' => '#0d6efd',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Chiffre d\'affaires (€)',
                    'data' => array_map(static fn ($month) => (float) $month->revenue, $stats->locationsPerMonth),
                    'type' => Chart::TYPE_LINE,
                    'borderColor' => '#198754',
                    'backgroundColor' => '#198754',
                    'yAxisID' => 'y1',
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => ['type' => 'linear', 'position' => 'left', 'beginAtZero' => true],
                'y1' => ['type' => 'linear', 'position' => 'right', 'beginAtZero' => true, 'grid' => ['drawOnChartArea' => false]],
            ],
        ]);

        return $chart;
    }

    /**
     * @param array<string, int>    $counts
     * @param array<string, string> $colors
     */
    private function buildStatusDoughnut(array $counts, array $colors): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chart->setData([
            'labels' => array_keys($counts),
            'datasets' => [
                [
                    'data' => array_values($counts),
                    'backgroundColor' => array_map(
                        static fn (string $status) => $colors[$status] ?? '#adb5bd',
                        array_keys($counts),
                    ),
                ],
            ],
        ]);

        return $chart;
    }
}
