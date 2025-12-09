<?php

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\JsonResponse; // <--- ADD THIS

class LogoutSubscriber implements EventSubscriberInterface
{
    private ActivityLogger $logger;

    // You don't strictly need RequestStack unless you use it, 
    // but I kept it here to match your code.
    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        
        if ($user) {
            // Log logout event
            $this->logger->log(
                'USER_LOGOUT',
                "User {$user->getUserIdentifier()} logged out",
                $user
            );
        }

        // 👇 CRITICAL FOR REACT:
        // Override the default redirect and send 200 OK JSON instead.
        $response = new JsonResponse([
        'message' => 'Logged out successfully',
        'ok' => true
        ], 200);
        $event->setResponse($response);
    }
}