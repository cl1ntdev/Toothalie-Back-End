<?php

namespace App\Entity;

use App\Repository\ReminderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReminderRepository::class)]
class Reminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Appointment $Appointment = null;

    #[ORM\Column]
    private array $Information = [];

    #[ORM\Column(nullable: true)]
    private ?bool $viewed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointment(): ?Appointment
    {
        return $this->Appointment;
    }

    public function setAppointment(?Appointment $Appointment): static
    {
        $this->Appointment = $Appointment;

        return $this;
    }

    public function getInformation(): array
    {
        return $this->Information;
    }

    public function setInformation(array $Information): static
    {
        $this->Information = $Information;

        return $this;
    }

    public function isViewed(): ?bool
    {
        return $this->viewed;
    }

    public function setViewed(?bool $viewed): static
    {
        $this->viewed = $viewed;

        return $this;
    }
}
