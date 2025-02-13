<?php
namespace App\Controller;

use App\Repository\PokedexPokemonRepository;
use App\Repository\PokedexRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PokedexController extends AbstractController
{
    #[Route('/pokedex', name: 'app_pokedex', methods: ['GET'])]
    public function index(PokedexPokemonRepository $pokedexPokemonRepository, PokedexRepository $pokedexRepository): Response
    {
        // Obtener usuario actual
        $user = $this->getUser();

        // Obtener la Pokedex del usuario actual
        $pokedex = $pokedexRepository->findOneBy(['trainer' => $user]);

        // Si no se encuentra la Pokedex, manejar el caso
        if (!$pokedex) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Obtener los Pokémon de la Pokedex y sacar sus nombres y fotos de pokemon
        $pokedexPokemons = $pokedexPokemonRepository->findBy(['pokedex' => $pokedex]);

        // Pasar a la vista los detalles de los Pokémon asociados a la Pokedex
        return $this->render('main/index.html.twig', [
            'pokedexPokemons' => $pokedexPokemons,
        ]);
    }

    #[Route('/pokedex/{id}', name: 'pokemon_train', methods: ['GET', 'POST'])]
    public function train(int $id, PokedexPokemonRepository $pokedexPokemonRepository, EntityManagerInterface $entityManager): Response
    {
        $pokedexPokemon = $pokedexPokemonRepository->find($id);

    if (!$pokedexPokemon) {
        throw $this->createNotFoundException('El Pokémon no existe en tu Pokedex.');
    }

    // Lógica para entrenar el Pokémon: aumentar fuerza respetando el límite
    $currentStrength = $pokedexPokemon->getStrength();
    $level = $pokedexPokemon->getLevel();
    $maxStrength = $level * 100; // Máxima fuerza permitida según el nivel

    if ($currentStrength + 20 > $maxStrength) {
        $pokedexPokemon->setStrength($maxStrength); // Ajustar al máximo permitido
    } else {
        $pokedexPokemon->setStrength($currentStrength + 20); // Aumentar fuerza en 20 puntos
    }

    // Persistir los cambios en la base de datos
    $entityManager->persist($pokedexPokemon);
    $entityManager->flush();

        return $this->redirectToRoute('app_pokedex');
    }
}
