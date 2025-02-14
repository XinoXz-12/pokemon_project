<?php

namespace App\Controller;

use App\Entity\Battle;
use App\Entity\Pokedex;
use App\Entity\Pokemon;
use App\Repository\PokedexPokemonRepository;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BattleController extends AbstractController
{
    #[Route('/battle', name: 'app_battle')]
    public function index(PokemonRepository $pokemonRepository): Response
    {
        // Recoge todos los pokemon y los baraja
        $pokemons = $pokemonRepository->findAll();
        shuffle($pokemons);

        return $this->render('battle/index.html.twig', [
            'pokemon' => $pokemons[0],
        ]);
    }

    #[Route('/battle/select', name: 'app_battle_select', methods: ['GET', 'POST'])]
    public function select(UserRepository $userRepository, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository, Request $request): Response
    {

        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Si no existe una Pokedex o está vacía, lo enviamos a cazar
        if (!$pokedex || $pokedex->getpokedexPokemons()->isEmpty()) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Me traigo los pokemon de la pokedex
        $pokemons = $pokedex->getpokedexPokemons();


        // Me traigo la variable id pasada por el metodo get en la url
        $id = $request->query->get('id');

        return $this->render('battle/select_pokemon.html.twig', ['pokemons' => $pokemons, 'id_enemy' => $id]);
    }

    #[Route('/battle/finish', name: 'app_combat_logical', methods: ['GET'])]
    public function logical(Request $request, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, PokedexPokemonRepository $pokedexPokemonRepository): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Me traigo el pokemon enemigo
        $enemy = $pokemonRepository->find($request->query->get('id_enemy'));

        // Me traigo a mi pokemon de mi pókedex
        $ally = $pokedexPokemonRepository->findBy(['pokemon' => $request->query->get('id_ally')])[0];

        // Calculo sus poderes
        $ally_power = $ally->getLevel() * $ally->getStrength();
        $enemy_power = $enemy->getLevel() * $enemy->getStrength();

        // El pokemon con el mayor poder gana, si gana el ally le suma 1 nivel
        if ($ally_power > $enemy_power) {
            $ally->setLevel($ally->getLevel() + 1);
            $entityManager->persist($ally);
            $entityManager->flush();
        }

        // Guardamos la información de la batalla en la base de datos
        $battle = new Battle();
        $battle->setAllyPokemon($ally->getPokemon());
        $battle->setRivalPokemon($enemy);
        $battle->setTrainer($user);
        $battle->setResult($ally_power > $enemy_power ? 1 : 0);
        $entityManager->persist($battle);
        $entityManager->flush();

        return $this->redirectToRoute('app_history');
    }
}
