<?php

namespace App\EventSubscriber;

use App\Service\ActivityLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class LogoutSubscriber implements EventSubscriberInterface
{
    private ActivityLogger $logger;
    private RequestStack $requestStack;

    public function __construct(ActivityLogger $logger, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->requestStack = $requestStack;
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
        
        if (!$user) {
            return;
        }

        // Log logout event
        $this->logger->log(
            'USER_LOGOUT',
            "User {$user->getUserIdentifier()} logged out",
            $user
        );
    }
}

