<?php
// src/EventSubscriber/UserActivitySubscriber.php
namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\Appointment;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use App\Service\ActivityLogger;
use Symfony\Bundle\SecurityBundle\Security;

class UserActivitySubscriber implements EventSubscriber
{
    private ActivityLogger $logger;
    private Security $security;

    public function __construct(ActivityLogger $logger, Security $security)
    {
        $this->logger = $logger;
        $this->security = $security;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,  // after entity is created
            Events::postUpdate,   // after update
            Events::postRemove    // after delete
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $actor = $this->security->getUser();

        if ($entity instanceof User) {
            $this->logger->log('USER_CREATED', "Created user ID {$entity->getId()} ({$entity->getUserIdentifier()})", $actor);
        } elseif ($entity instanceof Appointment) {
            $this->logger->log('RECORD_CREATED', "Created appointment ID {$entity->getId()}", $actor);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $actor = $this->security->getUser();

        if ($entity instanceof User) {
            $this->logger->log('USER_UPDATED', "Updated user ID {$entity->getId()} ({$entity->getUserIdentifier()})", $actor);
        } elseif ($entity instanceof Appointment) {
            $this->logger->log('RECORD_UPDATED', "Updated appointment ID {$entity->getId()}", $actor);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $actor = $this->security->getUser();

        if ($entity instanceof User) {
            $this->logger->log('USER_DELETED', "Deleted user ID {$entity->getId()}", $actor);
        } elseif ($entity instanceof Appointment) {
            $this->logger->log('RECORD_DELETED', "Deleted appointment ID {$entity->getId()}", $actor);
        }
    }
}
