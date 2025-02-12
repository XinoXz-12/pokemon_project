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
            $pokedex = new Pokedex();
            $pokedex->setTrainer($user);
            $pokedex->setLevel($pokemon->getLevel());
            $pokedex->setStrength($pokemon->getStrength());
            $pokedex->addPokemon($pokemon);

            $entityManager->persist($pokedex);
            $entityManager->flush();
        }

        return $this->render('user/throw.html.twig', [
            'result' => $result
        ]);
    }
}
