<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: "opinions")]
class Opinions
{
    #[MongoDB\Id]
    private $id;

    #[MongoDB\Field(type: "string")]
    private $pseudo;

    #[MongoDB\Field(type: "string")]
    private $description;

    #[MongoDB\Field(type: "bool")]
    private $is_visible;

    public function __construct()
    {
        $this->is_visible = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isVisible(): bool
    {
        return $this->is_visible;
    }

    public function setIsVisible(bool $is_visible): self
    {
        $this->is_visible = $is_visible;
        return $this;
    }
}