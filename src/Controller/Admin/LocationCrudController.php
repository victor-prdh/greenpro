<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Location;
use App\Form\LocationType;
use App\Pagination\Paginator;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/locations', name: 'app_admin_location_')]
#[IsGranted('ROLE_MANAGER')]
class LocationCrudController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, LocationRepository $locationRepository, Paginator $paginator): Response
    {
        $status = LocationStatusEnum::tryFrom((string) $request->query->get('status'));
        $period = $request->query->getString('period', 'current');

        $queryBuilder = $locationRepository->findFiltered($status, 'current' === $period);

        return $this->render('admin/location/index.html.twig', [
            'pagination' => $paginator->paginate($queryBuilder, $request),
            'statuses' => LocationStatusEnum::cases(),
            'currentStatus' => $status,
            'currentPeriod' => $period,
        ]);
    }

    #[Route(path: '/anomalies', name: 'anomalies', methods: ['GET'])]
    public function anomalies(Request $request, LocationRepository $locationRepository, Paginator $paginator): Response
    {
        return $this->render('admin/location/anomalies.html.twig', [
            'pagination' => $paginator->paginate($locationRepository->findAnomalies(), $request),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'show', methods: ['GET'], requirements: ['uuid' => Requirement::UUID])]
    public function show(#[MapEntity(id: 'uuid')] Location $location): Response
    {
        return $this->render('admin/location/show.html.twig', [
            'location' => $location,
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Location();
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            $this->addFlash('success', 'Location créée.');

            return $this->redirectToRoute('app_admin_location_index');
        }

        return $this->render('admin/location/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => Requirement::UUID])]
    public function edit(Request $request, #[MapEntity(id: 'uuid')] Location $location, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LocationType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Location modifiée.');

            return $this->redirectToRoute('app_admin_location_index');
        }

        return $this->render('admin/location/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/delete', name: 'delete', methods: ['POST'], requirements: ['uuid' => Requirement::UUID])]
    public function delete(Request $request, #[MapEntity(id: 'uuid')] Location $location, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-location-'.$location->uuid, $request->request->getString('_token'))) {
            $entityManager->remove($location);
            $entityManager->flush();

            $this->addFlash('success', 'Location supprimée.');
        }

        return $this->redirectToRoute('app_admin_location_index');
    }
}
