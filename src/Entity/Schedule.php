<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "scheduleID", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $dayOfWeek;

    #[ORM\Column(length: 20)]
    private string $timeSlot;

    // 🔗 Many schedules belong to one dentist
    #[ORM\ManyToOne(targetEntity: Dentist::class, inversedBy: "schedules")]
    #[ORM\JoinColumn(name: "dentistID", referencedColumnName: "dentistID", nullable: false)]
    private Dentist $dentist;

    #[ORM\OneToMany(mappedBy: "schedule", targetEntity: Appointment::class)]
    private $appointments;

    // getters and setters...
}
