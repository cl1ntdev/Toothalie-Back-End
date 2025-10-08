<?php

namespace App\Entity;

use App\Repository\PatientRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\UserRole;
#[ORM\Entity(repositoryClass: PatientRepository::class)]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "patient_id", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 100, unique: true)]
    private string $username;

    #[ORM\Column(length: 100)]
    private string $firstName;

    #[ORM\Column(length: 100)]
    private string $lastName;

    #[ORM\Column(type: "string", enumType: UserRole::class, nullable: true)]
    private ?UserRole $role = UserRole::Patient;
    
    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(type: "datetime_immutable", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeImmutable $createdAt = null;

    // 🔗 Relationships
    #[ORM\OneToMany(mappedBy: "patient", targetEntity: Appointment::class)]
    private $appointments;

    #[ORM\Column(length: 15)]
    private ?string $contact_no = null;

    #[ORM\Column(length: 30)]
    private ?string $email = null;

    // getters and setters...

    public function getContactNo(): ?string
    {
        return $this->contact_no;
    }

    public function setContactNo(string $contact_no): static
    {
        $this->contact_no = $contact_no;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
