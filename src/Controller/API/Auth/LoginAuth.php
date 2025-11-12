<?php

namespace App\Controller\API\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class LoginAuth extends AbstractController
{
    #[Route('/api/login-auth', name: "login-auth", methods: ['POST'])]
    public function doGetUser(Request $req, Connection $connection, JWTTokenManagerInterface $jwtManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $userInput = json_decode($req->getContent(), true);
            $username = $userInput['username'] ?? null;
            $password = $userInput['password'] ?? null;

            if (!$username || !$password) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Username and password are required.'
                ], 400);
            }

            // Fetch user info from `user` table
            $user = $connection->fetchAssociative(
                "SELECT id, username, email, password, first_name, last_name 
                 FROM user 
                 WHERE username = ?",
                [$username]
            );

            if (!$user) {
                return new JsonResponse([
                    'status' => "error",
                    'message' => "No user found with that username."
                ], 401);
            }

            // Verify password with hasher
            // if (!$passwordHasher->isPasswordValid(new User(), $password, $user['password'])) {
            //     return new JsonResponse([
            //         'status' => "error",
            //         'message' => "Incorrect username or password."
            //     ], 401);
            // }
            
            // VERIFY PASS WITHOUT HASHER
            if ($password !== $user['password']) {
                        return new JsonResponse([
                            'status' => "error",
                            'message' => "Incorrect username or password."
            ], 401);
            }

            // Fetch roles from `user_role` join table
            $roles = $connection->fetchFirstColumn(
                "SELECT r.role_name 
                FROM role r
                INNER JOIN user_role ur ON r.id = ur.role_id
                WHERE ur.user_id = ?",
                [$user['id']]
            );

            // Symfony User object for JWT
            $symfonyUser = new User($user['username'], null, $roles);
            $token = $jwtManager->create($symfonyUser);

            return new JsonResponse([
                'status' => 'ok',
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'firstName' => $user['first_name'],
                    'lastName' => $user['last_name'],
                    'email' => $user['email'],
                    'roles' => $roles
                ]
            ], 200);

        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
