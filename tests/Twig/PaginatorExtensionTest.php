<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Entity\Material;
use App\Pagination\Pagination;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginatorExtensionTest extends KernelTestCase
{
    private Environment $twig;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->twig = static::getContainer()->get(Environment::class);

        $request = Request::create('/admin/materials', 'GET', ['status' => 'available']);
        $request->attributes->set('_route', 'app_admin_material_index');
        $request->attributes->set('_route_params', []);

        static::getContainer()->get(RequestStack::class)->push($request);
    }

    public function testPaginatorNavRendersFirstLastAndAWindowOfSiblingsAroundTheCurrentPageWhenInTheMiddle(): void
    {
        $pagination = new Pagination(items: [], page: 5, limit: 10, totalItems: 200, totalPages: 20);

        $html = $this->twig->createTemplate('{{ paginator_nav(pagination) }}')->render(['pagination' => $pagination]);

        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=1">1</a>#', $html);
        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=4">4</a>#', $html);
        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=6">6</a>#', $html);
        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=20">20</a>#', $html);
        self::assertSame(2, substr_count($html, '…'));
        self::assertSame(1, substr_count($html, 'page-item active'));
    }

    public function testPaginatorNavOmitsTheLeadingEllipsisAndDuplicateFirstPageLinkOnTheFirstPage(): void
    {
        $pagination = new Pagination(items: [], page: 1, limit: 10, totalItems: 200, totalPages: 20);

        $html = $this->twig->createTemplate('{{ paginator_nav(pagination) }}')->render(['pagination' => $pagination]);

        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=2">2</a>#', $html);
        self::assertSame(1, substr_count($html, '…'));
        self::assertSame(1, substr_count($html, 'page-item active'));
        self::assertStringContainsString('page-item disabled', $html);
    }

    public function testPaginatorNavOmitsTheTrailingEllipsisAndDuplicateLastPageLinkOnTheLastPage(): void
    {
        $pagination = new Pagination(items: [], page: 20, limit: 10, totalItems: 200, totalPages: 20);

        $html = $this->twig->createTemplate('{{ paginator_nav(pagination) }}')->render(['pagination' => $pagination]);

        self::assertMatchesRegularExpression('#<a class="page-link" href="[^"]*page=19">19</a>#', $html);
        self::assertSame(1, substr_count($html, '…'));
        self::assertSame(1, substr_count($html, 'page-item active'));
    }

    public function testPaginatorNavShowsNoEllipsisAndNoDuplicateLinksWhenThereAreFewPages(): void
    {
        $pagination = new Pagination(items: [], page: 2, limit: 10, totalItems: 30, totalPages: 3);

        $html = $this->twig->createTemplate('{{ paginator_nav(pagination) }}')->render(['pagination' => $pagination]);

        self::assertSame(0, substr_count($html, '…'));
        self::assertSame(1, substr_count($html, 'page-item active'));
        self::assertSame(1, substr_count($html, '>1</a>'));
        self::assertSame(1, substr_count($html, '>3</a>'));
    }

    public function testPaginatorNavRendersNothingWhenThereIsOnlyOnePage(): void
    {
        $pagination = new Pagination(items: [], page: 1, limit: 30, totalItems: 3, totalPages: 1);

        $html = $this->twig->createTemplate('{{ paginator_nav(pagination) }}')->render(['pagination' => $pagination]);

        self::assertSame('', trim($html));
    }

    public function testPaginatorTableRendersHeadersAndRowsFromColumnsConfiguration(): void
    {
        $husqvarna = new Material();
        $husqvarna->name = 'Tondeuse autoportée Husqvarna';
        $husqvarna->reference = 'MAT-TEST-001';

        $karcher = new Material();
        $karcher->name = 'Nettoyeur haute pression Kärcher';
        $karcher->reference = 'MAT-TEST-002';

        $pagination = new Pagination(items: [$husqvarna, $karcher], page: 1, limit: 30, totalItems: 2, totalPages: 1);
        $columns = [
            ['label' => 'Nom', 'field' => 'name'],
            ['label' => 'Référence', 'field' => 'reference'],
        ];

        $html = $this->twig->createTemplate('{{ paginator_table(pagination, columns) }}')
            ->render(['pagination' => $pagination, 'columns' => $columns]);

        self::assertStringContainsString('<th>Nom</th>', $html);
        self::assertStringContainsString('<th>Référence</th>', $html);
        self::assertStringContainsString('Tondeuse autoportée Husqvarna', $html);
        self::assertStringContainsString('MAT-TEST-002', $html);
    }
}
