<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Tournoi;
use Doctrine\Common\Collections\Collection; // Correctly import the Collection interface


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'ID', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    private ?string $motdepasse = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $genre = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(name: 'numeroTelephone', length: 50, nullable: true)]
    private ?string $numeroTelephone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(name: 'photoProfil', length: 255, nullable: true)]
    private ?string $photoProfil = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'roleID', referencedColumnName: 'roleID')]
    private ?Role $role = null;

    #[ORM\Column(name: 'nomOrganisation', length: 255, nullable: true)]
    private ?string $nomOrganisation = null;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Tournoi', mappedBy: 'participants')]
    private Collection $tournois;

    public function __construct()
    {
        $this->tournois = new ArrayCollection();  // Initialize as an ArrayCollection
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->motdepasse;
    }

    public function setPassword(string $password): self
    {
        $this->motdepasse = $password;
        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getNumeroTelephone(): ?string
    {
        return $this->numeroTelephone;
    }

    public function setNumeroTelephone(?string $numeroTelephone): self
    {
        $this->numeroTelephone = $numeroTelephone;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): self
    {
        $this->photoProfil = $photoProfil;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getNomOrganisation(): ?string
    {
        return $this->nomOrganisation;
    }

    public function setNomOrganisation(?string $nomOrganisation): self
    {
        $this->nomOrganisation = $nomOrganisation;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role?->getRoleNom() ?? 'USER')];
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }


    public function getTournois(): Collection
    {
        return $this->tournois;
    }

    public function addTournoi(Tournoi $tournoi): self
    {
        if (!$this->tournois->contains($tournoi)) {
            $this->tournois[] = $tournoi;
            $tournoi->addParticipant($this);
        }

        return $this;
    }

    public function removeTournoi(Tournoi $tournoi): self
    {
        if ($this->tournois->removeElement($tournoi)) {
            $tournoi->removeParticipant($this);
        }

        return $this;
    }

    public function __toString(): string
{
    return $this->nom ?? 'User Inconnu';
}
} 