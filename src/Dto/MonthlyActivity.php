<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class MonthlyActivity
{
    public function __construct(
        public string $label,
        public int $locationCount,
        public string $revenue,
    ) {
    }
}
