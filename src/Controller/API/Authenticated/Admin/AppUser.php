<?php

namespace App\Controller\API\Authenticated\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppUser extends AbstractController
{
    #[Route('/api/get-users', name: "get-users", methods: ['GET'])]
    public function getUsers(Request $req, Connection $connection): JsonResponse
    {
       
        try {
            $users = $connection->fetchAllAssociative(
                'SELECT * from user'
            );
            
            return new JsonResponse([
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/get-user', name: "get-user", methods: ['POST'])]
    public function getOneUser(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
            $userID = $data['userID'];
            $user = $connection->fetchAssociative(
                'SELECT * from user where id = ?',
                [$userID]
            );
            
            return new JsonResponse([
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    
    #[Route('/api/update-user', name: "update-user", methods: ['POST'])]
    public function updateUser(Request $req, Connection $connection, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!$data || !isset($data['userID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing user ID',
                ], 400);
            }
    
            $userId = $data['userID'];
    
            // Prepare update data
            $updateData = [
                'first_name' => $data['first_name'] ?? null,
                'last_name'  => $data['last_name'] ?? null,
                'email'      => $data['email'] ?? null,
                'username'   => $data['username'] ?? null,
                'roles'      => $data['roles'] ?? '["ROLE_USER"]',
                'disable'    => isset($data['is_disabled']) ? (int)$data['is_disabled'] : 0,
            ];
    
            // Handle password change
            if (!empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
            }
    
            // Remove NULL values so fields remain unchanged
            $updateData = array_filter($updateData, fn($v) => $v !== null);
    
            // Update database
            $connection->update('user', $updateData, ['id' => $userId]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'User updated successfully',
                'updated' => $updateData
            ], 200);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
