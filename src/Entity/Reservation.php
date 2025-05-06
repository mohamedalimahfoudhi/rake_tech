<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID", type: "integer")]
    private ?int $id = null;

    #[ORM\Column(name: "utilisateurID", type: "integer")]
    private ?int $utilisateurID = null;

    #[ORM\Column(name: "dateReservation", type: "datetime")]
    private ?\DateTimeInterface $dateReservation = null;

    #[ORM\Column(name: "statut", type: "string", length: 255)]
    private ?string $statut = null;

    #[ORM\Column(name: "type", type: "string", length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "utilisateurID", referencedColumnName: "ID", nullable: false)]
    private ?Utilisateur $utilisateur = null;

    #[ORM\OneToMany(mappedBy: "reservation", targetEntity: ReservationBillet::class)]
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

    public function getUtilisateurID(): ?int
    {
        return $this->utilisateurID;
    }

    public function setUtilisateurID(int $utilisateurID): self
    {
        $this->utilisateurID = $utilisateurID;
        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->dateReservation;
    }

    public function setDateReservation(\DateTimeInterface $dateReservation): self
    {
        $this->dateReservation = $dateReservation;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
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
            $reservationBillet->setReservation($this);
        }

        return $this;
    }

    public function removeReservationBillet(ReservationBillet $reservationBillet): self
    {
        if ($this->reservationBillets->removeElement($reservationBillet)) {
            // set the owning side to null (unless already changed)
            if ($reservationBillet->getReservation() === $this) {
                $reservationBillet->setReservation(null);
            }
        }

        return $this;
    }
}
