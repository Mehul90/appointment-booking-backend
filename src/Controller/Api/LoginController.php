<?php

namespace App\Controller\Api;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LoginController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $jwtManager;
    private $validator;
    private $tokenStorage;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = $request->request->all();
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse([
                    'message' => 'Email and password are required'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Check if user already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse([
                    'message' => 'User with this email already exists'
                ], JsonResponse::HTTP_CONFLICT);
            }

            // Create new user
            $user = new User();
            $user->setEmail($data['email']);
            $user->setRoles(['ROLE_USER']);
            
            // Hash the password
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $data['password']
            );
            $user->setPassword($hashedPassword);

            // Validate the user entity
            $errors = $this->validator->validate($user);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            // Save the user
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Generate JWT token
            $token = $this->jwtManager->create($user);

            return new JsonResponse([
                'message' => 'User registered successfully',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles()
                ]
            ], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred during registration',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['email']) || !isset($data['password'])) {
                return new JsonResponse([
                    'message' => 'Email and password are required'
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
                return new JsonResponse([
                    'message' => 'Invalid credentials'
                ], JsonResponse::HTTP_UNAUTHORIZED);
            }

            $token = $this->jwtManager->create($user);

            return new JsonResponse([
                'message' => 'Login successful',
                'token' => $token,
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles()
                ]
            ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/token/refresh', name: 'api_token_refresh', methods: ['POST'])]
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                throw new AuthenticationException('User not found');
            }

            $token = $this->jwtManager->create($user);

            return new JsonResponse([
                'message' => 'Token refreshed successfully',
                'token' => $token
            ], JsonResponse::HTTP_OK);
        } catch (AuthenticationException $e) {
            return new JsonResponse([
                'message' => 'Invalid refresh token',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while refreshing token',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
