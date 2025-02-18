<?php

namespace App\Controller\Api;

use App\Entity\PokedexPokemon;
use App\Repository\PokedexPokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pokedex-pokemon', name: 'api_pokedex_pokemon_')]
class PokedexPokemonApiController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(PokedexPokemonRepository $repository): JsonResponse
    {
        $pokedexPokemons = $repository->findAll();
        $data = array_map(function($pokemon) {
            return [
                'id' => $pokemon->getId(),
                'pokemon' => $pokemon->getPokemon()->getName(),
                'level' => $pokemon->getLevel(),
                'strength' => $pokemon->getStrength(),
                'injured' => $pokemon->isInjured()
            ];
        }, $pokedexPokemons);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(PokedexPokemon $pokedexPokemon): JsonResponse
    {
        return $this->json([
            'id' => $pokedexPokemon->getId(),
            'pokemon' => $pokedexPokemon->getPokemon()->getName(),
            'level' => $pokedexPokemon->getLevel(),
            'strength' => $pokedexPokemon->getStrength(),
            'injured' => $pokedexPokemon->isInjured()
        ]);
    }

    #[Route('/{id}/level-up', name: 'level_up', methods: ['PUT'])]
    public function levelUp(PokedexPokemon $pokedexPokemon, EntityManagerInterface $entityManager): JsonResponse
    {
        $pokedexPokemon->setLevel($pokedexPokemon->getLevel() + 1);
        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon leveled up successfully',
            'new_level' => $pokedexPokemon->getLevel()
        ]);
    }

    #[Route('/{id}/heal', name: 'heal', methods: ['PUT'])]
    public function heal(PokedexPokemon $pokedexPokemon, EntityManagerInterface $entityManager): JsonResponse
    {
        $pokedexPokemon->setInjured(false);
        $entityManager->flush();

        return $this->json([
            'message' => 'Pokemon healed successfully',
            'status' => 'healthy'
        ]);
    }
}