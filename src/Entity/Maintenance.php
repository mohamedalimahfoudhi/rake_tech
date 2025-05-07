<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity]
#[ORM\Table(name: 'maintenance')]
class Maintenance
{

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'maintenanceID', type: 'integer', nullable: false)]
    private $maintenanceid;


    #[ORM\Column(name: 'dateMaintenance', type: 'date', nullable: false)]
    #[Assert\NotNull(message: 'La date de maintenance ne peut pas être vide')]
    #[Assert\Type("\DateTimeInterface")]
    #[Assert\GreaterThanOrEqual(
        value: 'today',
        message: 'La date de maintenance doit être aujourd\'hui ou dans le futur'
    )]
    private $datemaintenance;


    #[ORM\Column(name: 'description', type: 'text', length: 65535, nullable: false)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description doit faire au moins {{ limit }} caractères'
    )]
    private $description;


    #[ORM\Column(name: 'statutMaintenance', type: 'string', length: 0, nullable: true, options: ['default' => 'NULL'])]
    #[Assert\Choice(choices: ['Planifié', 'En cours', 'Terminé'], message: 'Le statut doit être "Planifié", "En cours", "Terminé"')]
    private $statutmaintenance = 'NULL';


    #[ORM\ManyToOne(targetEntity: Materiel::class)]
    #[ORM\JoinColumn(name: 'materielID', referencedColumnName: 'ID')]
    #[Assert\NotNull(message: 'Le matériel ne peut pas être vide')]
    private $materielid;

    // Getters
    public function getMaintenanceId(): ?int
    {
        return $this->maintenanceid;
    }

    public function getDateMaintenance(): ?\DateTimeInterface
    {
        return $this->datemaintenance;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatutMaintenance(): ?string
    {
        return $this->statutmaintenance;
    }

    public function getMaterielId(): ?Materiel
    {
        return $this->materielid;
    }

    // Setters avec validation
    public function setDateMaintenance(\DateTimeInterface $datemaintenance): self
    {
        $this->datemaintenance = $datemaintenance;
        return $this;
    }

    public function setDescription(string $description): self
    {
        if (strlen($description) < 10) {
            throw new \InvalidArgumentException('La description doit faire au moins 10 caractères');
        }
        $this->description = $description;
        return $this;
    }

    public function setStatutMaintenance(?string $statutmaintenance): self
    {
        $statutsValides = ['Planifié', 'En cours', 'Terminé'];
        if ($statutmaintenance !== null && !in_array($statutmaintenance, $statutsValides)) {
            throw new \InvalidArgumentException('Le statut doit être "Planifié", "En cours" ou "Terminé"');
        }
        $this->statutmaintenance = $statutmaintenance;
        return $this;
    }

    public function setMaterielId(?Materiel $materielid): self
    {
        if ($materielid === null) {
            throw new \InvalidArgumentException('Le matériel ne peut pas être null');
        }
        $this->materielid = $materielid;
        return $this;
    }

    // Méthodes utilitaires
    public function isPlanifie(): bool
    {
        return $this->statutmaintenance === 'Planifié';
    }

    public function isEnCours(): bool
    {
        return $this->statutmaintenance === 'En cours';
    }

    public function isTermine(): bool
    {
        return $this->statutmaintenance === 'Terminé';
    }
}
