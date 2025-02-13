<?php

namespace App\Controller;

use App\Entity\Pokedex;
use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(UserRepository $userRepository, PokemonRepository $pokemonRepository , EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Si no existe una Pokedex, lo enviamos a cazar
        if (!$pokedex) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Prueba
        $pokemons = $pokemonRepository->findAll();

        $poke1 = $pokemons[0];
        $poke2 = $pokemons[1];
        $poke3 = $pokemons[2];
        $poke4 = $pokemons[3];
        $poke5 = $pokemons[4];
        $poke6 = $pokemons[5];

        $pokemons = [$poke1, $poke2, $poke3, $poke4, $poke5, $poke6];

        return $this->render('main/index.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }
}
