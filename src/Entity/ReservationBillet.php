<?php

namespace App\Entity;

use App\Repository\ReservationBilletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationBilletRepository::class)]
#[ORM\Table(name: "reservationbillet")]
class ReservationBillet
{
    #[ORM\Id]
    #[ORM\Column(name: "reservationID", type: "integer")]
    private ?int $reservationID = null;

    #[ORM\Id]
    #[ORM\Column(name: "billetID", type: "integer")]
    private ?int $billetID = null;

    #[ORM\Column(name: "nombreBillet", type: "integer")]
    private ?int $nombreBillet = 1;

    #[ORM\ManyToOne(targetEntity: Reservation::class, inversedBy: "reservationBillets")]
    #[ORM\JoinColumn(name: "reservationID", referencedColumnName: "ID", nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(targetEntity: Billet::class)]
    #[ORM\JoinColumn(name: "billetID", referencedColumnName: "ID", nullable: false)]
    private ?Billet $billet = null;

    public function getReservationID(): ?int
    {
        return $this->reservationID;
    }

    public function setReservationID(int $reservationID): self
    {
        $this->reservationID = $reservationID;
        return $this;
    }

    public function getBilletID(): ?int
    {
        return $this->billetID;
    }

    public function setBilletID(int $billetID): self
    {
        $this->billetID = $billetID;
        return $this;
    }

    public function getNombreBillet(): ?int
    {
        return $this->nombreBillet;
    }

    public function setNombreBillet(int $nombreBillet): self
    {
        $this->nombreBillet = $nombreBillet;
        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;
        return $this;
    }

    public function getBillet(): ?Billet
    {
        return $this->billet;
    }

    public function setBillet(?Billet $billet): self
    {
        $this->billet = $billet;
        return $this;
    }
} 