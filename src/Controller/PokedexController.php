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
            return $this->render('pokedex/error.html.twig', [
                'message' => 'No tienes una Pokedex asociada.',
            ]);
        }

        // Obtener los Pokémon de la Pokedex y sacar sus nombres y fotos de pokemon
        $pokedexPokemons = $pokedexPokemonRepository->findBy(['pokedex' => $pokedex]);

        // Pasar a la vista los detalles de los Pokémon asociados a la Pokedex
        return $this->render('pokedex/pokedex.html.twig', [
            'pokedexPokemons' => $pokedexPokemons,
        ]);
    }
}
