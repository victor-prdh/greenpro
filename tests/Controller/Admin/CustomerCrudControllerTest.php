<?php

declare(strict_types=1);

namespace App\Tests\Controller\Admin;

use App\DataFixtures\UserFixtures;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\CustomerPaginationFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerCrudControllerTest extends WebTestCase
{
    use FixturesTrait;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->loadFixtures([UserFixtures::class, CustomerFixtures::class]);
    }

    public function testAManagerCanListCustomers(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/customers');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Jardins Alpha', $crawler->filter('table')->text());
    }

    public function testAManagerSeesOnlyThirtyCustomersOnTheFirstPageAndTheRestOnTheSecondPage(): void
    {
        $this->loadFixtures([UserFixtures::class, CustomerPaginationFixtures::class]);
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));

        $firstPage = $this->client->request('GET', '/admin/customers');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Société 01', $firstPage->filter('table')->text());
        self::assertStringNotContainsString('Société 31', $firstPage->filter('table')->text());

        $secondPage = $this->client->request('GET', '/admin/customers?page=2');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Société 31', $secondPage->filter('table')->text());
        self::assertStringNotContainsString('Société 01', $secondPage->filter('table')->text());
    }

    public function testAManagerCannotSeeTheCreateButtonOrAccessTheNewForm(): void
    {
        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/customers');

        self::assertCount(0, $crawler->selectLink('Nouveau client'));

        $this->client->request('GET', '/admin/customers/new');
        self::assertResponseStatusCodeSame(403);
    }

    public function testAManagerCannotDeleteACustomer(): void
    {
        $customer = static::getContainer()->get(CustomerRepository::class)->findOneBy(['email' => 'alice@jardins-alpha.test']);

        $this->client->loginUser($this->getUser('manager@greenpro.fr'));
        $this->client->request('POST', \sprintf('/admin/customers/%s/delete', $customer->uuid));

        self::assertResponseStatusCodeSame(403);
    }

    public function testAnAdminCanCreateACustomer(): void
    {
        $this->client->loginUser($this->getUser('admin@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/customers/new');

        $form = $crawler->selectButton('Enregistrer')->form([
            'customer[companyName]' => 'Nouveau Client SARL',
            'customer[contactName]' => 'Claire Nouveau',
            'customer[email]' => 'claire@nouveau-client.test',
            'customer[phone]' => '0600000000',
            'customer[address]' => '10 rue Neuve',
            'customer[postalCode]' => '75002',
            'customer[city]' => 'Paris',
        ]);
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/customers');
        self::assertNotNull(static::getContainer()->get(CustomerRepository::class)->findOneBy(['email' => 'claire@nouveau-client.test']));
    }

    public function testAnAdminCanDeleteACustomer(): void
    {
        $customer = static::getContainer()->get(CustomerRepository::class)->findOneBy(['email' => 'alice@jardins-alpha.test']);

        $this->client->loginUser($this->getUser('admin@greenpro.fr'));
        $crawler = $this->client->request('GET', '/admin/customers');

        $form = $crawler->filter(\sprintf('form[action="/admin/customers/%s/delete"]', $customer->uuid))->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/admin/customers');
        self::assertNull(static::getContainer()->get(CustomerRepository::class)->findOneBy(['email' => 'alice@jardins-alpha.test']));
    }

    private function getUser(string $email): \App\Entity\User
    {
        return static::getContainer()->get(UserRepository::class)->findOneBy(['email' => $email]);
    }
}
