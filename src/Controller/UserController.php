<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager, 
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = new Utilisateur();
        // $user->setFirstName($data['firstName'] ?? '');
        // $user->setLastName($data['lastName'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setPassword($passwordHasher->hashPassword($user, $data['password'] ?? ''));

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new JsonResponse(['errors' => $errorsString], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'User registered successfully'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(
        #[CurrentUser] ?Utilisateur $user,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        Request $request
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return new JsonResponse(['message' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/user', name: 'api_user', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not found'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'email' => $user->getUserIdentifier()
        ]);
    }
}
