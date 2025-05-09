<?php
namespace App\Entity;

use App\Repository\ReviewRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'The comment cannot be empty')]
    private ?string $comment = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'Rating must be between {{ min }} and {{ max }}')]
    private ?int $rating = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    private ?Event $event = null;

    public function getId(): ?int { return $this->id; }

    public function getComment(): ?string { return $this->comment; }

    public function setComment(string $comment): static {
        $this->comment = $comment;
        return $this;
    }

    public function getRating(): ?int { return $this->rating; }

    public function setRating(int $rating): static {
        $this->rating = $rating;
        return $this;
    }

    public function getUser(): ?User { return $this->user; }

    public function setUser(?User $user): static {
        $this->user = $user;
        return $this;
    }

    public function getEvent(): ?Event { return $this->event; }

    public function setEvent(?Event $event): static {
        $this->event = $event;
        return $this;
    }
}
