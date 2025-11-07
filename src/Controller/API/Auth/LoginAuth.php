<?php

namespace App\Controller\API\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Connection;

class LoginAuth extends AbstractController
{
    #[Route('/api/login-auth', name: "login-auth", methods: ['POST'])]
    public function doGetUser(Request $req, Connection $connection): JsonResponse
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

            $user = $connection->fetchAssociative(
                "SELECT patient_id AS id, username, first_name, last_name, role, password, 'patient' AS user_type
                 FROM patient WHERE username = ?",
                [$username]
            );

            if (!$user) {
                $user = $connection->fetchAssociative(
                    "SELECT dentistID AS id, username,name, experience, password,specialty,email, 'dentist' AS user_type
                     FROM dentist WHERE username = ?",
                    [$username]
                );
            }

            if (!$user) {
                return new JsonResponse([
                    'status' => "error",
                    'message' => "No user found with that username."
                ], 401);
            }

            if ($password !== $user['password']) {
                return new JsonResponse([
                    'status' => "error",
                    'message' => "Incorrect username or password."
                ], 401);
            }

            return new JsonResponse([
                'status' => 'ok',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    // Handle name fields depending on user type
                    'firstName' => $user['user_type'] === 'patient' ? $user['first_name'] : $user['name'],
                    'lastName' => $user['user_type'] === 'patient' ? $user['last_name'] : null,
                    'email' => $user['email'] ?? null,
                    'experience' => $user['experience'] ?? null,
                    'specialty' => $user['specialty'] ?? null,
                    // use "role" consistently for frontend (always 'patient' or 'dentist')
                    'role' => $user['user_type'],
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
