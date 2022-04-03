<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, UserRepository $users): Response
    {
        $coachs = [];
        $ville = $request->query->get('ville', null);
        $coaching = $request->query->get('coaching', null);
        $coachs = $users->findCoachs("ROLE_COACH", $ville, $coaching);
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'coachs' => $coachs,
        ]);
    }
}
