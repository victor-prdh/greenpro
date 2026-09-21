<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DashboardStats;
use App\Dto\MonthlyActivity;
use App\Entity\Enum\LocationStatusEnum;
use App\Repository\CustomerRepository;
use App\Repository\LocationRepository;
use App\Repository\MaterialRepository;

final readonly class DashboardStatsProvider
{
    private const int ACTIVITY_MONTHS = 6;

    public function __construct(
        private CustomerRepository $customerRepository,
        private MaterialRepository $materialRepository,
        private LocationRepository $locationRepository,
    ) {
    }

    public function getStats(): DashboardStats
    {
        return new DashboardStats(
            customerCount: $this->customerRepository->count([]),
            materialCount: $this->materialRepository->count([]),
            activeLocationCount: $this->locationRepository->countByStatusValue(LocationStatusEnum::IN_PROGRESS),
            anomalyLocationCount: $this->locationRepository->countAnomalies(),
            anomalyMaterialCount: $this->materialRepository->countAnomalies(),
            totalRevenue: $this->locationRepository->sumRevenue(),
            materialsByStatus: $this->materialRepository->countByStatus(),
            locationsByStatus: $this->locationRepository->countByStatus(),
            locationsPerMonth: $this->buildMonthlyActivity(),
        );
    }

    /**
     * @return list<MonthlyActivity>
     */
    private function buildMonthlyActivity(): array
    {
        $from = new \DateTimeImmutable('first day of this month')
            ->modify(\sprintf('-%d months', self::ACTIVITY_MONTHS - 1));

        $buckets = [];
        $cursor = $from;
        for ($i = 0; $i < self::ACTIVITY_MONTHS; ++$i) {
            $key = $cursor->format('Y-m');
            $buckets[$key] = ['label' => $cursor->format('M Y'), 'count' => 0, 'revenue' => 0.0];
            $cursor = $cursor->modify('+1 month');
        }

        foreach ($this->locationRepository->findActivitySince($from) as $row) {
            $key = $row['createdAt']->format('Y-m');
            if (!isset($buckets[$key])) {
                continue;
            }

            ++$buckets[$key]['count'];
            $buckets[$key]['revenue'] += (float) $row['totalPrice'];
        }

        return array_values(array_map(
            static fn (array $bucket): MonthlyActivity => new MonthlyActivity(
                label: $bucket['label'],
                locationCount: $bucket['count'],
                revenue: number_format($bucket['revenue'], 2, '.', ''),
            ),
            $buckets,
        ));
    }
}
