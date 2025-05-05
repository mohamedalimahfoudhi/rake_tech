<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;
use Doctrine\Common\Collections\Collection; // Correctly import the Collection interface
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: 'tournoi')]
class Tournoi
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'nom', type: 'string', length: 255, nullable: false)]
    private string $nom;

    #[ORM\Column(name: 'dateDebut', type: 'date', nullable: false)]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de début doit être supérieure ou égale à aujourd'hui"
    )]
    private \DateTimeInterface $datedebut;

    #[ORM\Column(name: 'dateFin', type: 'date', nullable: false)]
    #[Assert\Expression(
        "this.getDatedebut() <= this.getDatefin()",
        message: "La date de fin doit être supérieure à la date de début"
    )]
    private \DateTimeInterface $datefin;

    #[ORM\Column(name: 'lieu', type: 'string', length: 255, nullable: false)]
    private string $lieu;

    #[ORM\Column(name: 'typeSport', type: 'string', length: 255, nullable: false)]
    private string $typesport;

    #[ORM\Column(name: 'statut', type: 'string', length: 255, nullable: false)]
    #[Assert\NotBlank(message: 'Le statut ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['Prévu', 'En_cours', 'Terminé', 'Annulé'],
        message: 'Le statut doit être "Prévu", "En_cours", "Terminé" ou "Annulé"'
    )]
    private string $statut;

    #[ORM\Column(name: 'recompense', type: 'string', length: 255, nullable: false)]
    private string $recompense;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Event')]
    #[ORM\JoinColumn(name: 'eventId', referencedColumnName: 'id')]
    private ?\App\Entity\Event $eventid = null;

    #[ORM\ManyToMany(targetEntity: 'User', inversedBy: 'tournois')]
    #[ORM\JoinTable(name: 'tournoi_participants')]
    #[ORM\JoinColumn(name: 'tournois', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'participants', referencedColumnName: 'ID')]
    private Collection $participants;


    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }
    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDatedebut(): \DateTimeInterface
    {
        return $this->datedebut;
    }

    public function setDatedebut(\DateTimeInterface $datedebut): self
    {
        $this->datedebut = $datedebut;
        return $this;
    }

    public function getDatefin(): \DateTimeInterface
    {
        return $this->datefin;
    }

    public function setDatefin(\DateTimeInterface $datefin): self
    {
        $this->datefin = $datefin;
        return $this;
    }

    public function getLieu(): string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getTypesport(): string
    {
        return $this->typesport;
    }

    public function setTypesport(string $typesport): self
    {
        $this->typesport = $typesport;
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

    public function getRecompense(): string
    {
        return $this->recompense;
    }

    public function setRecompense(string $recompense): self
    {
        $this->recompense = $recompense;
        return $this;
    }

    public function getEventid(): ?\App\Entity\Event
    {
        return $this->eventid;
    }

    public function setEventid(?\App\Entity\Event $eventid): self
    {
        $this->eventid = $eventid;
        return $this;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $user): self
    {
        if (!$this->participants->contains($user)) {
            $this->participants[] = $user;
        }

        return $this;
    }

    public function removeParticipant(User $user): self
    {
        $this->participants->removeElement($user);

        return $this;
    }
}
