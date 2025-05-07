<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: 'reservation')]
class Reservation
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'ID', type: 'integer', nullable: false)]
    private ?int $id = null;


    #[ORM\Column(name: 'dateReservation', type: 'datetime', nullable: true)]
    #[Assert\Type("\DateTimeInterface")]
    private ?\DateTimeInterface $datereservation = null;

   
    #[ORM\Column(name: 'statut', type: 'string', length: 20, nullable: true, options: ['default' => "'Confirmée'"])]
    #[Assert\NotBlank(message: 'Le statut ne peut pas être vide')]
    #[Assert\Choice(
        choices: [ 'Confirmée', 'Annulée'],
        message: 'Le statut doit être  "Confirmée", "Annulée" '
    )]
    private string $statut = 'Confirmée';


    #[ORM\Column(name: 'type', type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Le type de réservation ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['TERRAIN', 'BILLET'],
        message: 'Le type doit être "TERRAIN", "BILLET" '
    )]
    private ?string $type = null;


    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'utilisateurid', referencedColumnName: 'ID')]
    #[Assert\NotNull(message: 'L\'utilisateur est requis')]
    private ?User $utilisateurid = null;


    #[ORM\ManyToMany(targetEntity: Billet::class, inversedBy: 'reservationid')]
    #[ORM\JoinTable(name: 'reservation_billet')]
    #[ORM\JoinColumn(name: 'reservationID', referencedColumnName: 'ID')]
    #[ORM\InverseJoinColumn(name: 'billetID', referencedColumnName: 'ID')]
    private Collection $billetid;

    #[ORM\ManyToOne(targetEntity: Tournoi::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'tournoiid', referencedColumnName: 'id')]
    private ?Tournoi $tournois = null;



    public function __construct()
    {
        $this->billetid = new ArrayCollection();
        $this->datereservation = new \DateTime();
        $this->type = null;
        $this->statut = 'Confirmée';
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->datereservation;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUtilisateurId(): ?User
    {
        return $this->utilisateurid;
    }

    public function getBilletId(): Collection
    {
        return $this->billetid;
    }

    // Setters
    public function setDateReservation(?\DateTimeInterface $datereservation): self
    {
        $this->datereservation = $datereservation;
        return $this;
    }

    public function setStatut(string $statut): self
    {
        $statutsValides = [ 'Confirmée', 'Annulée'];
        if (!in_array($statut, $statutsValides)) {
            throw new \InvalidArgumentException('Le statut doit être  "Confirmée" ou "Annulée"');
        }
        $this->statut = $statut;
        return $this;
    }

    public function setType(?string $type): self
    {
        if ($type !== null) {
            $typesValides = ['TERRAIN', 'BILLET'];
            if (!in_array($type, $typesValides)) {
                throw new \InvalidArgumentException('Le type doit être "TERRAIN" ou "BILLET"');
            }
        }
        $this->type = $type;
        return $this;
    }

    public function setUtilisateurId(?User $utilisateurid): self
    {
        $this->utilisateurid = $utilisateurid;
        return $this;
    }


    // Méthodes de gestion des billets
    public function addBilletId(Billet $billet): self
    {
        if (!$this->billetid->contains($billet)) {
            $this->billetid[] = $billet;
            $billet->addReservation($this);
        }
        return $this;
    }

    public function removeBilletId(Billet $billet): self
    {
        if ($this->billetid->removeElement($billet)) {
            $billet->removeReservation($this);
        }
        return $this;
    }

    // Méthodes utilitaires
    public function isEnAttente(): bool
    {
        return $this->statut === 'En attente';
    }

    public function isConfirmee(): bool
    {
        return $this->statut === 'Confirmée';
    }

    public function isAnnulee(): bool
    {
        return $this->statut === 'Annulée';
    }

    public function isTerminee(): bool
    {
        return $this->statut === 'Terminée';
    }

    public function isMateriel(): bool
    {
        return $this->type === 'Matériel';
    }

    public function isTERRAIN(): bool
    {
        return $this->type === 'TERRAIN';
    }

    public function isEvenement(): bool
    {
        return $this->type === 'Événement';
    }
    public function __toString(): string
    {
        return $this->getStatut();
    }

    public function getTournois(): ?Tournoi
    {
        return $this->tournois;
    }

    // Correct the setter for Tournoi
    public function setTournois(?Tournoi $tournois): self
    {
        $this->tournois = $tournois;
        return $this;
    }
}
