<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Repository\CustomerRepository;
use App\Repository\LocationRepository;
use App\Repository\MaterialRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\LocationAnomalyFixtures;
use App\Tests\Fixtures\LocationFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LocationCrudControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class, CustomerFixtures::class, MaterialFixtures::class, LocationFixtures::class]);
    }

    public function testAnAnonymousVisitorIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/locations');

        self::assertResponseRedirects('/');
    }

    public function testAManagerCanListLocations(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations', ['period' => 'all']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Jardins Alpha', $crawler->filter('table')->text());
    }

    public function testTheCurrentPeriodFilterIsActiveByDefault(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations');

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Jardins Alpha', $crawler->filter('table')->text());
    }

    public function testAManagerCanFilterLocationsByStatus(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations', ['period' => 'all', 'status' => 'confirmed']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Jardins Alpha', $crawler->filter('table')->text());

        $crawler = $this->client->request('GET', '/admin/locations', ['period' => 'all', 'status' => 'cancelled']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Aucune location', $crawler->filter('table')->text());
    }

    public function testAManagerCanCreateALocation(): void
    {
        $customer = static::getContainer()->get(CustomerRepository::class)->findOneBy(['email' => 'bruno@espaces-bravo.test']);
        $material = static::getContainer()->get(MaterialRepository::class)->findOneBy(['reference' => 'MAT-TEST-001']);

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations/new');

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['location[startAt]'] = '2026-11-01T09:00';
        $form['location[endAt]'] = '2026-11-05T18:00';
        $form['location[status]'] = 'draft';
        $form['location[totalPrice]'] = '180';
        $form['location[customer]'] = (string) $customer->uuid;

        foreach ($form['location[materials]'] as $materialField) {
            if (\in_array((string) $material->uuid, $materialField->availableOptionValues(), true)) {
                $materialField->tick();
            }
        }

        $this->client->submit($form);

        self::assertResponseRedirects('/admin/locations');
        self::assertCount(2, static::getContainer()->get(LocationRepository::class)->findAll());
    }

    public function testAManagerCanDeleteALocation(): void
    {
        $location = static::getContainer()->get(LocationRepository::class)->findAll()[0];

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations', ['period' => 'all']);

        $form = $crawler->filter(\sprintf('form[action="/admin/locations/%s/delete"]', $location->uuid))->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/locations');
        self::assertCount(0, static::getContainer()->get(LocationRepository::class)->findAll());
    }

    public function testAnAnonymousVisitorIsRedirectedToLoginOnAnomalies(): void
    {
        $this->client->request('GET', '/admin/locations/anomalies');

        self::assertResponseRedirects('/');
    }

    public function testAManagerCanListAnomalies(): void
    {
        $this->loadFixtures([UserFixtures::class, CustomerFixtures::class, MaterialFixtures::class, LocationAnomalyFixtures::class]);

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/locations/anomalies');

        self::assertResponseIsSuccessful();

        $tableText = $crawler->filter('table')->text();
        self::assertStringContainsString('Jardins Alpha', $tableText);
        self::assertStringContainsString('Espaces Verts Bravo', $tableText);
        self::assertSame(2, $crawler->filter('table tbody tr')->count());
    }

    private function getUser(string $email): \App\Entity\User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }
}
