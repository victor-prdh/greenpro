<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Material;
use App\Form\MaterialType;
use App\Pagination\Paginator;
use App\Repository\LocationRepository;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/materials', name: 'app_admin_material_')]
#[IsGranted('ROLE_MANAGER')]
class MaterialCrudController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, MaterialRepository $materialRepository, Paginator $paginator): Response
    {
        $onlyCurrentlyRented = $request->query->getBoolean('currentlyRented');

        $queryBuilder = $materialRepository->findFiltered($onlyCurrentlyRented);

        return $this->render('admin/material/index.html.twig', [
            'pagination' => $paginator->paginate($queryBuilder, $request),
            'currentlyRented' => $onlyCurrentlyRented,
        ]);
    }

    #[Route(path: '/anomalies', name: 'anomalies', methods: ['GET'])]
    public function anomalies(Request $request, MaterialRepository $materialRepository, Paginator $paginator): Response
    {
        return $this->render('admin/material/anomalies.html.twig', [
            'pagination' => $paginator->paginate($materialRepository->findAnomalies(), $request),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'show', methods: ['GET'], requirements: ['uuid' => Requirement::UUID])]
    public function show(#[MapEntity(id: 'uuid')] Material $material, LocationRepository $locationRepository): Response
    {
        return $this->render('admin/material/show.html.twig', [
            'material' => $material,
            'locations' => $locationRepository->findByMaterial($material),
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $material = new Material();
        $form = $this->createForm(MaterialType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($material);
            $entityManager->flush();

            $this->addFlash('success', 'Matériel créé.');

            return $this->redirectToRoute('app_admin_material_index');
        }

        return $this->render('admin/material/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => Requirement::UUID])]
    public function edit(Request $request, #[MapEntity(id: 'uuid')] Material $material, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaterialType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Matériel modifié.');

            return $this->redirectToRoute('app_admin_material_index');
        }

        return $this->render('admin/material/edit.html.twig', [
            'material' => $material,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/delete', name: 'delete', methods: ['POST'], requirements: ['uuid' => Requirement::UUID])]
    public function delete(Request $request, #[MapEntity(id: 'uuid')] Material $material, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-material-'.$material->uuid, $request->request->getString('_token'))) {
            $entityManager->remove($material);
            $entityManager->flush();

            $this->addFlash('success', 'Matériel supprimé.');
        }

        return $this->redirectToRoute('app_admin_material_index');
    }
}
