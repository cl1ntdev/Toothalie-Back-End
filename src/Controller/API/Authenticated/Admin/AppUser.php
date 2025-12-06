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
    
    #[Route('/api/delete-user', name: "delete-user", methods: ['POST'])]
    public function deleteUserHard(Request $req, Connection $connection): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            if (!isset($data['userID'])) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Missing user ID'
                ], 400);
            }
    
            $userID = $data['userID'];
    
            // Check user exists
            $user = $connection->fetchAssociative(
                "SELECT * FROM user WHERE id = ?",
                [$userID]
            );
    
            if (!$user) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }
    
            // Hard delete
            $connection->delete('user', ['id' => $userID]);
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'User deleted permanently',
                'user_id' => $userID
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
    
    #[Route('/api/create-user', name: "create-user", methods: ['POST'])]
    public function createUser(Request $req, Connection $connection, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $data = json_decode($req->getContent(), true);
    
            // Validate required fields
            $required = ['first_name', 'last_name', 'email', 'username', 'password', 'roles'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return new JsonResponse([
                        'status' => 'error',
                        'message' => "Missing required field: $field"
                    ], 400);
                }
            }
    
            // Check for duplicate email
            $existingEmail = $connection->fetchOne(
                "SELECT id FROM user WHERE email = ?",
                [$data['email']]
            );
    
            if ($existingEmail) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "Email already exists"
                ], 409);
            }
    
            // Check for duplicate username
            $existingUsername = $connection->fetchOne(
                "SELECT id FROM user WHERE username = ?",
                [$data['username']]
            );
    
            if ($existingUsername) {
                return new JsonResponse([
                    'status' => 'error',
                    'message' => "Username already exists"
                ], 409);
            }
    
            // Prepare insert data
            $insertData = [
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'email'      => $data['email'],
                'username'   => $data['username'],
                'password'   => password_hash($data['password'], PASSWORD_BCRYPT),
                'roles'      => $data['roles'], // already a JSON string
                'disable'    => $data['disable'] ?? 0,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];
    
            // Insert into database
            $connection->insert('user', $insertData);
    
            // Get new ID
            $newUserId = $connection->lastInsertId();
    
            return new JsonResponse([
                'status' => 'success',
                'message' => 'User created successfully',
                'user_id' => $newUserId,
                'data' => $insertData
            ]);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
