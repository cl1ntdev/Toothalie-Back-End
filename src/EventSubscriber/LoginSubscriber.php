<?php

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ActivityLog;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginSubscriber implements EventSubscriberInterface
{
    private ActivityLogger $logger;
    private EntityManagerInterface $em;
    private RequestStack $requestStack;

    public function __construct(ActivityLogger $logger, EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        
        // Only log USER_LOGIN if this is an actual login attempt via /api/login-auth
        // JWT token validation on other endpoints should NOT trigger login logging
        if (!$request || $request->getPathInfo() !== '/api/login-auth') {
            return; // Skip logging for JWT token validation on other endpoints
        }

        $user = $event->getUser();

        // Prevent duplicate login logs in last 10 seconds
        $lastLog = $this->em->getRepository(ActivityLog::class)
            ->findOneBy(['userId' => $user->getId(), 'action' => 'USER_LOGIN'], ['createdAt' => 'DESC']);

        if ($lastLog && $lastLog->getCreatedAt() > new \DateTimeImmutable('-10 seconds')) {
            return; // skip duplicate
        }

        // Only log when it's an actual login via /api/login-auth endpoint
        // This prevents logging on every authenticated API request (JWT token validation)
        $this->logger->log(
            'USER_LOGIN',
            "User {$user->getUserIdentifier()} logged in",
            $user  // Pass user explicitly from the event
        );
    }
}
