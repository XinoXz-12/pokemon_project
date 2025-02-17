<?php

namespace App\Entity;

use App\Repository\MultibattleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MultibattleRepository::class)]
class Multibattle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'multibattles')]
    private ?User $ally_trainer = null;

    #[ORM\ManyToOne(inversedBy: 'multibattles')]
    private ?User $rival_trainer = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $ally_pokearray = [];

    #[ORM\Column(type: Types::ARRAY)]
    private array $rival_pokearray = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $result = null;

    #[ORM\Column]
    private ?int $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAllyTrainer(): ?User
    {
        return $this->ally_trainer;
    }

    public function setAllyTrainer(?User $ally_trainer): static
    {
        $this->ally_trainer = $ally_trainer;

        return $this;
    }

    public function getRivalTrainer(): ?User
    {
        return $this->rival_trainer;
    }

    public function setRivalTrainer(?User $rival_trainer): static
    {
        $this->rival_trainer = $rival_trainer;

        return $this;
    }

    public function getAllyPokearray(): array
    {
        return $this->ally_pokearray;
    }

    public function setAllyPokearray(array $ally_pokearray): static
    {
        $this->ally_pokearray = $ally_pokearray;

        return $this;
    }

    public function getRivalPokearray(): array
    {
        return $this->rival_pokearray;
    }

    public function setRivalPokearray(array $rival_pokearray): static
    {
        $this->rival_pokearray = $rival_pokearray;

        return $this;
    }

    public function getResult(): ?array
    {
        return $this->result;
    }

    public function setResult(?array $result): static
    {
        $this->result = $result;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }
}
