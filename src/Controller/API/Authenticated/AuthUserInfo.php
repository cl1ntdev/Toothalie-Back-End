<?php

namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class AuthUserInfo extends AbstractController
{
    #[Route("/api/auth-user-info", name: "auth-user-info", methods: ["GET"])]
    public function getUserInfo(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse([
                "status" => "error",
                "message" => "Unauthorized"
            ], 401);
        }

        return new JsonResponse([
            "status" => "ok",
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "roles" => $user->getRoles(),
                "firstName" => $user->getFirstName(),
                "lastName" => $user->getLastName()
            ]
        ], 200);
    }
}
