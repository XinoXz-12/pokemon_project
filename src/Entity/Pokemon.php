<?php

namespace App\Entity;

use App\Repository\PokemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokemonRepository::class)]
class Pokemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?array $type = [];

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $level = null;

    #[ORM\Column]
    private ?int $strength = null;

    /**
     * @var Collection<int, Battle>
     */
    #[ORM\OneToMany(targetEntity: Battle::class, mappedBy: 'rivalPokemon')]
    private Collection $battles;

    /**
     * @var Collection<int, PokedexPokemon>
     */
    #[ORM\OneToMany(mappedBy: 'pokemon', targetEntity: PokedexPokemon::class, cascade: ['persist', 'remove'])]
    private Collection $pokedexPokemons;

    #[ORM\Column(nullable: true)]
    private ?int $evolution = null;

    public function __construct()
    {
        $this->pokedexPokemons = new ArrayCollection();
        $this->battles = new ArrayCollection();
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

    public function getType(): ?array
    {
        return $this->type;
    }

    public function setType(array $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): static
    {
        $this->strength = $strength;

        return $this;
    }

    /**
     * @return Collection<int, Battle>
     */
    public function getBattles(): Collection
    {
        return $this->battles;
    }

    public function addBattle(Battle $battle): static
    {
        if (!$this->battles->contains($battle)) {
            $this->battles->add($battle);
            $battle->setRivalPokemon($this);
        }

        return $this;
    }

    public function removeBattle(Battle $battle): static
    {
        if ($this->battles->removeElement($battle)) {
            // set the owning side to null (unless already changed)
            if ($battle->getRivalPokemon() === $this) {
                $battle->setRivalPokemon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PokedexPokemon>
     */
    public function getPokedexPokemons(): ?Collection
    {
        return $this->pokedexPokemons;
    }

    public function addPokedexPokemon(PokedexPokemon $pokedexPokemon): static
    {
        if (!$this->pokedexPokemons->contains($pokedexPokemon)) {
            $this->pokedexPokemons->add($pokedexPokemon);
            $pokedexPokemon->setPokemon($this);
        }

        return $this;
    }

    public function removePokedexPokemon(PokedexPokemon $pokedexPokemon): static
    {
        if ($this->pokedexPokemons->removeElement($pokedexPokemon)) {
            // set the owning side to null (unless already changed)
            if ($pokedexPokemon->getPokemon() === $this) {
                $pokedexPokemon->setPokemon(null);
            }
        }

        return $this;
    }

    public function getEvolution(): ?int
    {
        return $this->evolution;
    }

    public function setEvolution(?int $evolution): static
    {
        $this->evolution = $evolution;

        return $this;
    }
}
