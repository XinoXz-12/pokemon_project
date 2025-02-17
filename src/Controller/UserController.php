<?php

namespace App\Controller;

use App\Entity\Pokedex;
use App\Entity\PokedexPokemon;
use App\Entity\Pokemon;
use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/catch', name: 'app_user_catch')]
    public function catch(PokemonRepository $pokemonRepository): Response
    {
        // Recoge todos los pokemon y los baraja
        $pokemons = $pokemonRepository->findAll();
        shuffle($pokemons);

        return $this->render('user/catch.html.twig', [
            'pokemon' => $pokemons[0],
        ]);
    }

    #[Route('/{id}/throw', name: 'app_user_throw', methods: ['GET'])]
    public function throw(UserRepository $userRepository, Pokemon $pokemon, EntityManagerInterface $entityManager): Response
    {
        $idUser = $this->getUser();
        $user = $userRepository->findOneBy(array('id' => $idUser));

        // Sorteo, donde hay 6 sí y 4 no (60% posibilidades de sí, es decir, "cazado")
        $raffle = ["si", "no", "si", "no", "si", "no", "si", "no", "si", "si"];
        shuffle($raffle);
        $result = $raffle[0];

        if ($result === "si") {
            // Verificar si ya existe una Pokedex para el usuario
            $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);
    
            // Verificar si el Pokémon ya está en la Pokedex del usuario
            $existingPokedexPokemon = $entityManager->getRepository(PokedexPokemon::class)
                ->findOneBy(['pokedex' => $pokedex, 'pokemon' => $pokemon]);
    
            // Si el Pokémon no está en la Pokedex, agregarlo
            if (!$existingPokedexPokemon) {
                $pokedexPokemon = new PokedexPokemon();
                $pokedexPokemon->setPokedex($pokedex);
                $pokedexPokemon->setPokemon($pokemon);
                $pokedexPokemon->setLevel(1); 
                $pokedexPokemon->setStrength(10);
                $pokedexPokemon->setInjured(false);
    
                // Persistir el objeto PokedexPokemon
                $entityManager->persist($pokedexPokemon);
                $entityManager->flush();
            }
        }

        return $this->render('user/throw.html.twig', [
            'result' => $result
        ]);
    }
}
