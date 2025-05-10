<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Entity]
#[ORM\Table(name: 'emprunt')]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'empruntID', type: 'integer', nullable: false)]
    private $empruntid;

    #[ORM\Column(name: 'dateEmprunt', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date d\'emprunt ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface")]
    private $dateemprunt;

    #[ORM\Column(name: 'dateRetour', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de retour ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\Expression(
        "this.getDateRetour() > this.getDateEmprunt()",
        message: 'La date de retour doit être postérieure à la date d\'emprunt'
    )]
    private $dateretour;

    #[ORM\Column(name: 'statutEmprunt', type: 'string', length: 0, nullable: true, options: ['default' => 'NULL'])]
    #[Assert\Choice(choices: ['Emprunté', 'Retourné', 'En retard'], message: 'Le statut doit être soit "Emprunté", "Retourné" ou "En retard"')]
    private $statutemprunt = 'NULL';

    #[ORM\ManyToOne(targetEntity: Materiel::class)]
    #[ORM\JoinColumn(name: 'materielID', referencedColumnName: 'ID')]
    #[Assert\NotNull(message: 'Le matériel ne peut pas être vide')]
    private $materielid;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'userid', referencedColumnName: 'ID')]
    #[Assert\NotNull(message: 'L\'utilisateur ne peut pas être vide')]
    private User $userid;

    // Getters
    public function getEmpruntId(): ?int
    {
        return $this->empruntid;
    }

    public function getDateEmprunt(): ?\DateTimeInterface
    {
        return $this->dateemprunt;
    }

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->dateretour;
    }

    public function getStatutEmprunt(): ?string
    {
        return $this->statutemprunt;
    }

    public function getMaterielId(): ?Materiel
    {
        return $this->materielid;
    }

    public function getUserId(): ?User
    {
        return $this->userid;
    }

    // Alias methods for better compatibility
    public function getMateriel(): ?Materiel
    {
        return $this->materielid;
    }

    public function getUser(): ?User
    {
        return $this->userid;
    }

    // Original setters
    public function setMaterielId(?Materiel $materielid): self
    {
        if ($materielid === null) {
            throw new \InvalidArgumentException('Le matériel ne peut pas être null');
        }
        $this->materielid = $materielid;
        return $this;
    }

    public function setUserId(?User $userid): self
    {
        if ($userid === null) {
            throw new \InvalidArgumentException('L\'utilisateur ne peut pas être null');
        }
        $this->userid = $userid;
        return $this;
    }

    // Alias setters
    public function setMateriel(?Materiel $materiel): self
    {
        if ($materiel === null) {
            throw new \InvalidArgumentException('Le matériel ne peut pas être null');
        }
        $this->materielid = $materiel;
        return $this;
    }

    public function setUser(?User $user): self
    {
        if ($user === null) {
            throw new \InvalidArgumentException('L\'utilisateur ne peut pas être null');
        }
        $this->userid = $user;
        return $this;
    }

    // Setters avec validation
    public function setDateEmprunt(\DateTimeInterface $dateemprunt): self
    {
        $this->dateemprunt = $dateemprunt;
        return $this;
    }

    public function setDateRetour(\DateTimeInterface $dateretour): self
    {
        if ($dateretour < $this->dateemprunt) {
            throw new \InvalidArgumentException('La date de retour doit être postérieure à la date d\'emprunt');
        }
        $this->dateretour = $dateretour;
        return $this;
    }

    public function setStatutEmprunt(?string $statutemprunt): self
    {
        $statutsValides = ['Emprunté', 'Retourné', 'En retard'];
        if (!in_array($statutemprunt, $statutsValides) && $statutemprunt !== null) {
            throw new \InvalidArgumentException('Le statut doit être soit "Emprunté", "Retourné" ou "En retard"');
        }
        $this->statutemprunt = $statutemprunt;
        return $this;
    }

    // Méthode utilitaire pour vérifier si un emprunt est en retard
    public function isEnRetard(): bool
    {
        return $this->dateretour < new DateTime() && $this->statutemprunt === 'en cours';
    }

    // Méthode pour mettre à jour automatiquement le statut
    public function updateStatut(): self
    {
        if ($this->isEnRetard()) {
            $this->setStatutEmprunt('en retard');
        }
        return $this;
    }
}
