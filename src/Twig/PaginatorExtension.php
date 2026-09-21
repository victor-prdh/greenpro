<?php

declare(strict_types=1);

namespace App\Twig;

use App\Pagination\Pagination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PaginatorExtension extends AbstractExtension
{
    public function __construct(
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('paginator_nav', $this->renderNav(...), ['is_safe' => ['html']]),
            new TwigFunction('paginator_table', $this->renderTable(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderNav(Pagination $pagination): string
    {
        return $this->twig->render('pagination/_nav.html.twig', [
            'pagination' => $pagination,
            'pageUrls' => $this->pageUrls($pagination),
        ]);
    }

    /**
     * @param list<array{label: string, field: string}> $columns
     */
    public function renderTable(Pagination $pagination, array $columns): string
    {
        return $this->twig->render('pagination/_table.html.twig', [
            'pagination' => $pagination,
            'columns' => $columns,
            'pageUrls' => $this->pageUrls($pagination),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function pageUrls(Pagination $pagination): array
    {
        $pages = array_unique(array_filter([
            1,
            $pagination->hasPrevious() ? $pagination->page - 1 : null,
            $pagination->page,
            $pagination->hasNext() ? $pagination->page + 1 : null,
            $pagination->totalPages,
        ], static fn (?int $page): bool => null !== $page));

        $urls = [];
        foreach ($pages as $page) {
            $urls[$page] = $this->urlForPage($page);
        }

        return $urls;
    }

    private function urlForPage(int $page): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return '';
        }

        $routeParams = $request->attributes->get('_route_params', []);
        $query = $request->query->all();
        unset($query['page']);

        return $this->urlGenerator->generate(
            $request->attributes->get('_route'),
            [...$routeParams, ...$query, 'page' => $page],
        );
    }
}
