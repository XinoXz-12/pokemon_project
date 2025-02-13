<?php

namespace App\Controller;

use App\Repository\BattleRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoryController extends AbstractController
{
    #[Route('/history', name: 'app_history')]
    public function index(BattleRepository $battleRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(array('id' => $this->getUser()));
        $battles = $battleRepository->myBattles($user);

        return $this->render('history/index.html.twig', [
            'battles' => $battles,
        ]);
    }
}
