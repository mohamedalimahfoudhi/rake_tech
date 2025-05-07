<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'billet')]
class Billet
{

    #[ORM\Column(name: 'ID', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;


    #[ORM\Column(name: 'dateAchat', type: 'datetime', nullable: true)]
    #[Assert\Type('\DateTimeInterface', message: 'La date d\'achat doit être une date valide.')]
    private ?\DateTime $dateachat = null;


    #[ORM\Column(name: 'prix', type: 'float', precision: 10, scale: 0, nullable: true)]
    #[Assert\Type(type: 'float', message: 'Le prix doit être un nombre.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Le prix ne peut pas être négatif.')]
    private ?float $prix = null;


    #[ORM\Column(name: 'typeBillet', type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le type de billet ne peut pas être vide.')]
    #[Assert\Choice(
        choices: ['Standard', 'VIP', 'Early Bird', 'Groupe'],
        message: 'Le type de billet doit être l\'un des suivants : Standard, VIP, Early Bird, Groupe.'
    )]
    private ?string $typebillet = null;


    #[ORM\Column(name: 'statut', type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Le statut ne peut pas être vide.')]
    #[Assert\Choice(
        choices: ['Valide', 'Non valide', 'Annulé'],
        message: 'Le statut doit être l\'un des suivants : Valide, Non valide, Annulé .'
    )]
    private string $statut = 'Valide';


    #[ORM\Column(name: 'quantite', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'integer', message: 'La quantité doit être un nombre entier.')]
    #[Assert\GreaterThan(value: 0, message: 'La quantité doit être supérieure à 0.')]
    private int $quantite = 1;


    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: 'eventID', referencedColumnName: 'id')]
    #[Assert\NotNull(message: 'L\'événement associé est requis.')]
    private ?Event $eventid = null;


    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'billetid')]
    private Collection $reservationid;


    public function __construct()
    {
        $this->reservationid = new ArrayCollection();
        $this->dateachat = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAchat(): ?\DateTime
    {
        return $this->dateachat;
    }

    public function setDateAchat(?\DateTime $dateachat): self
    {
        $this->dateachat = $dateachat;
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
        return $this->typebillet;
    }

    public function setTypeBillet(?string $typebillet): self
    {
        $this->typebillet = $typebillet;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getQuantite(): int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getEventId(): ?Event
    {
        return $this->eventid;
    }

    public function setEventId(?Event $eventid): self
    {
        $this->eventid = $eventid;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservationid;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservationid->contains($reservation)) {
            $this->reservationid[] = $reservation;
            $reservation->addBilletId($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservationid->removeElement($reservation)) {
            $reservation->removeBilletId($this);
        }
        return $this;
    }

    public function isValid(): bool
    {
        return $this->statut === 'Valide';
    }

    public function isUsed(): bool
    {
        return $this->statut === 'Utilise';
    }

    public function isCancelled(): bool
    {
        return $this->statut === 'Annule';
    }

    public function isExpired(): bool
    {
        return $this->statut === 'Expire';
    }

    public function getMontantTotal(): float
    {
        return $this->prix * $this->quantite;
    }
}
