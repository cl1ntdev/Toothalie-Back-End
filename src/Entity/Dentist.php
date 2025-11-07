<?php

namespace App\Entity;

use App\Repository\DentistRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DentistRepository::class)]
class Dentist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "dentistID", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 100, unique: true)]
    private string $email;

    #[ORM\Column(length: 50)]
    private string $experience;

    #[ORM\Column(length: 100)]
    private string $specialty;

    // 🔗 Relationships
    #[ORM\OneToMany(mappedBy: "dentist", targetEntity: Schedule::class)]
    private $schedules;

    #[ORM\OneToMany(mappedBy: "dentist", targetEntity: Appointment::class)]
    private $appointments;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    // getters and setters...

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
}
