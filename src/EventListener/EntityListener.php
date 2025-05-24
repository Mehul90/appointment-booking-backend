<?php

namespace App\EventListener;

use App\Entity\Participant;
use DateTimeImmutable;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Appointment;
use App\Entity\AppointmentParticipant;
use App\Entity\User;

class EntityListener
{
    public function __construct(
        private Security $security,
    ) {}

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$this->security->getUser() instanceof User) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if ($entity instanceof Appointment || $entity instanceof AppointmentParticipant || $entity instanceof Participant) {
            if (method_exists($entity, 'setCreatedBy') && $entity->getCreatedBy() === null) {
                $entity->setCreatedBy($user);
            }
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof Appointment || $entity instanceof AppointmentParticipant || $entity instanceof Participant) {
            if (method_exists($entity, 'setUpdatedAt')) {
                $entity->setUpdatedAt();
            }
        }
    }
}
