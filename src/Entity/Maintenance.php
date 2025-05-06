<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MaintenanceRepository;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class)]
#[ORM\Table(name: 'maintenance')]
class Maintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $maintenanceID = null;

    public function getMaintenanceID(): ?int
    {
        return $this->maintenanceID;
    }

    public function setMaintenanceID(int $maintenanceID): self
    {
        $this->maintenanceID = $maintenanceID;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'maintenances')]
    #[ORM\JoinColumn(name: 'materielID', referencedColumnName: 'ID')]
    private ?Materiel $materiel = null;

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiel $materiel): self
    {
        $this->materiel = $materiel;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateMaintenance = null;

    public function getDateMaintenance(): ?\DateTimeInterface
    {
        return $this->dateMaintenance;
    }

    public function setDateMaintenance(\DateTimeInterface $dateMaintenance): self
    {
        $this->dateMaintenance = $dateMaintenance;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statutMaintenance = null;

    public function getStatutMaintenance(): ?string
    {
        return $this->statutMaintenance;
    }

    public function setStatutMaintenance(?string $statutMaintenance): self
    {
        $this->statutMaintenance = $statutMaintenance;
        return $this;
    }

}
