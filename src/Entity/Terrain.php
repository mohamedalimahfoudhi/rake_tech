<?php

namespace App\Entity;

use App\Repository\TerrainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TerrainRepository::class)]
class Terrain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $courtType = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The location cannot be empty')]
    #[Assert\Length(min: 3, max: 8, minMessage: 'The location must be at least {{ limit }} characters long.', maxMessage: 'The name cannot be longer than {{ limit }} characters.')]

    private ?string $location = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The name cannot be empty')]
    #[Assert\Length(min: 3, max: 8, minMessage: 'The name must be at least {{ limit }} characters long.', maxMessage: 'The name cannot be longer than {{ limit }} characters.')]

    private ?string $name = null;
    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'terrain')]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourtType(): ?string
    {
        return $this->courtType;
    }

    public function setCourtType(string $courtType): static
    {
        $this->courtType = $courtType;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setTerrain($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getTerrain() === $this) {
                $event->setTerrain(null);
            }
        }

        return $this;
    }
}
