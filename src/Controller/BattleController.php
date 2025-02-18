<?php

namespace App\Controller;

use App\Entity\Battle;
use App\Entity\Pokedex;
use App\Entity\PokedexPokemon;
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

    #[Route('/battle/winner', name: 'app_battle_winner_selection', methods: ['GET'])]
    public function winner(UserRepository $userRepository, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Si no existe una Pokedex o está vacía, lo enviamos a cazar
        if (!$pokedex || $pokedex->getpokedexPokemons()->isEmpty()) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Me traigo la variable id pasada por el metodo get en la url
        $id = $request->query->get('id');

        return $this->render('battle/winner_selection.html.twig', ['id_enemy' => $id]);
    }

    #[Route('/battle/select', name: 'app_battle_select', methods: ['GET'])]
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

        // Me traigo la variable reward pasada por el metodo get en la url
        $reward = $request->query->get('reward');

        return $this->render('battle/select_pokemon.html.twig', ['pokemons' => $pokemons, 'id_enemy' => $id, 'reward' => $reward]);
    }

    #[Route('/battle/resurrection', name: 'app_battle_resurrection', methods: ['GET'])]
    public function resurrection(UserRepository $userRepository, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);
        if ($pokedex) {
            $pokemons = $pokedex->getpokedexPokemons();
        } else {
            $pokemons = null;
        }

        return $this->render('battle/resurrection.html.twig', [
            'pokedexPokemons' => $pokemons,
        ]);
    }

    #[Route('/battle/resurrection/ok', name: 'app_battle_resurrection_ok', methods: ['POST'])]
    public function resurrection_ok(UserRepository $userRepository, PokedexPokemonRepository $pokedexPokemonRepository, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository, Request $request): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);
        //$pokedexPokemons = $entityManager->getRepository(PokedexPokemon::class)->findBy(['pokedex' => $pokedex->getId()]);
        $pokedexPokemons = $pokedex->getpokedexPokemons();

        foreach($pokedexPokemons as $pokedexPokemon) {
            if ($pokedexPokemon->getId() == $request->query->get('id')) {
                // Dejar de estar herido/debilitado
                $pokedexPokemonInjured = $entityManager->getRepository(PokedexPokemon::class)->find($pokedexPokemon->getId());
                $pokedexPokemonInjured->setInjured(false);
                $entityManager->persist($pokedexPokemonInjured);
                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_history');
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

        // Me traigo la variable reward pasada por el metodo get en la url
        $reward = $request->query->get('reward');

        // Calculo sus poderes
        $ally_power = $ally->getLevel() * $ally->getStrength();
        $enemy_power = $enemy->getLevel() * $enemy->getStrength();

        // El pokemon con el mayor poder gana, si gana el ally le suma 1 nivel
        if ($ally_power > $enemy_power) {
            switch ($reward) {
                case 'level':
                    $ally->setLevel($ally->getLevel() + 1);
                    $entityManager->persist($ally);
                    $entityManager->flush();
                    break;
                case 'catch':
                    // Guardamos la información de la batalla en la base de datos
                    $battle = new Battle();
                    $battle->setAllyPokemon($ally->getPokemon());
                    $battle->setRivalPokemon($enemy);
                    $battle->setTrainer($user);
                    $battle->setResult($ally_power > $enemy_power ? 1 : 0);
                    $entityManager->persist($battle);
                    $entityManager->flush();
                    // Y redirigimos a caza
                    return $this->redirectToRoute('app_user_throw', ['id' => $request->query->get('id_enemy')]);
                case 'cure':
                    // Guardamos la información de la batalla en la base de datos
                    $battle = new Battle();
                    $battle->setAllyPokemon($ally->getPokemon());
                    $battle->setRivalPokemon($enemy);
                    $battle->setTrainer($user);
                    $battle->setResult($ally_power > $enemy_power ? 1 : 0);
                    $entityManager->persist($battle);
                    $entityManager->flush();
                    // app_battle_resurrection
                    return $this->redirectToRoute('app_battle_resurrection');
            }
        } else if ($ally_power < $enemy_power) {
            $ally->setInjured(true);
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
