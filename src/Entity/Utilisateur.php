<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

use App\Repository\UtilisateurRepository;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
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

    #[ORM\Column(name: 'email', type: 'string', nullable: false)]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    #[ORM\Column(name: 'motdepasse', type: 'string', nullable: false)]
    private ?string $motdepasse = null;

    public function getMotdepasse(): ?string
    {
        return $this->motdepasse;
    }

    public function setMotdepasse(string $motdepasse): self
    {
        $this->motdepasse = $motdepasse;
        return $this;
    }

    #[ORM\Column(name: 'genre', type: 'string', nullable: true)]
    private ?string $genre = null;

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(?string $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    #[ORM\Column(name: 'prenom', type: 'string', nullable: true)]
    private ?string $prenom = null;

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    #[ORM\Column(name: 'nom', type: 'string', nullable: true)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(name: 'numeroTelephone', type: 'string', nullable: true)]
    private ?string $numeroTelephone = null;

    public function getNumeroTelephone(): ?string
    {
        return $this->numeroTelephone;
    }

    public function setNumeroTelephone(?string $numeroTelephone): self
    {
        $this->numeroTelephone = $numeroTelephone;
        return $this;
    }

    #[ORM\Column(name: 'adresse', type: 'text', nullable: true)]
    private ?string $adresse = null;

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    #[ORM\Column(name: 'photoProfil', type: 'string', nullable: true)]
    private ?string $photoProfil = null;

    public function getPhotoProfil(): ?string
    {
        return $this->photoProfil;
    }

    public function setPhotoProfil(?string $photoProfil): self
    {
        $this->photoProfil = $photoProfil;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(name: 'roleID', referencedColumnName: 'roleID', nullable: true)]
    private ?Role $role = null;

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;
        return $this;
    }

    #[ORM\Column(name: 'nomOrganisation', type: 'string', nullable: true)]
    private ?string $nomOrganisation = null;

    public function getNomOrganisation(): ?string
    {
        return $this->nomOrganisation;
    }

    public function setNomOrganisation(?string $nomOrganisation): self
    {
        $this->nomOrganisation = $nomOrganisation;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Emprunt::class, mappedBy: 'utilisateur')]
    private Collection $emprunts;

    /**
     * @return Collection<int, Emprunt>
     */
    public function getEmprunts(): Collection
    {
        if (!$this->emprunts instanceof Collection) {
            $this->emprunts = new ArrayCollection();
        }
        return $this->emprunts;
    }

    public function addEmprunt(Emprunt $emprunt): self
    {
        if (!$this->getEmprunts()->contains($emprunt)) {
            $this->getEmprunts()->add($emprunt);
        }
        return $this;
    }

    public function removeEmprunt(Emprunt $emprunt): self
    {
        $this->getEmprunts()->removeElement($emprunt);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'utilisateur')]
    private Collection $reservations;

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        if (!$this->reservations instanceof Collection) {
            $this->reservations = new ArrayCollection();
        }
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        $this->getReservations()->removeElement($reservation);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Reservationterrain::class, mappedBy: 'utilisateur')]
    private Collection $reservationterrains;

    /**
     * @return Collection<int, Reservationterrain>
     */
    public function getReservationterrains(): Collection
    {
        if (!$this->reservationterrains instanceof Collection) {
            $this->reservationterrains = new ArrayCollection();
        }
        return $this->reservationterrains;
    }

    public function addReservationterrain(Reservationterrain $reservationterrain): self
    {
        if (!$this->getReservationterrains()->contains($reservationterrain)) {
            $this->getReservationterrains()->add($reservationterrain);
        }
        return $this;
    }

    public function removeReservationterrain(Reservationterrain $reservationterrain): self
    {
        $this->getReservationterrains()->removeElement($reservationterrain);
        return $this;
    }

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'utilisateurs')]
    #[ORM\JoinTable(
        name: 'jointable',
        joinColumns: [
            new ORM\JoinColumn(name: 'userID', referencedColumnName: 'ID')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'eventID', referencedColumnName: 'ID')
        ]
    )]
    private Collection $evenements;

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        if (!$this->evenements instanceof Collection) {
            $this->evenements = new ArrayCollection();
        }
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->getEvenements()->contains($evenement)) {
            $this->getEvenements()->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->getEvenements()->removeElement($evenement);
        return $this;
    }

    /**
     * Returns the roles granted to the user.
     */
    public function getRoles(): array
    {
        // Always include ROLE_USER as a base role
        $roles = ['ROLE_USER'];
        
        // Add role based on the user's assigned role
        if ($this->role !== null) {
            try {
                $roleName = $this->role->getRoleNom();
                
                if ($roleName) {
                    // Handle ADMIN role specifically
                    if ($roleName === 'ADMIN') {
                        $roles[] = 'ROLE_ADMIN';
                    } else {
                        // Convert to uppercase for consistency
                        $roleName = strtoupper($roleName);
                        
                        // Add ROLE_ prefix if it doesn't already have it
                        if (!str_starts_with($roleName, 'ROLE_')) {
                            $roleName = 'ROLE_' . $roleName;
                        }
                        
                        $roles[] = $roleName;
                    }
                }
            } catch (\Exception $e) {
                // If there's any issue accessing the role, just continue with basic roles
            }
        }
        
        return array_unique($roles);
    }

    /**
     * The password used to authenticate the user.
     */
    public function getPassword(): ?string
    {
        return $this->motdepasse;
    }

    /**
     * Returns the username used to authenticate the user.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    /**
     * @deprecated since Symfony 5.3
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }
}
