<?php
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\DBAL\Connection;

class GetUserInfo extends AbstractController 
{
    #[Route('/api/get-user-info', name:'get-user-info', methods:['POST'])]
    public function getUserInfo(Request $req, Connection $conn): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'User ID is required.'
            ], 400);
        }

        // Fetch user info from `user` table
        $userInfo = $conn->fetchAssociative(
            "SELECT id, username, first_name, last_name, email, created_at 
             FROM user 
             WHERE id = ?",
            [$id]
        );

        if (!$userInfo) {
            return new JsonResponse([
                'status' => "error",
                'message' => "No user found."
            ], 404);
        }

        // Fetch roles from `user_role` join table
        $roles = $conn->fetchFirstColumn(
            "SELECT r.role_name 
             FROM role r
             INNER JOIN user_role ur ON r.id = ur.role_id
             WHERE ur.user_id = ?",
            [$id]
        );

        // Return user info along with roles
        return new JsonResponse([
            'status' => 'ok',
            'user' => [
                'id' => $userInfo['id'],
                'username' => $userInfo['username'],
                'firstName' => $userInfo['first_name'],
                'lastName' => $userInfo['last_name'],
                'email' => $userInfo['email'],
                'createdAt' => $userInfo['created_at'],
                'roles' => $roles
            ]
        ]);
    }
}
