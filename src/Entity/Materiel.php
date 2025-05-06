<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\MaterielRepository;

#[ORM\Entity(repositoryClass: MaterielRepository::class)]
#[ORM\Table(name: 'materiel')]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $ID = null;

    public function getID(): ?int
    {
        return $this->ID;
    }

    public function setID(int $ID): self
    {
        $this->ID = $ID;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $typeSport = null;

    public function getTypeSport(): ?string
    {
        return $this->typeSport;
    }

    public function setTypeSport(?string $typeSport): self
    {
        $this->typeSport = $typeSport;
        return $this;
    }

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateReservation = null;

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(?\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $ownerType = null;

    public function getOwnerType(): ?string
    {
        return $this->ownerType;
    }

    public function setOwnerType(?string $ownerType): self
    {
        $this->ownerType = $ownerType;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'materiel')]
    private Collection $emprunts;

    /**
     * @return Collection<int, Emprunt>
     */
    public function getEmprunts(): Collection
    {
        if (!$this->emprunts instanceof Collection) {
            $this->emprunts = new ArrayCollection();
        }
        return $this->emprunts;
    }

    public function addEmprunt(Emprunt $emprunt): self
    {
        if (!$this->getEmprunts()->contains($emprunt)) {
            $this->getEmprunts()->add($emprunt);
        }
        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): self
    {
        $this->getEmprunts()->removeElement($emprunt);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Maintenance::class, mappedBy: 'materiel')]
    private Collection $maintenances;

    /**
     * @return Collection<int, Maintenance>
     */
    public function getMaintenances(): Collection
    {
        if (!$this->maintenances instanceof Collection) {
            $this->maintenances = new ArrayCollection();
        }
        return $this->maintenances;
    }

    public function addMaintenance(Maintenance $maintenance): self
    {
        if (!$this->getMaintenances()->contains($maintenance)) {
            $this->getMaintenances()->add($maintenance);
        }
        return $this;
    }

    public function removeMaintenance(Maintenance $maintenance): self
    {
        $this->getMaintenances()->removeElement($maintenance);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservationmateriel::class, mappedBy: 'materiel')]
    private Collection $reservationmateriels;

    /**
     * @return Collection<int, Reservationmateriel>
     */
    public function getReservationmateriels(): Collection
    {
        if (!$this->reservationmateriels instanceof Collection) {
            $this->reservationmateriels = new ArrayCollection();
        }
        return $this->reservationmateriels;
    }

    public function addReservationmateriel(Reservationmateriel $reservationmateriel): self
    {
        if (!$this->getReservationmateriels()->contains($reservationmateriel)) {
            $this->getReservationmateriels()->add($reservationmateriel);
        }
        return $this;
    }

    public function removeReservationmateriel(Reservationmateriel $reservationmateriel): self
    {
        $this->getReservationmateriels()->removeElement($reservationmateriel);
        return $this;
    }

}
