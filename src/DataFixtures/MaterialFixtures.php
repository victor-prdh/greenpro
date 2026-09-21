<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class MaterialFixtures extends Fixture
{
    public const int COUNT = 5000;

    /**
     * Distribution of material statuses that do not depend on a location assignment.
     * RENTED is assigned later, by LocationFixtures, once materials are actually
     * attached to an in-progress location.
     */
    private const array BASE_STATUS_WEIGHTS = [
        MaterialStatusEnum::AVAILABLE->value => 88,
        MaterialStatusEnum::MAINTENANCE->value => 7,
        MaterialStatusEnum::RETIRED->value => 5,
    ];

    private const array CATEGORIES = [
        'Tondeuse autoportée', 'Tondeuse thermique', 'Débroussailleuse', 'Taille-haie',
        'Tronçonneuse', 'Broyeur de végétaux', 'Motoculteur', 'Motobineuse',
        'Fendeuse à bûches', 'Souffleur de feuilles', 'Scarificateur', 'Aérateur de pelouse',
        'Nettoyeur haute pression', 'Groupe électrogène', 'Compresseur d\'air',
        'Bétonnière', 'Plaque vibrante', 'Pilonneuse', 'Perforateur',
        'Nacelle élévatrice', 'Échafaudage roulant', 'Échelle télescopique',
        'Remorque benne', 'Chapiteau de réception', 'Tente pliante', 'Table pliante',
        'Chaise pliante', 'Barnum', 'Groupe froid', 'Chauffage d\'appoint',
        'Palan électrique', 'Diable de manutention', 'Transpalette',
    ];

    private const array BRANDS = [
        'Husqvarna', 'Stihl', 'Honda', 'Bosch', 'Makita', 'Kärcher', 'Stanley',
        'Manitou', 'Haulotte', 'SDMO', 'Wacker Neuson', 'Altrad',
    ];

    private Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::COUNT; ++$i) {
            $material = new Material();
            $category = $this->faker->randomElement(self::CATEGORIES);
            $material->name = \sprintf('%s %s', $this->faker->randomElement(self::BRANDS), $category);
            $material->reference = \sprintf('MAT-%05d', $i);
            $material->dailyPrice = number_format($this->faker->randomFloat(2, 8, 650), 2, '.', '');
            $material->status = MaterialStatusEnum::from($this->faker->randomElement(
                $this->expandWeights(self::BASE_STATUS_WEIGHTS),
            ));

            $manager->persist($material);
            $this->addReference('material_'.$i, $material);

            if (0 === $i % 250) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

    /**
     * @param array<string, int> $weights
     *
     * @return list<string>
     */
    private function expandWeights(array $weights): array
    {
        $expanded = [];
        foreach ($weights as $value => $weight) {
            $expanded = [...$expanded, ...array_fill(0, $weight, $value)];
        }

        return $expanded;
    }
}
