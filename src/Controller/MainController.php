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
        $pokemons = $pokedex->getpokedexPokemons();

        return $this->render('main/index.html.twig', [
            'pokedexPokemons' => $pokemons,
        ]);
    }
}
