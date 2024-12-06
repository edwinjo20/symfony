<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'get_users', methods: ['GET'])]

    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users);
    }
    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]

    public function show(UserRepository $userRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }
        return $this->json($user);
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse{

    
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setEmail($data['email']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        
        $user->setPassword($hashedPassword);        $user->setRoles($data['roles']);
    
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['status'=>'User created'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function update(EntityManagerInterface $entityManager, Request $request, User $user , UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if ($data) {
            // Mettre à jour les champs de l'utilisateur
            if (isset($data['firstName'])) {
                $user->setFirstName($data['firstName']);
            }
            if (isset($data['lastName'])) {
                $user->setLastName($data['lastName']);
            }
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['password'])) {
                // Hachage du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
                $user->setPassword($hashedPassword);
            }
            if (isset($data['roles'])) {
                $user->setRoles($data['roles']);
            }

            // Sauvegarder les modifications
            $entityManager->flush();

            return $this->json($user);
        }

        return $this->json(['message' => 'Invalid data'], 400);
    }


    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, User $user): JsonResponse
    {
        // Supprimer l'utilisateur
        $entityManager->remove($user);
        $entityManager->flush();
        
        return $this->json(['status' => 'User deleted']);
    }


    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        throw new \Exception('The JWT authentication system should handle this request.');
    }
}
