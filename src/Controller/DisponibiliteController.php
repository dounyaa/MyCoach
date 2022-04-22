<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use App\Entity\User;
use App\Form\DisponibiliteType;
use App\Repository\DisponibiliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;


#[Route('/disponibilite')]
class DisponibiliteController extends AbstractController
{
    #[Route('/', name: 'app_disponibilite_index', methods: ['GET'])]
    public function index(Security $security): Response
    {
        $this->denyAccessUnlessGranted("ROLE_COACH");
        $this->security = $security;
        $coach = $this->security->getUser();
        
        return $this->render('disponibilite/index.html.twig', [
            'disponibilites' => $coach->getDisponibilites(),
        ]);
    }

    #[Route('/{id}/detail', name: 'app_disponibilite_detail')]
    public function detail($id, Security $security,Request $request, Disponibilite $disponibilite, DisponibiliteRepository $disponibiliteRepository): Response
    {
        $this->security = $security;
        
        if ($request->request->get('detail') > 0) {
            $disponibilite = $disponibiliteRepository->findOneBy(['id'=>$id]);
            $user = $disponibilite->getUser();
        }

        return $this->renderForm('disponibilite/detail.html.twig', [
            'disponibilite' => $disponibilite,
            'user' => $user,
        ]);
    }


}
