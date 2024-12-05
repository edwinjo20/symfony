<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/users', name: 'get_users', methods: ['GET'])]
    public function getAllUsers(): JsonResponse
    {
        $user = $this->getUser();
        dump($user->getRoles()); // Ensure the user has ROLE_USER
        return $this->json(['message' => 'Access successful']);
    }
    
    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getUserById(User $user): JsonResponse
    {
        return $this->json($user);
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
    
        if (!$data['username'] || !$data['email'] || !$data['password']) {
            return $this->json(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }
    
        // Log the incoming data
        file_put_contents('php://stderr', print_r($data, true));
    
        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);
    
        $entityManager->persist($user);
        $entityManager->flush();
    
        return $this->json($user, Response::HTTP_CREATED);
    }
    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $entityManager->flush();

        return $this->json($user);
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        throw new \Exception('The JWT authentication system should handle this request.');
    }
    
}
