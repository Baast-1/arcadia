<?php

namespace App\Entity;

use App\Repository\AnimalsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalsRepository::class)]
class Animals
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $name = null;

    #[ORM\Column(length: 60)]
    private ?string $breed = null;

    #[ORM\Column]
    private ?int $view = null;

    /**
     * @var Collection<int, Pictures>
     */
    #[ORM\OneToMany(targetEntity: Pictures::class, mappedBy: 'animals')]
    private Collection $pictures;

    #[ORM\ManyToOne(inversedBy: 'animals')]
    private ?Habitats $habitats = null;

    /**
     * @var Collection<int, Feed>
     */
    #[ORM\OneToMany(targetEntity: Feed::class, mappedBy: 'animal')]
    private Collection $feeds;

    /**
     * @var Collection<int, Reports>
     */
    #[ORM\OneToMany(targetEntity: Reports::class, mappedBy: 'animal')]
    private Collection $reports;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
        $this->animalFeeds = new ArrayCollection();
        $this->feeds = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getBreed(): ?string
    {
        return $this->breed;
    }

    public function setBreed(string $breed): static
    {
        $this->breed = $breed;

        return $this;
    }

    public function getView(): ?int
    {
        return $this->view;
    }

    public function setView(int $view): static
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return Collection<int, Pictures>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Pictures $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setAnimals($this);
        }

        return $this;
    }

    public function removePicture(Pictures $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getAnimals() === $this) {
                $picture->setAnimals(null);
            }
        }

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

    /**
     * @return Collection<int, Feed>
     */
    public function getFeeds(): Collection
    {
        return $this->feeds;
    }

    public function addFeed(Feed $feed): static
    {
        if (!$this->feeds->contains($feed)) {
            $this->feeds->add($feed);
            $feed->setAnimal($this);
        }

        return $this;
    }

    public function removeFeed(Feed $feed): static
    {
        if ($this->feeds->removeElement($feed)) {
            // set the owning side to null (unless already changed)
            if ($feed->getAnimal() === $this) {
                $feed->setAnimal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reports>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Reports $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setAnimal($this);
        }

        return $this;
    }

    public function removeReport(Reports $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getAnimal() === $this) {
                $report->setAnimal(null);
            }
        }

        return $this;
    }
}
