<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'services', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceType $serviceType = null;

    /**
     * @var Collection<int, DentistService>
     */
    #[ORM\ManyToMany(targetEntity: DentistService::class, mappedBy: 'Service')]
    private Collection $dentistServices;

    public function __construct()
    {
        $this->dentistServices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getServiceType(): ?ServiceType
    {
        return $this->serviceType;
    }

    public function setServiceType(?ServiceType $serviceType): static
    {
        $this->serviceType = $serviceType;
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
            $dentistService->addService($this);
        }

        return $this;
    }

    public function removeDentistService(DentistService $dentistService): static
    {
        if ($this->dentistServices->removeElement($dentistService)) {
            $dentistService->removeService($this);
        }

        return $this;
    }
}
