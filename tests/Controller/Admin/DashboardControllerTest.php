<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\LocationFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardControllerTest extends WebTestCase
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
        $this->client->request('GET', '/admin');

        self::assertResponseRedirects('/');
    }

    public function testAUserWithRoleManagerCanAccessTheDashboard(): void
    {
        $manager = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'manager@greenpro.fr']);

        $this->client->loginUser($manager);
        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testTheDashboardDisplaysStatsComputedFromTheFixtures(): void
    {
        $manager = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'manager@greenpro.fr']);

        $this->client->loginUser($manager);
        $crawler = $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSame('2', trim($crawler->filter('.card')->eq(0)->filter('.fs-3')->text()));
        self::assertSame('3', trim($crawler->filter('.card')->eq(1)->filter('.fs-3')->text()));
    }

    public function testAUserWithRoleAdminCanAccessTheDashboardThroughTheRoleHierarchy(): void
    {
        $admin = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@greenpro.fr']);

        $this->client->loginUser($admin);
        $this->client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
    }
}
