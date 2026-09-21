<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Location;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class LocationFixtures extends Fixture implements DependentFixtureInterface
{
    public const int COUNT = 4000;

    private const string PERIOD_START = '2024-01-01';
    private const string PERIOD_END = '2027-12-31';

    private Generator $faker;

    /** @var list<int> material indices still free to be booked "now" */
    private array $availableNowPool = [];

    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            MaterialFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');
        $now = new \DateTimeImmutable('now');
        $periodStart = new \DateTimeImmutable(self::PERIOD_START);
        $periodEnd = new \DateTimeImmutable(self::PERIOD_END);

        // Only materials that start out AVAILABLE can be booked; MAINTENANCE/RETIRED stay out of rotation.
        $bookableIndices = [];
        for ($i = 1; $i <= MaterialFixtures::COUNT; ++$i) {
            $material = $this->getReference('material_'.$i, Material::class);
            if (MaterialStatusEnum::AVAILABLE === $material->status) {
                $bookableIndices[] = $i;
            }
        }
        $this->availableNowPool = $bookableIndices;

        for ($i = 1; $i <= self::COUNT; ++$i) {
            $startAt = $this->randomDateBetween($periodStart, $periodEnd);
            $endAt = $startAt->modify('+'.random_int(1, 21).' days');
            if ($endAt > $periodEnd) {
                $endAt = $periodEnd;
            }

            $status = $this->resolveStatus($startAt, $endAt, $now);

            $location = new Location();
            $location->startAt = $startAt;
            $location->endAt = $endAt;
            $location->status = $status;
            $location->customer = $this->getReference('customer_'.random_int(1, CustomerFixtures::COUNT), Customer::class);

            $materials = $this->pickMaterials($status, $bookableIndices);
            $totalPrice = '0.00';
            $days = max(1, $endAt->diff($startAt)->days);

            foreach ($materials as $index) {
                $material = $this->getReference('material_'.$index, Material::class);
                $location->addMaterial($material);
                $totalPrice = bcadd($totalPrice, bcmul($material->dailyPrice, (string) $days, 2), 2);

                if (LocationStatusEnum::IN_PROGRESS === $status) {
                    $material->status = MaterialStatusEnum::RENTED;
                    $this->availableNowPool = array_values(array_diff($this->availableNowPool, [$index]));
                }
            }

            $location->totalPrice = $totalPrice;

            $manager->persist($location);

            if (0 === $i % 100) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

    private function resolveStatus(
        \DateTimeImmutable $startAt,
        \DateTimeImmutable $endAt,
        \DateTimeImmutable $now,
    ): LocationStatusEnum {
        if ($endAt < $now) {
            return $this->weightedChoice([
                LocationStatusEnum::FINISHED->value => 85,
                LocationStatusEnum::CANCELLED->value => 15,
            ]);
        }

        if ($startAt > $now) {
            return $this->weightedChoice([
                LocationStatusEnum::DRAFT->value => 35,
                LocationStatusEnum::CONFIRMED->value => 55,
                LocationStatusEnum::CANCELLED->value => 10,
            ]);
        }

        // $now falls within [startAt, endAt]: only IN_PROGRESS reflects an ongoing rental,
        // a CANCELLED rental never actually starts even if its period covers today.
        return $this->weightedChoice([
            LocationStatusEnum::IN_PROGRESS->value => 90,
            LocationStatusEnum::CANCELLED->value => 10,
        ]);
    }

    /**
     * @param list<int> $bookableIndices
     *
     * @return list<int>
     */
    private function pickMaterials(LocationStatusEnum $status, array $bookableIndices): array
    {
        $pool = LocationStatusEnum::IN_PROGRESS === $status ? $this->availableNowPool : $bookableIndices;
        if ([] === $pool) {
            return [];
        }

        $count = min(\count($pool), random_int(1, 8));
        $keys = (array) array_rand($pool, $count);

        return array_map(static fn (int $key): int => $pool[$key], $keys);
    }

    /**
     * @param array<string, int> $weights
     */
    private function weightedChoice(array $weights): LocationStatusEnum
    {
        $expanded = [];
        foreach ($weights as $value => $weight) {
            $expanded = [...$expanded, ...array_fill(0, $weight, $value)];
        }

        return LocationStatusEnum::from($this->faker->randomElement($expanded));
    }

    private function randomDateBetween(\DateTimeImmutable $start, \DateTimeImmutable $end): \DateTimeImmutable
    {
        $timestamp = random_int($start->getTimestamp(), $end->getTimestamp());

        return (new \DateTimeImmutable())->setTimestamp($timestamp);
    }
}
