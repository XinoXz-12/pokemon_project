<?php

namespace App\Controller;

use App\Entity\Battle;
use App\Entity\Pokedex;
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

    #[Route('/battle/select', name: 'app_battle_select', methods: ['GET'])]
    public function select(UserRepository $userRepository, EntityManagerInterface $entityManager, PokemonRepository $pokemonRepository, Request $request): Response
    {

        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Si no existe una Pokedex o está vacía, lo enviamos a cazar
        if (!$pokedex /*|| $pokedex->getpokedexPokemons()->isEmpty()*/) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Si tiene más de un pokemon en la pokedex, lo enviamos a elegir

        // Prueba
        $pokemons = $pokemonRepository->findAll();

        $poke1 = $pokemons[0];
        $poke2 = $pokemons[1];
        $poke3 = $pokemons[2];
        $poke4 = $pokemons[3];
        $poke5 = $pokemons[4];
        $poke6 = $pokemons[5];

        $pokemons = [$poke1, $poke2, $poke3, $poke4, $poke5, $poke6];

        // Me traigo la variable id pasada por el metodo get en la url
        $id = $request->query->get('id');

        return $this->render('battle/select_pokemon.html.twig', ['pokemons' => $pokemons, 'id_enemy' => $id]);
    }

    #[Route('/battle/finish', name: 'app_combat_logical', methods: ['GET'])]
    public function logical(Request $request, PokemonRepository $pokemonRepository, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);

        // Si no existe una Pokedex, lo enviamos a cazar
        if (!$pokedex) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Me traigo el pokemon enemigo
        $enemy = $pokemonRepository->find($request->query->get('id_enemy'));

        // Me traigo a mi pokemon de mi pókedex
        // $ally = $pokedex->getpokedexPokemons()->find($request->query->get('id_ally'));
        // Prueba
        $ally = $pokemonRepository->find("1");

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
        $battle->setAllyPokemon($ally);
        $battle->setRivalPokemon($enemy);
        $battle->setTrainer($user);
        $battle->setResult($ally_power > $enemy_power ? 1 : 0);
        $entityManager->persist($battle);
        $entityManager->flush();

        return $this->redirectToRoute('app_main');
    }
}
