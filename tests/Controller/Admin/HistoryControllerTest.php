<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Entity\Enum\HistoryTypeEnum;
use App\Entity\History;
use App\Repository\UserRepository;
use App\Tests\Support\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HistoryControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $history = new History();
        $history->message = 'Connexion de test@greenpro.fr';
        $history->type = HistoryTypeEnum::OTHER;

        $entityManager->persist($history);
        $entityManager->flush();
    }

    public function testAnAnonymousVisitorIsRedirectedToLogin(): void
    {
        $this->client->request('GET', '/admin/history');

        self::assertResponseRedirects('/');
    }

    public function testAManagerCannotAccessTheHistory(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $this->client->request('GET', '/admin/history');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnAdminCanListHistoryEntries(): void
    {
        $this->client->loginUser($this->getUser('admin@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/history');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Connexion de test@greenpro.fr', $crawler->filter('table')->text());
    }

    public function testAnAdminCanFilterHistoryEntriesByType(): void
    {
        $this->client->loginUser($this->getUser('admin@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/history', ['type' => 'other']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Connexion de test@greenpro.fr', $crawler->filter('table')->text());
    }

    private function getUser(string $email): \App\Entity\User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }
}
