<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Customer;
use App\Form\CustomerType;
use App\Pagination\Paginator;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/customers', name: 'app_admin_customer_')]
#[IsGranted('ROLE_MANAGER')]
class CustomerCrudController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, CustomerRepository $customerRepository, Paginator $paginator): Response
    {
        $queryBuilder = $customerRepository->createQueryBuilder('c')->orderBy('c.companyName', 'ASC');

        return $this->render('admin/customer/index.html.twig', [
            'pagination' => $paginator->paginate($queryBuilder, $request),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'show', methods: ['GET'], requirements: ['uuid' => Requirement::UUID])]
    public function show(#[MapEntity(id: 'uuid')] Customer $customer): Response
    {
        return $this->render('admin/customer/show.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($customer);
            $entityManager->flush();

            $this->addFlash('success', 'Client créé.');

            return $this->redirectToRoute('app_admin_customer_index');
        }

        return $this->render('admin/customer/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => Requirement::UUID])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, #[MapEntity(id: 'uuid')] Customer $customer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Client modifié.');

            return $this->redirectToRoute('app_admin_customer_index');
        }

        return $this->render('admin/customer/edit.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/delete', name: 'delete', methods: ['POST'], requirements: ['uuid' => Requirement::UUID])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, #[MapEntity(id: 'uuid')] Customer $customer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-customer-'.$customer->uuid, $request->request->getString('_token'))) {
            $entityManager->remove($customer);
            $entityManager->flush();

            $this->addFlash('success', 'Client supprimé.');
        }

        return $this->redirectToRoute('app_admin_customer_index');
    }
}
