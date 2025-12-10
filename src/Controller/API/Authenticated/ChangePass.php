<?php
namespace App\Controller\API\Authenticated;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Service\ActivityLogger;
use Doctrine\ORM\EntityManagerInterface;

class ChangePass extends AbstractController 
{
    #[Route('/api/change-pass', name: 'change-pass', methods: ['POST'])]
    public function changePass(
        Request $req,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ActivityLogger $logger
    ): JsonResponse {
        $data = json_decode($req->getContent(), true);

        $currentPassword = $data['currentPassword'] ?? '';
        $newPassword = $data['newPassword'] ?? '';
        $confirmPassword = $data['confirmPassword'] ?? '';

        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        if (!$hasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Current password is incorrect'
            ], 400);
        }

        if ($newPassword !== $confirmPassword) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'New passwords do not match'
            ], 400);
        }
        $logger->log(
            'PASSWORD_UPDATED',
            "updated password of {$user->getUsername()}"
        );
        $hashed = $hasher->hashPassword($user, $newPassword);
        $user->setPassword($hashed);

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'message' => 'Password updated successfully'
        ]);
    }
}
