<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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

    // 🔗 Many schedules belong to one dentist (now a User)
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "dentistID", referencedColumnName: "id", nullable: false)]
    private User $dentist;

    #[ORM\OneToMany(mappedBy: "schedule", targetEntity: Appointment::class, cascade: ["persist", "remove"])]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    // 🔹 Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getTimeSlot(): string
    {
        return $this->timeSlot;
    }

    public function setTimeSlot(string $timeSlot): static
    {
        $this->timeSlot = $timeSlot;
        return $this;
    }

    public function getDentist(): User
    {
        return $this->dentist;
    }

    public function setDentist(User $dentist): static
    {
        $this->dentist = $dentist;
        return $this;
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
            $appointment->setSchedule($this);
        }
        return $this;
    }

    public function removeAppointment(Appointment $appointment): static
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getSchedule() === $this) {
                $appointment->setSchedule(null);
            }
        }
        return $this;
    }
}
