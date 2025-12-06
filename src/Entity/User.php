<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, UserRole>
     */
    #[ORM\ManyToMany(targetEntity: UserRole::class, mappedBy: 'User')]
    private Collection $userRoles;

    /**
     * @var Collection<int, DentistService>
     */
    #[ORM\OneToMany(targetEntity: DentistService::class, mappedBy: 'User')]
    private Collection $dentistServices;

    #[ORM\Column(nullable: true)]
    private ?bool $disable = null;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
        $this->dentistServices = new ArrayCollection();
    }

    // ---------- Getters and Setters ----------

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // every user has ROLE_USER
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // Deprecated method, required by UserInterface
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->addUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            $userRole->removeUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, DentistService>
     */
    public function getDentistServices(): Collection
    {
        return $this->dentistServices;
    }

    public function addDentistService(DentistService $dentistService): static
    {
        if (!$this->dentistServices->contains($dentistService)) {
            $this->dentistServices->add($dentistService);
            $dentistService->setUser($this);
        }

        return $this;
    }

    public function removeDentistService(DentistService $dentistService): static
    {
        if ($this->dentistServices->removeElement($dentistService)) {
            // set the owning side to null (unless already changed)
            if ($dentistService->getUser() === $this) {
                $dentistService->setUser(null);
            }
        }

        return $this;
    }

    public function isDisable(): ?bool
    {
        return $this->disable;
    }

    public function setDisable(?bool $disable): static
    {
        $this->disable = $disable;

        return $this;
    }
}
