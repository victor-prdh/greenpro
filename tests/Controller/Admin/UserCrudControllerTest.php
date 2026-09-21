<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserCrudControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class]);
    }

    public function testAManagerCannotAccessTheUserList(): void
    {
        $manager = $this->getUser('manager@greenpro.fr');

        $this->client->loginUser($manager);
        $this->client->request('GET', '/admin/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnAdminCanListUsers(): void
    {
        $admin = $this->getUser('admin@greenpro.fr');

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/users');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('admin@greenpro.fr', $crawler->filter('table')->text());
        self::assertStringContainsString('manager@greenpro.fr', $crawler->filter('table')->text());
    }

    public function testAnAdminCanCreateAUser(): void
    {
        $admin = $this->getUser('admin@greenpro.fr');

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/users/new');

        $form = $crawler->selectButton('Enregistrer')->form([
            'user[email]' => 'new-user@greenpro.fr',
            'user[roleEnums][2]' => true,
            'user[plainPassword]' => 'a-strong-password',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/users');

        $created = $this->getUser('new-user@greenpro.fr');
        self::assertNotNull($created);
        self::assertContains('ROLE_MANAGER', $created->getRoles());
    }

    public function testAnAdminCanEditAUsersRolesWithoutChangingThePassword(): void
    {
        $admin = $this->getUser('admin@greenpro.fr');
        $manager = $this->getUser('manager@greenpro.fr');
        $originalHash = $manager->getPassword();

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', \sprintf('/admin/users/%s/edit', $manager->uuid));

        $form = $crawler->selectButton('Enregistrer')->form([
            'user[roleEnums][1]' => true,
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/users');

        $updated = $this->getUser('manager@greenpro.fr');
        self::assertContains('ROLE_ADMIN', $updated->getRoles());
        self::assertSame($originalHash, $updated->getPassword());
    }

    public function testAnAdminCanDeleteAUser(): void
    {
        $admin = $this->getUser('admin@greenpro.fr');
        $manager = $this->getUser('manager@greenpro.fr');

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/users');

        $form = $crawler->filter(\sprintf('form[action="/admin/users/%s/delete"]', $manager->uuid))->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/users');
        self::assertNull($this->getUser('manager@greenpro.fr'));
    }

    private function getUser(string $email): ?\App\Entity\User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }
}
