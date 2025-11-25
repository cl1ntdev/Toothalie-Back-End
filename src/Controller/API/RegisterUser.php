<?php

namespace App\Controller\API;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterUser extends AbstractController
{
    #[Route("/api/register", name: "app_register", methods: ["POST"])]
    public function index(
        Request $req,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {
        $data = json_decode($req->getContent(), true);

        $required = [
            "email",
            "role",
            "password",
            "username",
            "first_name",
            "last_name",
        ];

        foreach ($required as $r) {
            if (!isset($data[$r]) || empty($data[$r])) {
                return new JsonResponse(
                    [
                        "error" => "Missing required field: $r",
                    ],
                    400,
                );
            }
        }

        $email = $data["email"];
        $role = is_array($data["role"]) ? $data["role"] : [$data["role"]];
        $password = $data["password"];
        $username = $data["username"];
        $firstName = $data["first_name"];
        $lastName = $data["last_name"];

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($role);
        $user->setUsername($username);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setCreatedAt(new \DateTimeImmutable());

        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(
            [
                "status" => "ok",
                "message" => "User registered successfully",
                "user" => [
                    "email" => $email,
                    "roles" => $role,
                    "username" => $username,
                    "first_name" => $firstName,
                    "last_name" => $lastName,
                    "created_at" => $user
                        ->getCreatedAt()
                        ->format("Y-m-d H:i:s"),
                ],
            ],
            201,
        );
    }
}
