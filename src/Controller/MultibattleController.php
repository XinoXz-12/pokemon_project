<?php

namespace App\Controller;

use App\Entity\Multibattle;
use App\Entity\Pokedex;
use App\Repository\MultibattleRepository;
use App\Repository\PokedexPokemonRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MultibattleController extends AbstractController
{
    #[Route('/multibattle', name: 'app_multibattle')]
    public function index(UserRepository $userRepository, EntityManagerInterface $entityManager, MultibattleRepository $multibattleRepository): Response
    {
        // Si no existe una Pokedex o está vacía, lo enviamos a cazar
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);
        if (!$pokedex || $pokedex->getpokedexPokemons()->isEmpty()) {
            return $this->redirectToRoute('app_user_catch');
        }

        // Traerme todos los duelos disponibles
        $multibattles = $multibattleRepository->findAll([
            'rival_trainer' => null
        ]);

        return $this->render('multibattle/index.html.twig', [
            'multibattles' => $multibattles
        ]);
    }

    #[Route('/multibattle/open', name: 'app_user_open_battle', methods: ['GET'])]
    public function openBattle(UserRepository $userRepository, EntityManagerInterface $entityManager, Request $request, MultibattleRepository $multibattle): Response
    {
        // Hacer que el usuario esté abierto a un combate
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $user->setOpenBattle(true);
        $entityManager->persist($user);
        $entityManager->flush();

        // Guardar el tipo de combate
        $type = $request->query->get('type');
        $multibattle = new Multibattle();
        $multibattle->setType($type);
        $multibattle->setAllyTrainer($user);
        $entityManager->persist($multibattle);
        $entityManager->flush();

        // Traerme todos mis pokemons de mi pokedex
        $pokedex = $entityManager->getRepository(Pokedex::class)->findOneBy(['trainer' => $user]);
        $pokemons = $pokedex->getpokedexPokemons();

        return $this->render('multibattle/select_pokemon.html.twig', [
            'pokemons' => $pokemons,
            'select' => $type
        ]);
    }

    #[Route('/multibattle/close', name: 'app_user_close_battle')]
    public function closeBattle(UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Eliminar el combate abierto
        $multibattle = $entityManager->getRepository(Multibattle::class)->findOneBy(['ally_trainer' => $this->getUser(), 'rival_trainer' => null]);
        if ($multibattle !== null) {
            $entityManager->remove($multibattle);
            $entityManager->flush();
        }

        // Cerrar la disposición a combate del usuario
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $user->setOpenBattle(false);
        $entityManager->persist($user);
        $entityManager->flush();

        $rivals = $userRepository->search_rivals($this->getUser());

        return $this->render('multibattle/index.html.twig', [
            'rivals' => $rivals,
        ]);
    }

    #[Route('/multibattle/check-team', name: 'check_team', methods: ['POST'])]
    public function checkTeam(Request $request, EntityManagerInterface $entityManager, MultibattleRepository $multibattleRepository): Response
    {
        // Recoger los pokemons seleccionados
        $team = $request->request->get('pokemon', []);

        // Recoger el número de pokemons para combatir
        $maxSeleccion = $request->request->get('select');

        // Recoger el input hidden select
        $type = $request->request->get('select');

        // Validar que el usuario ha seleccionado la cantidad correcta
        if (!is_array($team) || $team->getSize() != $maxSeleccion) {
            return $this->render('multibattle/message.html.twig', [
                'message' => "Número de pokemons incorrecto, tienes que seleccionar $maxSeleccion pokemons.",
            ]);
        }

        // Si está correcto, lo guardamos en la base de datos
        $multibattle = $multibattleRepository->findOneBy(['type' => $type, 'ally_trainer' => $this->getUser()]);
        $multibattle->setAllyPokearray($team);
        $entityManager->persist($multibattle);
        $entityManager->flush();

        if ($multibattle->getRivalPokearray() !== null && $multibattle->getRivalPokearray() !== [] && $multibattle->getAllyPokearray() !== null && $multibattle->getAllyPokearray() !== []) {
            return $this->render('multibattle/order.html.twig', [
                'my_team' => $multibattle->getAllyPokearray(),
                'rival_team' => $multibattle->getRivalPokearray(),
                'type' => $multibattle->getType(),
                'multibattle' => $multibattle->getId(),
            ]);
        } else {
            return $this->render('multibattle/message.html.twig', [
                'message' => "Duelo abierto, espere al otro entrenador para combatir."
            ]);
        }
    }

    #[Route('/multibattle/start', name: 'app_multibattle_start', methods: ['GET'])]
    public function start(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, MultibattleRepository $multibattleRepository): Response
    {
        // Me traigo la id del duelo seleccionado
        $id = $request->query->get('battle');

        // Me traigo el combate
        $multibattle = $multibattleRepository->find($id);

        // Me añado al combate
        $multibattle->setRivalTrainer($this->getUser());
        $entityManager->persist($multibattle);
        $entityManager->flush();

        // Cerrar la disposición a combate del usuario
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $user->setOpenBattle(false);
        $entityManager->persist($user);
        $entityManager->flush();

        // Me voy a elegir mis pokemons
        return $this->render('multibattle/select_pokemon.html.twig', ['select' => $multibattle->getType()]);
    }

    #[Route('/multibattle/order', name: 'order_pokemons', methods: ['POST'])]
    public function order_pokemons(Request $request, PokedexPokemonRepository $pokedexPokemonRepository, EntityManagerInterface $entityManager, MultibattleRepository $multibattleRepository): Response
    {
        $pokemons = $request->request->get('pokemon');

        if (!is_array($pokemons)) {
            $this->addFlash('error', 'Hubo un problema al procesar los datos del equipo.');
            return $this->redirectToRoute('ruta_de_seleccion');
        }

        // Obtener los pokemons seleccionados
        $order_pokemons = [];
        foreach ($pokemons as $pokemonId) {
            $or_poke = $pokedexPokemonRepository->find($pokemonId);
            $order_pokemons[] = $or_poke;
        }

        // Guardar en la base de datos
        $multibattle = $multibattleRepository->findOneBy(['id' => $request->request->get('multibattle_id')]);
        if ($multibattle->getAllyTrainer == $this->getUser()) {
            $multibattle->setAllyPokearray($order_pokemons);
        } elseif ($multibattle->getRivalTrainer == $this->getUser()) {
            $multibattle->setRivalPokearray($order_pokemons);
        }
        $entityManager->persist($multibattle);
        $entityManager->flush();

        // Comprobar que todos los campos, a excepción del resultado, de la multibattle están completos

        if (
            $multibattle->getAllyTrainer() !== null &&
            $multibattle->getRivalTrainer() !== null &&
            $multibattle->getType() !== null &&
            $multibattle->getAllyPokearray() !== null &&
            $multibattle->getPokemonsRival() !== null
        ) {
            return $this->redirectToRoute('app_multibattle_logic', ['id' => $multibattle->getId()]);
        } else {
            return $this->render('multibattle/message.html.twig', [
                'message' => "Esperando al otro entrenador."
            ]);
        }
    }

    #[Route('/multibattle/logic', name: 'app_multibattle_logic', methods: ['GET'])]
    public function logic(Request $request, EntityManagerInterface $entityManager, MultibattleRepository $multibattleRepository): Response
    {
        $id = $request->query->get('id');
        $multibattle = $multibattleRepository->find($id);

        $allyPokemons = $multibattle->getAllyPokearray();
        $rivalPokemons = $multibattle->getPokemonsRival();
        $type = $multibattle->getType();

        $allyWins = 0;
        $rivalWins = 0;

        for ($i = 0; $i < $type; $i++) {
            $allyPokemon = $allyPokemons[$i];
            $rivalPokemon = $rivalPokemons[$i];

            $allyPokemonStrength = $allyPokemon->getLevel() * $allyPokemon->getStrength();
            $rivalPokemonStrength = $rivalPokemon->getLevel() * $rivalPokemon->getStrength();

            if ($allyPokemonStrength > $rivalPokemonStrength) {
                $allyWins++;
                $rivalPokemon->setInjured(true);
            } elseif ($rivalPokemonStrength > $allyPokemonStrength) {
                $rivalWins++;
                $allyPokemon->setInjured(true);
            }
        }

        // Guardar los cambios
        $multibattle->setResult($allyWins > $rivalWins ? 1 : 0);
        $entityManager->persist($multibattle);
        $entityManager->flush();

        if ($allyWins > $rivalWins) {
            return $this->render('duel_result.html.twig', [
                'multibattle' => $multibattle,
                'winner' => $allyWins > $rivalWins ? $allyWins : $rivalWins,
                'winnerPokemons' => $allyWins > $rivalWins ? $multibattle->getAllyPokearray() : $multibattle->getPokemonsRival(),
            ]);
        } else {
            return $this->render('message.html.twig', [
                'message' => "Has perdido el combate.",
            ]);
        }
    }

    #[Route('/multibattle/result', name: 'duel_result_action', methods: ['GET'])]
    public function duelResultAction(Request $request, Multibattle $multibattle, EntityManagerInterface $entityManager)
    {
        $select = $request->get('select');
    
        if ($select == 'level_up') {
            // Subir 1 level a todos los pokemons del ganador
            foreach ($multibattle->getAllyPokearray() as $pokemon) {
                $pokemon->setLevel($pokemon->getLevel() + 1);
            }
        } elseif ($select == 'revive') {
            // Envía a la página de revivir pokemons
            return $this->redirectToRoute('battle_resurrection', ['multibattle_id' => $multibattle->getId()]);
        }

        // Obtener los pokemons modificados
        $pokemons = $multibattle->getAllyPokearray();
        
        // Mergear los pokemons modificados con la base de datos
        foreach ($pokemons as $pokemon) {
            $entityManager->persist($pokemon, ['merge' => true]);
        }
        
        // Guardar los cambios
        $entityManager->flush();
    
        return $this->render('multibattle/message.html.twig', ['message' => "Todo correcto, feliciades!"]);
    }
}
