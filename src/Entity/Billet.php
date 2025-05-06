<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\BilletRepository;

#[ORM\Entity(repositoryClass: BilletRepository::class)]
#[ORM\Table(name: 'billet')]
class Billet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "eventID", type: "integer")]
    private ?int $eventID = null;

    #[ORM\Column(name: "dateAchat", type: "datetime", nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[ORM\Column(name: "prix", type: "float", nullable: true)]
    private ?float $prix = null;

    #[ORM\Column(name: "typeBillet", type: "string", length: 255, nullable: true)]
    private ?string $typeBillet = null;

    #[ORM\Column(name: "statut", type: "string", length: 255, nullable: true)]
    private ?string $statut = 'Valide';

    #[ORM\Column(name: "quantite", type: "integer", nullable: true)]
    private ?int $quantite = 1;
    
    private ?string $codeUnique = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "eventID", referencedColumnName: "ID", nullable: false)]
    private ?Evenement $evenement = null;

    #[ORM\OneToMany(mappedBy: "billet", targetEntity: ReservationBillet::class)]
    private Collection $reservationBillets;

    public function __construct()
    {
        $this->reservationBillets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getEventID(): ?int
    {
        return $this->eventID;
    }

    public function setEventID(int $eventID): self
    {
        $this->eventID = $eventID;
        return $this;
    }

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(?\DateTimeInterface $dateAchat): self
    {
        $this->dateAchat = $dateAchat;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getTypeBillet(): ?string
    {
        return $this->typeBillet;
    }

    public function setTypeBillet(?string $typeBillet): self
    {
        $this->typeBillet = $typeBillet;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }
    
    public function getCodeUnique(): ?string
    {
        return $this->id !== null ? (string)$this->id : null;
    }

    public function setCodeUnique(?string $codeUnique): self
    {
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    /**
     * @return Collection<int, ReservationBillet>
     */
    public function getReservationBillets(): Collection
    {
        return $this->reservationBillets;
    }

    public function addReservationBillet(ReservationBillet $reservationBillet): self
    {
        if (!$this->reservationBillets->contains($reservationBillet)) {
            $this->reservationBillets->add($reservationBillet);
            $reservationBillet->setBillet($this);
        }
        return $this;
    }

    public function removeReservationBillet(ReservationBillet $reservationBillet): self
    {
        if ($this->reservationBillets->removeElement($reservationBillet)) {
            // set the owning side to null (unless already changed)
            if ($reservationBillet->getBillet() === $this) {
                $reservationBillet->setBillet(null);
            }
        }
        return $this;
    }
}
