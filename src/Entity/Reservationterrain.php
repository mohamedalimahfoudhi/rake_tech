<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationterrainRepository;

#[ORM\Entity(repositoryClass: ReservationterrainRepository::class)]
#[ORM\Table(name: 'reservationterrain')]
class Reservationterrain
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

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'reservationterrains')]
    #[ORM\JoinColumn(name: 'userID', referencedColumnName: 'ID')]
    private ?Utilisateur $utilisateur = null;

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Terrain::class, inversedBy: 'reservationterrains')]
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

    #[ORM\Column(type: 'time', nullable: true)]
    private ?string $heureReservation = null;

    public function getHeureReservation(): ?string
    {
        return $this->heureReservation;
    }

    public function setHeureReservation(?string $heureReservation): self
    {
        $this->heureReservation = $heureReservation;
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

    #[ORM\ManyToMany(targetEntity: Terrainsiege::class, inversedBy: 'reservationterrains')]
    #[ORM\JoinTable(
        name: 'reservationsiege',
        joinColumns: [
            new ORM\JoinColumn(name: 'reservationID', referencedColumnName: 'ID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'siegeID', referencedColumnName: 'siegeID')
        ]
    )]
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
