<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EmpruntRepository;

#[ORM\Entity(repositoryClass: EmpruntRepository::class)]
#[ORM\Table(name: 'emprunt')]
class Emprunt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $empruntID = null;

    public function getEmpruntID(): ?int
    {
        return $this->empruntID;
    }

    public function setEmpruntID(int $empruntID): self
    {
        $this->empruntID = $empruntID;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'emprunts')]
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

    #[ORM\ManyToOne(targetEntity: Materiel::class, inversedBy: 'emprunts')]
    #[ORM\JoinColumn(name: 'materielID', referencedColumnName: 'ID')]
    private ?Materiel $materiel = null;

    public function getMateriel(): ?Materiel
    {
        return $this->materiel;
    }

    public function setMateriel(?Materiel $materiel): self
    {
        $this->materiel = $materiel;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateEmprunt = null;

    public function getDateEmprunt(): ?\DateTimeInterface
    {
        return $this->dateEmprunt;
    }

    public function setDateEmprunt(\DateTimeInterface $dateEmprunt): self
    {
        $this->dateEmprunt = $dateEmprunt;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $dateRetour = null;

    public function getDateRetour(): ?\DateTimeInterface
    {
        return $this->dateRetour;
    }

    public function setDateRetour(\DateTimeInterface $dateRetour): self
    {
        $this->dateRetour = $dateRetour;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statutEmprunt = null;

    public function getStatutEmprunt(): ?string
    {
        return $this->statutEmprunt;
    }

    public function setStatutEmprunt(?string $statutEmprunt): self
    {
        $this->statutEmprunt = $statutEmprunt;
        return $this;
    }

}
