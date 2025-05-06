<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TerrainsiegeRepository;

#[ORM\Entity(repositoryClass: TerrainsiegeRepository::class)]
#[ORM\Table(name: 'terrainsiege')]
class Terrainsiege
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $siegeID = null;

    public function getSiegeID(): ?int
    {
        return $this->siegeID;
    }

    public function setSiegeID(int $siegeID): self
    {
        $this->siegeID = $siegeID;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Terrain::class, inversedBy: 'terrainsieges')]
    #[ORM\JoinColumn(name: 'terrainID', referencedColumnName: 'courtID')]
    private ?Terrain $terrain = null;

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): self
    {
        $this->terrain = $terrain;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $rangee = null;

    public function getRangee(): ?string
    {
        return $this->rangee;
    }

    public function setRangee(?string $rangee): self
    {
        $this->rangee = $rangee;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $numero = null;

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): self
    {
        $this->numero = $numero;
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

    #[ORM\Column(type: 'decimal', nullable: true)]
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

    #[ORM\ManyToMany(targetEntity: Reservationterrain::class, inversedBy: 'terrainsieges')]
    #[ORM\JoinTable(
        name: 'reservationsiege',
        joinColumns: [
            new ORM\JoinColumn(name: 'siegeID', referencedColumnName: 'siegeID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'reservationID', referencedColumnName: 'ID')
        ]
    )]
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

}
