<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\RoleRepository;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'role')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'roleID', type: 'integer')]
    private ?int $roleID = null;

    public function getRoleID(): ?int
    {
        return $this->roleID;
    }

    public function setRoleID(int $roleID): self
    {
        $this->roleID = $roleID;
        return $this;
    }

    #[ORM\Column(name: 'roleNom', type: 'string', nullable: false)]
    private ?string $roleNom = null;

    public function getRoleNom(): ?string
    {
        return $this->roleNom;
    }

    public function setRoleNom(string $roleNom): self
    {
        $this->roleNom = $roleNom;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Utilisateur::class, mappedBy: 'role')]
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
