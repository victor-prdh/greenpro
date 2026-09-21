<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class DashboardStats
{
    /**
     * @param array<string, int>    $materialsByStatus
     * @param array<string, int>    $locationsByStatus
     * @param list<MonthlyActivity> $locationsPerMonth
     */
    public function __construct(
        public int $customerCount,
        public int $materialCount,
        public int $activeLocationCount,
        public int $anomalyLocationCount,
        public int $anomalyMaterialCount,
        public string $totalRevenue,
        public array $materialsByStatus,
        public array $locationsByStatus,
        public array $locationsPerMonth,
    ) {
    }
}
