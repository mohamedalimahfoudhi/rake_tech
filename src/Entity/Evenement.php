<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID', type: 'integer')]
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

    #[ORM\Column(name: 'nom', type: 'string', nullable: false)]
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

    #[ORM\Column(name: 'details', type: 'text', nullable: true)]
    private ?string $details = null;

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    #[ORM\Column(name: 'dateDebut', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    #[ORM\Column(name: 'dateFin', type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateFin = null;

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    #[ORM\Column(name: 'type', type: 'string', nullable: true)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(name: 'recompense', type: 'string', nullable: true)]
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

    #[ORM\Column(name: 'statut', type: 'string', nullable: true)]
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

    #[ORM\Column(name: 'participantsMax', type: 'integer', nullable: true)]
    private ?int $participantsMax = null;

    public function getParticipantsMax(): ?int
    {
        return $this->participantsMax;
    }

    public function setParticipantsMax(?int $participantsMax): self
    {
        $this->participantsMax = $participantsMax;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Billet::class, mappedBy: 'evenement')]
    private Collection $billets;

    /**
     * @return Collection<int, Billet>
     */
    public function getBillets(): Collection
    {
        if (!$this->billets instanceof Collection) {
            $this->billets = new ArrayCollection();
        }
        return $this->billets;
    }

    public function addBillet(Billet $billet): self
    {
        if (!$this->getBillets()->contains($billet)) {
            $this->getBillets()->add($billet);
        }
        return $this;
    }

    public function removeBillet(Billet $billet): self
    {
        $this->getBillets()->removeElement($billet);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Tournoi::class, mappedBy: 'evenement')]
    private Collection $tournois;

    /**
     * @return Collection<int, Tournoi>
     */
    public function getTournois(): Collection
    {
        if (!$this->tournois instanceof Collection) {
            $this->tournois = new ArrayCollection();
        }
        return $this->tournois;
    }

    public function addTournoi(Tournoi $tournoi): self
    {
        if (!$this->getTournois()->contains($tournoi)) {
            $this->getTournois()->add($tournoi);
        }
        return $this;
    }

    public function removeTournoi(Tournoi $tournoi): self
    {
        $this->getTournois()->removeElement($tournoi);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Utilisateur::class, inversedBy: 'evenements')]
    #[ORM\JoinTable(
        name: 'jointable',
        joinColumns: [
            new ORM\JoinColumn(name: 'eventID', referencedColumnName: 'ID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'userID', referencedColumnName: 'ID')
        ]
    )]
    private Collection $utilisateurs;

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateurs(): Collection
    {
        if (!$this->utilisateurs instanceof Collection) {
            $this->utilisateurs = new ArrayCollection();
        }
        return $this->utilisateurs;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->getUtilisateurs()->contains($utilisateur)) {
            $this->getUtilisateurs()->add($utilisateur);
        }
        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        $this->getUtilisateurs()->removeElement($utilisateur);
        return $this;
    }

}
