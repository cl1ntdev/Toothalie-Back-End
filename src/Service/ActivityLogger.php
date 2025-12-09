<?php

namespace App\Service;

use App\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogger
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security
    ) {}

    /**
     * Logs a user action
     * 
     * @param string $action The action being logged (e.g., 'USER_LOGIN', 'USER_CREATED')
     * @param string $targetData Description of what was done
     * @param object|null $user Optional user object. If not provided, will use $security->getUser()
     */
    public function log(string $action, string $targetData, ?object $user = null): void
    {
        // Use provided user, or fall back to security context
        // This prevents USER_LOGIN from being logged on every authenticated request
        if ($user === null) {
            $user = $this->security->getUser();
        }

        $username = "UNKNOWN";
        $role = "NONE";

        if ($user) {
            if (method_exists($user, 'getUserIdentifier')) {
                $username = $user->getUserIdentifier(); 
            }
            if (method_exists($user, 'getRoles')) {
                $role = $user->getRoles()[0] ?? "NONE";
            }
        }

        $log = new ActivityLog();
        $log->setUserId($user?->getId());
        $log->setUsername($username);
        $log->setRole($role);
        $log->setAction($action);
        $log->setTargetData($targetData);
        $this->em->persist($log);
        $this->em->flush();
    }
}
