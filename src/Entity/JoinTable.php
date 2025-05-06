<?php

namespace App\Entity;

use App\Repository\JoinTableRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JoinTableRepository::class)]
#[ORM\Table(name: 'jointable')]
class JoinTable
{
    #[ORM\Id]
    #[ORM\Column(name: "userID", type: "integer")]
    private ?int $userID = null;

    #[ORM\Id]
    #[ORM\Column(name: "eventID", type: "integer")]
    private ?int $eventID = null;

    #[ORM\Column(name: "userRoleInEvent", type: "string", length: 255, nullable: true)]
    private ?string $userRoleInEvent = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "userID", referencedColumnName: "ID")]
    private ?Utilisateur $user = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class)]
    #[ORM\JoinColumn(name: "eventID", referencedColumnName: "ID")]
    private ?Evenement $event = null;

    public function getUserID(): ?int
    {
        return $this->userID;
    }

    public function setUserID(int $userID): self
    {
        $this->userID = $userID;
        return $this;
    }

    public function getEventID(): ?int
    {
        return $this->eventID;
    }

    public function setEventID(int $eventID): self
    {
        $this->eventID = $eventID;
        return $this;
    }

    public function getUserRoleInEvent(): ?string
    {
        return $this->userRoleInEvent;
    }

    public function setUserRoleInEvent(?string $userRoleInEvent): self
    {
        $this->userRoleInEvent = $userRoleInEvent;
        return $this;
    }

    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    public function setUser(?Utilisateur $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getEvent(): ?Evenement
    {
        return $this->event;
    }

    public function setEvent(?Evenement $event): self
    {
        $this->event = $event;
        return $this;
    }
} 