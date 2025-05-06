<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TournoiRepository;

#[ORM\Entity(repositoryClass: TournoiRepository::class)]
#[ORM\Table(name: 'tournoi')]
class Tournoi
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateDebut = null;

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateFin = null;

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $lieu = null;

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $typeSport = null;

    public function getTypeSport(): ?string
    {
        return $this->typeSport;
    }

    public function setTypeSport(string $typeSport): self
    {
        $this->typeSport = $typeSport;
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
    private ?string $recompense = null;

    public function getRecompense(): ?string
    {
        return $this->recompense;
    }

    public function setRecompense(?string $recompense): self
    {
        $this->recompense = $recompense;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'tournois')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'ID')]
    private ?Evenement $evenement = null;

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

}
