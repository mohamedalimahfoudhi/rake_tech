<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TerrainRepository;

#[ORM\Entity(repositoryClass: TerrainRepository::class)]
#[ORM\Table(name: 'terrain')]
class Terrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $courtID = null;

    public function getCourtID(): ?int
    {
        return $this->courtID;
    }

    public function setCourtID(int $courtID): self
    {
        $this->courtID = $courtID;
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $localisation = null;

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(?string $localisation): self
    {
        $this->localisation = $localisation;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $capacite = null;

    public function getCapacite(): ?int
    {
        return $this->capacite;
    }

    public function setCapacite(?int $capacite): self
    {
        $this->capacite = $capacite;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $statut = null;

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(?int $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservationterrain::class, mappedBy: 'terrain')]
    private Collection $reservationterrains;

    /**
     * @return Collection<int, Reservationterrain>
     */
    public function getReservationterrains(): Collection
    {
        if (!$this->reservationterrains instanceof Collection) {
            $this->reservationterrains = new ArrayCollection();
        }
        return $this->reservationterrains;
    }

    public function addReservationterrain(Reservationterrain $reservationterrain): self
    {
        if (!$this->getReservationterrains()->contains($reservationterrain)) {
            $this->getReservationterrains()->add($reservationterrain);
        }
        return $this;
    }

    public function removeReservationterrain(Reservationterrain $reservationterrain): self
    {
        $this->getReservationterrains()->removeElement($reservationterrain);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Terrainsiege::class, mappedBy: 'terrain')]
    private Collection $terrainsieges;

    /**
     * @return Collection<int, Terrainsiege>
     */
    public function getTerrainsieges(): Collection
    {
        if (!$this->terrainsieges instanceof Collection) {
            $this->terrainsieges = new ArrayCollection();
        }
        return $this->terrainsieges;
    }

    public function addTerrainsiege(Terrainsiege $terrainsiege): self
    {
        if (!$this->getTerrainsieges()->contains($terrainsiege)) {
            $this->getTerrainsieges()->add($terrainsiege);
        }
        return $this;
    }

    public function removeTerrainsiege(Terrainsiege $terrainsiege): self
    {
        $this->getTerrainsieges()->removeElement($terrainsiege);
        return $this;
    }

}
