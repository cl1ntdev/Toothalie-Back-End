<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "appointment_id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTime $appointmentDate = null;

    // 🔗 Relationships
    #[ORM\ManyToOne(targetEntity: Patient::class, inversedBy: "appointments")]
    #[ORM\JoinColumn(name: "patient_id", referencedColumnName: "patient_id", nullable: false)]
    private Patient $patient;

    #[ORM\ManyToOne(targetEntity: Dentist::class, inversedBy: "appointments")]
    #[ORM\JoinColumn(name: "dentist_id", referencedColumnName: "dentistID", nullable: false)]
    private Dentist $dentist;

    #[ORM\ManyToOne(targetEntity: Schedule::class, inversedBy: "appointments")]
    #[ORM\JoinColumn(name: "schedule_id", referencedColumnName: "scheduleID", nullable: false)]
    private Schedule $schedule;

    #[ORM\Column(nullable: true)]
    private ?bool $Emergency = null;

    #[ORM\ManyToOne(inversedBy: 'appointments')]
    private ?AppointmentType $appointmentType = null;

   
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $userSetDate = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $Status = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $deletedOn = null;


    // getters and setters...

    public function isEmergency(): ?bool
    {
        return $this->Emergency;
    }

    public function setEmergency(?bool $Emergency): static
    {
        $this->Emergency = $Emergency;

        return $this;
    }

    public function getAppointmentType(): ?AppointmentType
    {
        return $this->appointmentType;
    }

    public function setAppointmentType(?AppointmentType $appointmentType): static
    {
        $this->appointmentType = $appointmentType;

        return $this;
    }

   

    public function getUserSetDate(): ?string
    {
        return $this->userSetDate;
    }

    public function setUserSetDate(?string $userSetDate): static
    {
        $this->userSetDate = $userSetDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->Status;
    }

    public function setStatus(?string $Status): static
    {
        $this->Status = $Status;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getDeletedOn(): ?\DateTime
    {
        return $this->deletedOn;
    }
    
    public function setDeletedOn(?\DateTime $deletedOn): self
    {
        $this->deletedOn = $deletedOn;
        return $this;
    }

}
