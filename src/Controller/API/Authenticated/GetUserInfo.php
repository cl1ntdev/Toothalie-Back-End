<?php
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use App\Entity\User;

class GetUserInfo extends AbstractController 
{
    #[Route('/api/get-user-info', name: 'get-user-info', methods: ['GET'])]
    public function getUserInfo(Connection $conn): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        $id = $user->getId();

        // Fetch roles
        // $roles = $conn->fetchFirstColumn(
        //     "SELECT r.role_name 
        //      FROM role r
        //      INNER JOIN user_role ur ON r.id = ur.role_id
        //      WHERE ur.user_id = ?",
        //     [$id]
        // );
        
        $roles = $conn->fetchFirstColumn(
            "SELECT roles from user where id = ?",
            [$id]
        );


        return new JsonResponse([
            'status' => 'ok',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'password' => $user->getPassword(),
                'roles' => $roles
            ]
        ]);
    }
}
