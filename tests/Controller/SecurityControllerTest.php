<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserFixtures;
use App\Entity\Enum\HistoryTypeEnum;
use App\Repository\HistoryRepository;
use App\Repository\UserRepository;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
    }

    public function testLoginPageIsAccessibleToAnAnonymousVisitor(): void
    {
        $client = $this->client;

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testSubmittingWrongCredentialsRedisplaysTheFormWithAnError(): void
    {
        $client = $this->client;
        $client->request('GET', '/');

        $client->submitForm('Connexion', [
            '_username' => 'admin@greenpro.fr',
            '_password' => 'wrong-password',
        ]);

        self::assertResponseRedirects('/');
        $client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Identifiants invalides');
    }

    public function testSubmittingValidCredentialsLogsInAndRedirectsToTheDashboard(): void
    {
        $client = $this->client;
        $client->followRedirects();
        $client->request('GET', '/');

        $client->submitForm('Connexion', [
            '_username' => 'admin@greenpro.fr',
            '_password' => 'greenpro',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');

        $history = static::getContainer()->get(HistoryRepository::class)
            ->findOneBy(['message' => 'Connexion de admin@greenpro.fr']);
        self::assertNotNull($history);
        self::assertSame(HistoryTypeEnum::LOGIN, $history->type);
        self::assertSame('admin@greenpro.fr', $history->author?->email);
    }

    public function testAnAlreadyAuthenticatedUserIsRedirectedAwayFromTheLoginPage(): void
    {
        $client = $this->client;
        $userRepository = static::getContainer()->get(UserRepository::class);
        $admin = $userRepository->findOneBy(['email' => 'admin@greenpro.fr']);

        $client->loginUser($admin);
        $client->request('GET', '/');

        self::assertResponseRedirects('/admin');
    }
}
