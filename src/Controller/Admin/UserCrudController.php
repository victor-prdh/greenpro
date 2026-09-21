<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Pagination\Paginator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/users', name: 'app_admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractController
{
    #[Route(path: '', name: 'index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, Paginator $paginator): Response
    {
        $queryBuilder = $userRepository->createQueryBuilder('u')->orderBy('u.email', 'ASC');

        return $this->render('admin/user/index.html.twig', [
            'pagination' => $paginator->paginate($queryBuilder, $request),
        ]);
    }

    #[Route(path: '/{uuid}', name: 'show', methods: ['GET'], requirements: ['uuid' => Requirement::UUID])]
    public function show(#[MapEntity(id: 'uuid')] User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles($form->get('roleEnums')->getData());
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['uuid' => Requirement::UUID])]
    public function edit(Request $request, #[MapEntity(id: 'uuid')] User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->get('roleEnums')->setData($user->getRoleEnums());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles($form->get('roleEnums')->getData());

            $plainPassword = $form->get('plainPassword')->getData();
            if (null !== $plainPassword && '' !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur modifié.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{uuid}/delete', name: 'delete', methods: ['POST'], requirements: ['uuid' => Requirement::UUID])]
    public function delete(Request $request, #[MapEntity(id: 'uuid')] User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete-user-'.$user->uuid, $request->request->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }
}
