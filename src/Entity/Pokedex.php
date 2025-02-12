<?php

namespace App\Entity;

use App\Repository\PokedexRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokedexRepository::class)]
class Pokedex
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pokedex', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $trainer = null;

    /**
     * @var Collection<int, PokedexPokemon>
     */
    #[ORM\OneToMany(mappedBy: 'pokedex', targetEntity: PokedexPokemon::class, cascade: ['persist', 'remove'])]
    private Collection $pokedexPokemons;



    /**
     * @var Collection<int, Battle>
     */
    #[ORM\OneToMany(targetEntity: Battle::class, mappedBy: 'allyPokemon')]
    private Collection $battles;


    public function __construct()
    {
        $this->pokedexPokemons = new ArrayCollection();
        $this->battles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(User $trainer): static
    {
        $this->trainer = $trainer;

        return $this;
    }

    /**
     * @return Collection<int, Pokemon>
     */
    public function getpokedexPokemons(): Collection
    {
        return $this->pokedexPokemons;
    }

    public function addPokemon(Pokemon $pokemon, int $level, int $strength): static
{
    // Crear un objeto PokedexPokemon
    $pokedexPokemon = new PokedexPokemon();
    $pokedexPokemon->setPokedex($this);  // Establecer la relación con la Pokedex
    $pokedexPokemon->setPokemon($pokemon);  // Establecer la relación con el Pokemon
    $pokedexPokemon->setLevel($level);  // Asignar el nivel
    $pokedexPokemon->setStrength($strength);  // Asignar la fuerza

    // Agregar el objeto PokedexPokemon a la colección
    if (!$this->pokedexPokemons->contains($pokedexPokemon)) {
        $this->pokedexPokemons->add($pokedexPokemon);
    }

    return $this;
}

    public function removePokemon(Pokemon $pokemon): static
    {
        $this->pokedexPokemons->removeElement($pokemon);

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
            $battle->setAllyPokemon($this);
        }

        return $this;
    }

    public function removeBattle(Battle $battle): static
    {
        if ($this->battles->removeElement($battle)) {
            // set the owning side to null (unless already changed)
            if ($battle->getAllyPokemon() === $this) {
                $battle->setAllyPokemon(null);
            }
        }

        return $this;
    }
}
