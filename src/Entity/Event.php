<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The details cannot be empty')]
    #[Assert\Length(min: 3, max: 50, minMessage: 'The name must be at least {{ limit }} characters long.', maxMessage: 'The name cannot be longer than {{ limit }} characters.')]

    private ?string $details = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The name cannot be empty')]
    #[Assert\Length(min: 3, max: 8, minMessage: 'The name must be at least {{ limit }} characters long.', maxMessage: 'The name cannot be longer than {{ limit }} characters.')]

    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'The start date cannot be empty')]

    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "The end date must be after the start date.")]
    #[Assert\NotBlank (message: 'The end date cannot be empty')]

    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message:'The reward cannot be empty')]
    private ?string $reward = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?Terrain $terrain = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getReward(): ?string
    {
        return $this->reward;
    }

    public function setReward(string $reward): static
    {
        $this->reward = $reward;

        return $this;
    }

    public function getTerrain(): ?Terrain
    {
        return $this->terrain;
    }

    public function setTerrain(?Terrain $terrain): static
    {
        $this->terrain = $terrain;

        return $this;
    }
    public function __construct()
{
    $this->reviews = new ArrayCollection();

    $this->startDate = $this->startDate ?? new \DateTime();
    $this->endDate = $this->endDate ?? new \DateTime(); 
}
#[ORM\OneToMany(mappedBy: 'event', targetEntity: Review::class, orphanRemoval: true)]
private Collection $reviews;

public function getReviews(): Collection
{
    return $this->reviews;
}
public function __toString()
{
    return $this->getName(); // or any other meaningful string representation of the object
}

}
