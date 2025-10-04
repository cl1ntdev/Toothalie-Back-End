<?php

namespace App\Entity;

use App\Repository\AppointmentRepository;
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

    // getters and setters...
}
