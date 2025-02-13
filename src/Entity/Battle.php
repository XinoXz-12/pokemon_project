<?php

namespace App\Entity;

use App\Repository\BattleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BattleRepository::class)]
class Battle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'battles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $trainer = null;

    #[ORM\ManyToOne(inversedBy: 'battles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $allyPokemon = null;

    #[ORM\ManyToOne(inversedBy: 'battles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pokemon $rivalPokemon = null;

    #[ORM\Column(nullable: true)]
    private ?int $result = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(?User $trainer): static
    {
        $this->trainer = $trainer;

        return $this;
    }

    public function getAllyPokemon(): ?Pokemon
    {
        return $this->allyPokemon;
    }

    public function setAllyPokemon(?Pokemon $allyPokemon): static
    {
        $this->allyPokemon = $allyPokemon;

        return $this;
    }

    public function getRivalPokemon(): ?Pokemon
    {
        return $this->rivalPokemon;
    }

    public function setRivalPokemon(?Pokemon $rivalPokemon): static
    {
        $this->rivalPokemon = $rivalPokemon;

        return $this;
    }

    public function getResult(): ?int
    {
        return $this->result;
    }

    public function setResult(?int $result): static
    {
        $this->result = $result;

        return $this;
    }
}
