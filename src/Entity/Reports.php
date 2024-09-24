<?php

namespace App\Entity;

use App\Repository\ReportsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportsRepository::class)]
class Reports
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $state = null;

    #[ORM\Column(length: 120)]
    private ?string $food = null;

    #[ORM\Column]
    private ?int $food_weight = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $time = null;

    #[ORM\Column(length: 255)]
    private ?string $detail_state = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?Animals $animal = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getFood(): ?string
    {
        return $this->food;
    }

    public function setFood(string $food): static
    {
        $this->food = $food;

        return $this;
    }

    public function getFoodWeight(): ?int
    {
        return $this->food_weight;
    }

    public function setFoodWeight(int $food_weight): static
    {
        $this->food_weight = $food_weight;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): static
    {
        $this->time = $time;

        return $this;
    }

    public function getDetailState(): ?string
    {
        return $this->detail_state;
    }

    public function setDetailState(string $detail_state): static
    {
        $this->detail_state = $detail_state;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAnimal(): ?Animals
    {
        return $this->animal;
    }

    public function setAnimal(?Animals $animal): static
    {
        $this->animal = $animal;

        return $this;
    }
}
