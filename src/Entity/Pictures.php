<?php
namespace App\Entity;

use App\Repository\PicturesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PicturesRepository::class)]
class Pictures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\File(mimeTypes: ['image/png', 'image/jpeg'])]
    private ?string $filename = null;

    #[ORM\OneToOne(inversedBy: 'picture', cascade: ['persist', 'remove'])]
    private ?Services $services = null;

    #[ORM\OneToOne(inversedBy: 'picture', cascade: ['persist', 'remove'])]
    private ?Habitats $habitats = null;

    #[ORM\ManyToOne(inversedBy: 'pictures')]
    private ?Animals $animals = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getServices(): ?Services
    {
        return $this->services;
    }

    public function setServices(?Services $services): static
    {
        $this->services = $services;

        return $this;
    }

    public function getHabitats(): ?Habitats
    {
        return $this->habitats;
    }

    public function setHabitats(?Habitats $habitats): static
    {
        $this->habitats = $habitats;

        return $this;
    }

    public function getAnimals(): ?Animals
    {
        return $this->animals;
    }

    public function setAnimals(?Animals $animals): static
    {
        $this->animals = $animals;

        return $this;
    }
}