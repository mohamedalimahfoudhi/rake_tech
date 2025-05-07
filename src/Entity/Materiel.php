<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'materiel')]
class Materiel
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'ID', type: 'integer', nullable: false)]
    private $id;

    #[ORM\Column(name: 'type', type: 'string', length: 255, nullable: true, options: ['default' => null])]
    #[Assert\NotBlank(message: 'Le type de matériel ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le type doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le type ne peut pas être plus long que {{ limit }} caractères'
    )]
    private $type = null;

    #[ORM\Column(name: 'typeSport', type: 'string', length: 255, nullable: true, options: ['default' => null])]
    #[Assert\NotBlank(message: 'Le type de sport ne peut pas être vide')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le type de sport doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le type de sport ne peut pas être plus long que {{ limit }} caractères'
    )]
    private $typesport = null;

    #[ORM\Column(name: 'prix', type: 'float', precision: 10, scale: 0, nullable: true, options: ['default' => null])]
    #[Assert\Type(type: 'float', message: 'Le prix doit être un nombre')]
    #[Assert\PositiveOrZero(message: 'Le prix ne peut pas être négatif')]
    private $prix = null;

    #[ORM\Column(name: 'dateReservation', type: 'date', nullable: true, options: ['default' => null])]
    #[Assert\Type("\DateTimeInterface")]
    private $datereservation = null;

    #[ORM\Column(name: 'statut', type: 'string', length: 0, nullable: true, options: ['default' => null])]
    #[Assert\Choice(choices: ['Disponible', 'Réservé', 'Sous maintenance'], message: 'Le statut doit être "Disponible", "Réservé" ou "Sous maintenance"')]
    private $statut = null;

    #[ORM\Column(name: 'ownerType', type: 'string', length: 0, nullable: true, options: ['default' => null])]
    #[Assert\Choice(choices: ['Club', 'Fédération', 'Privé'], message: 'Le type de propriétaire doit être "Club", "Fédération", "Privé"')]
    private $ownertype = null;

    #[ORM\Column( nullable: true)]
    private ?int $signaler = 0;

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getTypeSport(): ?string
    {
        return $this->typesport;
    }
    public function getSignaler(): ?int
    {
        return $this->signaler;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->datereservation;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function getOwnerType(): ?string
    {
        return $this->ownertype;
    }

    // Setters avec validation
    public function setType(?string $type): self
    {
        if (empty($type)) {
            throw new \InvalidArgumentException('Le type de matériel ne peut pas être vide');
        }
        $this->type = $type;
        return $this;
    }

    public function setTypeSport(?string $typesport): self
    {
        if (empty($typesport)) {
            throw new \InvalidArgumentException('Le type de sport ne peut pas être vide');
        }
        $this->typesport = $typesport;
        return $this;
    }

    public function setPrix(?float $prix): self
    {
        if ($prix !== null && $prix < 0) {
            throw new \InvalidArgumentException('Le prix ne peut pas être négatif');
        }
        $this->prix = $prix;
        return $this;
    }

    public function setDateReservation(?\DateTimeInterface $datereservation): self
    {
        $this->datereservation = $datereservation;
        return $this;
    }

    public function setStatut(?string $statut): self
    {
        $statutsValides = ['Disponible', 'Réservé', 'Sous maintenance'];
        if ($statut !== null && !in_array($statut, $statutsValides)) {
            throw new \InvalidArgumentException('Le statut doit être "Disponible", "Réservé" ou "Sous maintenance"');
        }
        $this->statut = $statut;
        return $this;
    }

    public function setOwnerType(?string $ownertype): self
    {
        $typesValides = ['Club', 'Fédération', 'Privé'];
        if ($ownertype !== null && !in_array($ownertype, $typesValides)) {
            throw new \InvalidArgumentException('Le type de propriétaire doit être "Club", "Fédération", "Privé"');
        }
        $this->ownertype = $ownertype;
        return $this;
    }

    // Méthodes utilitaires
    public function isDisponible(): bool
    {
        return $this->statut === 'disponible';
    }

    public function isReserve(): bool
    {
        return $this->statut === 'reserve';
    }

    public function isEnMaintenance(): bool
    {
        return $this->statut === 'maintenance';
    }

    public function setSignaler(?int $signaler): self
    {
         $this->signaler = $signaler;
         return $this;
    }
}
