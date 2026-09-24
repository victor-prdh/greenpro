<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Enum\HistoryTypeEnum;
use App\Pagination\Paginator;
use App\Repository\HistoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/history', name: 'app_admin_history_')]
#[IsGranted('ROLE_ADMIN')]
class HistoryController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, HistoryRepository $historyRepository, Paginator $paginator): Response
    {
        $type = HistoryTypeEnum::tryFrom((string) $request->query->get('type'));

        return $this->render('admin/history/index.html.twig', [
            'pagination' => $paginator->paginate($historyRepository->findFiltered($type), $request),
            'types' => HistoryTypeEnum::cases(),
            'currentType' => $type,
        ]);
    }
}
