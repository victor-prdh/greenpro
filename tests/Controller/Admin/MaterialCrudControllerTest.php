<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Repository\MaterialRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\MaterialCurrentlyRentedFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Fixtures\MaterialPaginationFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MaterialCrudControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class, MaterialFixtures::class]);
    }

    public function testAnAnonymousVisitorIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/materials');

        self::assertResponseRedirects('/');
    }

    public function testAManagerCanListMaterials(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/materials');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Tondeuse autoportée Husqvarna', $crawler->filter('table')->text());
    }

    public function testAManagerSeesOnlyThirtyMaterialsOnTheFirstPageAndTheRestOnTheSecondPage(): void
    {
        $this->loadFixtures([UserFixtures::class, MaterialPaginationFixtures::class]);
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));

        $firstPage = $this->client->request('GET', '/admin/materials');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Matériel 01', $firstPage->filter('table')->text());
        self::assertStringNotContainsString('Matériel 31', $firstPage->filter('table')->text());

        $secondPage = $this->client->request('GET', '/admin/materials?page=2');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Matériel 31', $secondPage->filter('table')->text());
        self::assertStringNotContainsString('Matériel 01', $secondPage->filter('table')->text());
    }

    public function testAManagerCanCreateAMaterial(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/materials/new');

        $form = $crawler->selectButton('Enregistrer')->form([
            'material[name]' => 'Taille-haie électrique',
            'material[reference]' => 'MAT-TEST-999',
            'material[dailyPrice]' => '15',
            'material[status]' => 'available',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/materials');
        self::assertNotNull(static::getContainer()->get(MaterialRepository::class)->findOneBy(['reference' => 'MAT-TEST-999']));
    }

    public function testAManagerCanDeleteAMaterial(): void
    {
        $material = static::getContainer()->get(MaterialRepository::class)->findOneBy(['reference' => 'MAT-TEST-001']);

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/materials');

        $form = $crawler->filter(\sprintf('form[action="/admin/materials/%s/delete"]', $material->uuid))->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/materials');
        self::assertNull(static::getContainer()->get(MaterialRepository::class)->findOneBy(['reference' => 'MAT-TEST-001']));
    }

    public function testAnAnonymousVisitorIsRedirectedToLoginOnAnomalies(): void
    {
        $this->client->request('GET', '/admin/materials/anomalies');

        self::assertResponseRedirects('/');
    }

    public function testAManagerCanListAnomalies(): void
    {
        $this->loadFixtures([UserFixtures::class, CustomerFixtures::class, MaterialCurrentlyRentedFixtures::class]);

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/materials/anomalies');

        self::assertResponseIsSuccessful();

        $tableText = $crawler->filter('table')->text();
        self::assertStringContainsString('Bétonnière Altrad', $tableText);
        self::assertStringContainsString('Perforateur Hilti', $tableText);
        self::assertSame(2, $crawler->filter('table tbody tr')->count());
    }

    private function getUser(string $email): \App\Entity\User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }
}
